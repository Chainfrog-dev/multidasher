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
class WalletController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->blockchainController = new RequestsController();
  }

  /**
   * Returns Json of all wallets on a blockchain.
   */
  public function exportWallets(String $nodeId = '') {
    $json_array = [
      'data' => [],
    ];

    $node = $this->blockchainController->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $nid = $node->id();

    // Default settings.
    $view = Views::getView('multidasher_wallet');
    if (is_object($view)) {
      $view->setArguments([$nid]);
      $view->setDisplay('page_1');
      $view->preExecute();
      $view->execute();
      $result = $view->result;
      if ($result) {
        foreach ($result as $key => $value) {
          $wallet = Node::load(($value->nid));
          $balance = [];
          foreach ($wallet->field_wallet_asset_reference->getValue(['target_id']) as $key => $value) {
            $asset = node::load($value['target_id']);
            $balance_object = [
              'target_id' => $value['target_id'],
              'value' => $wallet->field_wallet_asset_balance->getValue(['value'])[$key]['value'],
              'name' => $asset->get('title')->value,
            ];
            array_push($balance, $balance_object);
          }
          $json_array['data'][$wallet->get('title')->value] = [
            'wallet_id' => $wallet->get('nid')->value,
            'name' => $wallet->get('title')->value,
            'address' => $wallet->get('field_wallet_address')->value,
            'balance' => $balance,
          ];
        }
      }
    }
    return new JsonResponse($json_array);
  }

  /**
   * Returns total balance on a blockchain.
   */
  public function getTotalBalance(String $nodeId = '') {
    $json_array = [
      'data' => [],
    ];

    $node = $this->blockchainController->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $exec = $this->blockchainController->constructSystemCommand('get_balances', $blockchain);
    $response = shell_exec($exec);
    if (!$response) {
      $json_array['status'] = 0;
      return new JsonResponse($json_array);
    }
    if ($response) {
      $json_array['status'] = 1;
      $json = json_decode($response, TRUE);
      foreach ($json as $key => $value) {
        foreach ($value as $key2 => $value2) {
          $json[$key][$key2]['name'] = json_decode($json[$key][$key2]['name'], TRUE);
        }
      }
      $json_array['data'] = $json;
      return new JsonResponse($json_array);
    }
  }

  /**
   * Adds a wallet to Drupal, returns Json.
   */
  public function addWallet(Request $request) {
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
    $permissions_list = $params['permissions'];

    $exec = 'get_new_address';
    $command = $this->blockchainController->constructSystemCommand($exec, $blockchain);
    $result = shell_exec($command . " &");

    $wallet = Node::create(['type' => 'blockchain_wallet']);
    $wallet->set('title', $title);
    $wallet->set('field_wallet_ismine', TRUE);
    $address = preg_replace('/\s+/', '', $result);
    $wallet->set('field_wallet_address', $address);
    $wallet->field_wallet_blockchain_ref = ['target_id' => $blockchain_nid];
    $wallet->status = 1;
    $wallet->enforceIsNew();

    // Grant permissions to wallet.
    $exec = 'grant';
    $parameters[0] = $address;
    $parameters[1] = $permissions_list;
    $message = $this->blockchainController->executeRequest($blockchain, 'grant', $parameters);
    $wallet->save();
    $json_array['data']['message'] = $message;
    $json_array['status'] = 1;
    return new JsonResponse($json_array);
  }

}
