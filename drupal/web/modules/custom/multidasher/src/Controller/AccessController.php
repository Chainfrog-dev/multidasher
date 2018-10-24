<?php

namespace Drupal\multidasher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\views\Views;

/**
 * Controller for export json.
 */
class AccessController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->blockchainController = new RequestsController();
  }

  public function initiateRemoteBlockchain(Request $request) {
    $json_array = [
      'data' => [],
    ];

    $params = [];
    $content = $request->getContent();

    if (!empty($content)) {
      $params = json_decode($content, TRUE);
    }

    $blockchain = $params['blockchain'];
    $chain_address = $params['chainAddress'];
    $port = $params['port'];
    $blockchain_address = $blockchain .'@'.$chain_address.':'.$port;

    $exec = $this->blockchainController->constructSystemCommand('connect_external_multichain', $blockchain_address);
    $result = shell_exec($command);

    $json_array['data']['message'] = $result;
    $json_array['data']['exec'] = $exec;
    $json_array['status'] = 1;
    $json_array['blockchain_address'] = $blockchainAddress;

    return new JsonResponse($json_array);

  }

  /**
   * Function adds Drupal 8 asset, returns Json.
   */
  public function requestAccess(Request $request) {
    $json_array = [
      'data' => [],
    ];

    $params = [];
    $content = $request->getContent();

    if (!empty($content)) {
      $params = json_decode($content, TRUE);
    }

    $chain_address = $params['chainAddress'];
    $resource_url = $params['resourceUrl'];
    $email = $params['email'];
    $first_name = $params['firstName'];
    $second_name = $params['secondName'];
    $blockchain = $params['blockchain'];

    // Needs some required fi "mandatory" : 
    // [
    //  "chain-address",
    //  "resource-url",
    //  "email"
    //  ]

    // custom verification logic here

    $parameters[0] = $chain_address;
    $parameters[1] = "activate,connect,create,issue,mine,receive,send";
  
    $message = $this->blockchainController->executeRequest($blockchain, 'grant', $parameters);

    $json_array['data']['message'] = $message;
    $json_array['status'] = 1;
    return new JsonResponse($json_array);
  }

  public function getBlockchainMaster(Request $request) {
    $json_array = [
      'data' => [],
    ];

    $params = [];
    $content = $request->getContent();

    if (!empty($content)) {
      $params = json_decode($content, TRUE);
    }

    $blockchain = $params['blockchain'];
    $parameters[0] = $params['stream'];

    $exec = $this->blockchainController->constructSystemCommandParameters('list_stream_items', $blockchain, $parameters);
    $response = shell_exec($exec);

    $json_array['data']['result'] = json_decode($response);
    $json_array['exec'] = $exec;
    $json_array['status'] = 1;
    $json_array['params'] = $params;

    return new JsonResponse($json_array);
  }


}
