<?php

namespace Drupal\multidasher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines BlockchainController class.
 */
class CreateBlockchainController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->blockchainController = new RequestsController();
  }

  /**
   * Iniitate the blockchain
   */
  public function launchBlockchain() {
    $route_match = \Drupal::service('current_route_match');
    $blockchain = $route_match->getParameter('blockchain');

    $exec = $this->blockchainController->constructSystemCommand('create_multichain', $blockchain);
    $response = shell_exec($exec . " &");
    drupal_set_message($result);

    if (!$response) {
      $json_array['data']['status'] = 0;
      return new JsonResponse($json_array);
    }

    if ($response) {
      $json_array['data']['status'] = 1;
      $json_array['data']['params'] = shell_exec('cat < /var/www/.multichain/' . $blockchain . '/params.dat');
      return new JsonResponse($json_array);
    }
  }

  /**
   * Launch the blockchain with Params
   */
  public function createBlockchainParams(Request $request) {
    // Get your POST parameter.
    $params = [];
    $content = $request->getContent();
    if (!empty($content)) {
      $params = json_decode($content, TRUE);
    }
    $params = $params['params'];
    $blockchain = $params['blockchain'];

    $file = '/var/www/.multichain/' . $blockchain . '/params.dat';
    file_save_data($params);
    $fp = fopen($file, 'w+');
    if ($fp) {
      fputs($fp, $params);
      fclose($fp);
    }
    $exec = $this->blockchainController->constructSystemCommand('connect_multichain', $blockchain);
    $result = shell_exec($exec . " &");

    $node = Node::create(['type' => 'params']);
    $node->set('title', $blockchain . '_params');
    $node->set('body', $params);

    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_blockchain_id' => $blockchain]);

    if ($blockchain_node = reset($nodes)) {
      $nid = $blockchain_node->id();
      $node->field_params_blockchain_ref = ['target_id' => $nid];
    }

    $node->status = 1;
    $node->enforceIsNew();
    $node->save();

    if (!$result) {
      $json_array['data']['status'] = 0;
      return new JsonResponse($json_array);
    }
    if ($result) {
      $json_array['data']['status'] = 1;
      $json_array['data']['result'] = $result;
      return new JsonResponse($json_array);
    }

  }

}
