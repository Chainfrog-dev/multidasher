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
class AssetsController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->blockchainController = new RequestsController();
  }

  /**
   * Function adds Drupal 8 asset, returns Json.
   */
  public function addAsset(Request $request) {
    $json_array = [
      'data' => [],
    ];

    $node = $this->blockchainController->multidasherNodeLoad('');
    $blockchain = $node->field_blockchain_id->getString();
    $blockchain_nid = $node->id();

    $params = [];
    $content = $request->getContent();

    if (!empty($content)) {
      $params = json_decode($content, TRUE);
    }

    $title = $params['title'];
    $asset_name = $params['title'];
    $asset_quantity = $params['asset_quantity'];
    $asset_open = $params['open'];
    $recipient = $params['recipient'];
    $description = $params['description'];

    $asset = Node::create(['type' => 'blockchain_asset']);
    $asset->field_asset_blockchain_ref = ['target_id' => $blockchain_nid];
    $asset->set('field_asset_description', $description);
    $asset->set('title', $asset_name);
    $asset->set('field_asset_name', $asset_name);
    $asset->set('field_asset_open', $asset_open);
    $asset->set('field_asset_quantity', $asset_quantity);
    $wallets = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_wallet_address' => $recipient]);
    if ($wallet = reset($wallets)) {
      $wallet_id = $wallet->id();
      $asset->field_asset_issue_address = ['target_id' => $wallet_id];
    }
    $asset->enforceIsNew();
    $asset->save();

    $command = 'issue';
    $parameters[0] = $recipient;
    $parameters[1] = $asset_name;
    $parameters[2] = +$asset_quantity;

    $response = $this->blockchainController->executeRequest($blockchain, $command, $parameters);

    if ($response['error']['message']) {
      $json_array['data']['message'] = $response['error']['message'];
      $json_array['status'] = 0;
      $json_array['parameters'] = $parameters;
    }
    else {
      $json_array['data']['message'] = $response;
      $json_array['status'] = 1;
      $json_array['quantity'] = $asset_quantity;

    }
    return new JsonResponse($json_array);
  }

  /**
   * Function exports Drupal 8 assets, returns Json.
   */
  public function exportAssets(String $nodeId = '') {
    $json_array = [
      'data' => [],
    ];

    $node = $this->blockchainController->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $nid = $node->id();

    // Default settings.
    $view = Views::getView('multidash_assets');
    if (is_object($view)) {
      $view->setArguments([$nid]);
      $view->setDisplay('page_1');
      $view->preExecute();
      $view->execute();
      $result = $view->result;
      if ($result) {
        foreach ($result as $key => $value) {
          $asset = Node::load(($value->nid));
          $json_array['data'][$asset->get('title')->value] = [
            'description' => $asset->get('field_asset_description')->value,
            'name' => $asset->get('title')->value,
          ];
        }
      }
    }
    return new JsonResponse($json_array);
  }

  /**
   * Function loads asset transaction history from Multichain.
   */
  public function loadAssetTransactions(String $nodeId = '') {
    $json_array = [
      'data' => [],
    ];

    $node = $this->blockchainController->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();

    $route_match = \Drupal::service('current_route_match');
    $asset = $route_match->getParameter('asset');

    $exec = $this->blockchainController->constructSystemCommandParameters('list_asset_transactions', $blockchain, [$asset]);
    $response = shell_exec($exec);

    if (!$response) {
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
