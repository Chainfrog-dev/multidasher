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
class RecepientController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->blockchainController = new BlockchainController();
  }

  /**
   * Export recepients
   */
  public function exportRecepients(String $nodeId = '') {
    $json_array = [
      'data' => [],
    ];

    $node = $this->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $nid = $node->id();

    // Default settings.
    $view = Views::getView('recipients');
    if (is_object($view)) {
      $view->setArguments([$nid]);
      $view->setDisplay('page_1');
      $view->preExecute();
      $view->execute();
      $result = $view->result;
      if ($result) {
        foreach ($result as $key => $value) {
          $recepient = Node::load(($value->nid));
          foreach ($recepient->field_recipient_asset->getValue(['target_id']) as $key => $value) {
            $asset = node::load($value['target_id']);
            $asset_name = $asset->get('title')->value;
          }

          $json_array['data'][$recepient->get('title')->value] = [
            'name' => $recepient->get('title')->value,
            'description' => $recepient->get('body')->value,
            'asset' => $asset_name,
            'address' => $recepient->get('field_recipient_wallet_address')->value,
          ];
        }
      }
    }
    return new JsonResponse($json_array);
  }

  /**
   * add Recepients
   */
  public function addRecepient(Request $request) {
    $json_array = [
      'data' => [],
    ];

    $node = $this->multidasherNodeLoad('');
    $blockchain = $node->field_blockchain_id->getString();
    $blockchain_nid = $node->id();

    $params = [];
    $content = $request->getContent();

    if (!empty($content)) {
      $params = json_decode($content, TRUE);
    }

    $title = $params['title'];
    $description = $params['description'];
    $address = $params['address'];
    $asset_name = $params['asset_name'];

    $json_array['data']['params'] = $params;
    $json_array['data']['address'] = $address;
    $json_array['data']['blockchain_nid'] = $blockchain_nid;

    $recepient = Node::create(['type' => 'recipient']);

    $recepient->field_recipient_blockchain_ref = ['target_id' => $blockchain_nid];
    $recepient->set('body', $description);
    $recepient->set('title', $title);
    $recepient->set('field_recipient_wallet_address', $address);
    $assets = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_asset_name' => $asset_name]);
    if ($asset = reset($assets)) {
      $asset_id = $asset->id();
      $recepient->field_recipient_asset = ['target_id' => $asset_id];
    }
    $recepient->enforceIsNew();
    $recepient->save();
    $json_array['data']['message'] = 'created new recepient';
    $json_array['status'] = 1;
    return new JsonResponse($json_array);
  }

}
