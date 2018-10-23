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
class StreamsController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->blockchainController = new RequestsController();
  }

  /**
   * Returns Json of all wallets on a blockchain.
   */
  public function publishStream(Request $request) {
    $json_array = [
      'data' => [],
    ];

    // Get your POST parameter.
    $params = [];
    $content = $request->getContent();
    if (!empty($content)) {
      $params = json_decode($content, TRUE);
    }

    $blockchain = $params['blockchain'];
    $stream = $params['stream'];
    $key = $params['key'];
    $message = $params['message'];

    $parameters[0] = $stream;
    $parameters[1] = $key;
    $parameters[2] = "'".json_encode($message)."'";
    
    $exec = $this->blockchainController->constructSystemCommandParameters('publish', $blockchain, $parameters);
    $result = shell_exec($exec);
    if(!$result){
      $json_array['data']['status'] = 0;
      $json_array['data']['message'] = 'something went wrong';
      $json_array['data']['exec'] = $exec;
    }
    if($result){
      $json_array['data']['status'] = 1;
      $json_array['data']['message'] = $result;
      $json_array['data']['params'] = $parameters;
      $json_array['data']['exec'] = $exec;
    }
    return new JsonResponse($json_array);
  }


}
