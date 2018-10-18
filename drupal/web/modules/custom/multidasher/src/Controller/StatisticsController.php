<?php

namespace Drupal\multidasher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for export json.
 */
class StatisticsController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->blockchainController = new RequestsController();
  }

  /**
   * Returns Json object of all blockchain nodes.
   */
  public function exportBlockchains() {
    $json_array = [
      'data' => [],
    ];
    $nids = \Drupal::entityQuery('node')->condition('type', 'blockchain')->execute();
    $nodes = Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      $json_array['data'][] = [
        'type' => $node->get('type')->target_id,
        'id' => $node->get('nid')->value,
        'name' => $node->get('title')->value,
        'description' => $node->get('body')->value,
      ];
    }
    return new JsonResponse($json_array);
  }

  /**
   * * Returns Json object of all multichain blockchain information. If can't connect to multichain, restarts.
   */
  public function loadStatus(String $nodeId = '') {
    $json_array = [
      'data' => [],
    ];

    $node = $this->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $exec = $this->blockchainController->constructSystemCommand('get_info', $blockchain);

    $response = shell_exec($exec);
    if (!$response) {
      $exec = $this->blockchainController->constructSystemCommand('connect_multichain', $blockchain);
      $result = shell_exec($exec . " 2>&1 &");

      $json_array['data']['status'] = 0;
      $json_array['data']['response'] = 'didnt start, trying hard reboot';
      return new JsonResponse($json_array);
    }
    if ($response) {
      $json_array['data']['status'] = 1;
      $json_array['data']['info'] = json_decode($response);
      return new JsonResponse($json_array);
    }
  }

}
