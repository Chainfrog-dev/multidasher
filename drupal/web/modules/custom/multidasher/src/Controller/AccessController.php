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

    $directory = '/var/www/.multichain/' . $blockchain;

    if (is_dir($directory)) {
      $command = $this->blockchainController->constructSystemCommand('connect_multichain', $blockchain);
      $result = shell_exec($command);
    }

    else{
      $command = $this->blockchainController->constructSystemCommand('connect_external_multichain', $blockchain_address);
      $result = shell_exec($command);
    }
      
    fclose($fh);
    $json_array['status'] = 1;
    $json_array['blockchain_address'] = $blockchain_address;
    $json_array['chain_address'] = $chain_address;
    $json_array['port'] = $port;
    $json_array['blockchain'] = $blockchain;
    $json_array['data']['directory'] = $directory;
    $json_array['data']['exec'] = $command;
    $json_array['data']['message'] = $result;
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
    $parameters[1] = 'sign-up';
    $parameters[2] = 'true';
    $parameters[3] = 1;
    $parameters[4] = 0;
    $parameters[5] = 'false';

    // $response = $this->blockchainController->executeRequest($blockchain,'liststreamkeyitems', $parameters);
    $command = $this->blockchainController->constructSystemCommandParameters('list_stream_key_items', $blockchain, $parameters);
    $response = shell_exec($command);

    $json_array['data']['result'] = json_decode($response);
    $json_array['exec'] = $command;
    $json_array['status'] = 1;
    $json_array['response'] = $response;

    $json_array['params'] = $parameters;

    return new JsonResponse($json_array);
  }

  public function getMasterJson(Request $request) {
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
    $parameters[1] = $params['author'];
    $parameters[2] = 'true';
    $parameters[3] = 1;
    $parameters[4] = -1;

    $response = $this->blockchainController->executeRequest($blockchain,'liststreampublisheritems', $parameters);

    $json_array['data']['result'] = json_decode($response);
    // $json_array['exec'] = $exec;
    $json_array['response'] = $response;
    $json_array['status'] = 1;
    $json_array['params'] = $parameters;

    return new JsonResponse($json_array);
  }

  public function getMasterAddress(Request $request) {
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
    $parameters[1] = $params['author'];
    $parameters[2] = 'true';
    $parameters[3] = 1;
    $parameters[4] = -1;

    $command = $this->blockchainController->constructSystemCommand('list_addresses', $blockchain);
    $response = shell_exec($command);

    $json_array['data']['result'] = json_decode($response);
    $json_array['exec'] = $command;
    $json_array['response'] = $response;
    $json_array['status'] = 1;

    return new JsonResponse($json_array);
  }


}
