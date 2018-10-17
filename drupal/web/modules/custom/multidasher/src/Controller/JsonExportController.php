<?php

namespace Drupal\multidasher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\multidasher\Controller\BlockchainController;
use Symfony\Component\HttpFoundation\Request;
use Drupal\views\Views;

/**
 * Controller for export json.
 */
class JsonExportController extends ControllerBase {
  /**
   * {@inheritdoc}
   */

  public function __construct() {
    $this->blockchainController = new BlockchainController();
  }

  public function exportBlockchains() {
    $json_array = array(
      'data' => array()
    );
    $nids = \Drupal::entityQuery('node')->condition('type','blockchain')->execute();
    $nodes =  Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      $json_array['data'][] = array(
        'type' => $node->get('type')->target_id,
        'id' => $node->get('nid')->value,
        'name' => $node->get('title')->value,
        'description' => $node->get('body')->value
      );
    }
    return new JsonResponse($json_array);
  }

  public function loadStatus(String $nodeId = '') {
    $json_array = array(
      'data' => array()
    );

    $node = $this->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $exec = $this->blockchainController->constructSystemCommand('get_info',$blockchain);

    $response = shell_exec($exec);
    if(!$response){
      $exec = $this->blockchainController->constructSystemCommand('connect_multichain',$blockchain);
      $result = shell_exec($exec." 2>&1 &");

      $json_array['data']['status'] = 0;
      $json_array['data']['response'] = 'didnt start, trying hard reboot';
      return new JsonResponse($json_array);
    }
    if($response){
      $json_array['data']['status'] = 1;
      $json_array['data']['info'] = json_decode($response);
      return new JsonResponse($json_array);
    }
  }

  public function loadAssetTransactions(String $nodeId = '') {
    $json_array = array(
      'data' => array()
    );

    $node = $this->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();

    $route_match = \Drupal::service('current_route_match');
    $asset = $route_match->getParameter('asset');

    $exec = $this->blockchainController->constructSystemCommandParameters('list_asset_transactions',$blockchain, [$asset]);
    $response = shell_exec($exec);

    if(!$response){
      $json_array['data']['status'] = 0;
      $json_array['data']['response'] = 'didnt start, trying hard reboot';
      return new JsonResponse($json_array);
    }
    if($response){
      $json_array['data']['status'] = 1;
      $json_array['data']['info'] = json_decode($response);
      return new JsonResponse($json_array);
    }
  }


  public function launchBlockchain() {
    $route_match = \Drupal::service('current_route_match');
    $blockchain = $route_match->getParameter('blockchain');

    $exec = $this->blockchainController->constructSystemCommand('create_multichain',$blockchain);
    $response = shell_exec($exec." &");
    drupal_set_message($result);

    if(!$response){
      $json_array['data']['status'] = 0;
      return new JsonResponse($json_array);
    }

    if($response){
      $json_array['data']['status'] = 1;
      $json_array['data']['params'] = shell_exec('cat < /var/www/.multichain/'.$blockchain.'/params.dat');
      return new JsonResponse($json_array);
    }
  }

  public function submitParams(Request $request) {
    // get your POST parameter
    $params = array();
    $content = $request->getContent();
    if (!empty($content)) {
      $params = json_decode($content, TRUE);
    }
    $params = $params['params'];
    $blockchain = $params['blockchain'];

    $file = '/var/www/.multichain/'.$blockchain.'/params.dat';
    file_save_data($params);
    $fp = fopen($file, 'w+');
    if ($fp) {
      fputs($fp,$params);
      fclose($fp);
    } 
    $exec = $this->blockchainController->constructSystemCommand('connect_multichain',$blockchain);
    $result = shell_exec($exec." &");

    $node = Node::create(['type' => 'params']);
    $node->set('title', $blockchain.'_params');
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

    if(!$result){
      $json_array['data']['status'] = 0;
      return new JsonResponse($json_array);
    }
    if($result){
      $json_array['data']['status'] = 1;
      $json_array['data']['result'] = $result;
      return new JsonResponse($json_array);
    }

  }

  public function getTotalBalance(String $nodeId = '') {
    $json_array = array(
      'data' => array()
    );

    $node = $this->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $exec = $this->blockchainController->constructSystemCommand('get_balances',$blockchain);
    $response = shell_exec($exec);
    if(!$response){
      $json_array['status'] = 0;
      return new JsonResponse($json_array);
    }
    if($response){
      $json_array['status'] = 1;
      $json = json_decode($response, true);
      foreach ($json as $key => $value) {
        foreach ($value as $key2 => $value2) {
          $json[$key][$key2]['name'] = json_decode($json[$key][$key2]['name'], true);
        }
      }
      $json_array['data'] = $json;
      return new JsonResponse($json_array);
    }
  }

  public function exportWallets(String $nodeId = '') {
    $json_array = array(
      'data' => array()
    );

    $node = $this->multidasherNodeLoad($nodeId);
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
        if($result){
        foreach ($result as $key => $value) {
          $wallet = Node::load(($value->nid));
          $balance = array();
          foreach ($wallet->field_wallet_asset_reference->getValue(['target_id']) as $key => $value) {
            $asset = node::load($value['target_id']);
            $balance_object = array(
              'target_id' => $value['target_id'],
              'value' => $wallet->field_wallet_asset_balance->getValue(['value'])[$key]['value'],
              'name' => $asset->get('title')->value
            );
            array_push($balance,$balance_object);
          }
          $json_array['data'][$wallet->get('title')->value] = array(
            'wallet_id' => $wallet->get('nid')->value,
            'name' => $wallet->get('title')->value,
            'address' => $wallet->get('field_wallet_address')->value,
            'balance' => $balance,
          );
        }
      }
    }
    return new JsonResponse($json_array);
  }

  public function exportRecepients(String $nodeId = '') {
    $json_array = array(
      'data' => array()
    );

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
        if($result){
        foreach ($result as $key => $value) {
          $recepient = Node::load(($value->nid));
          foreach ($recepient->field_recipient_asset->getValue(['target_id']) as $key => $value) {
            $asset = node::load($value['target_id']);
            $asset_name = $asset->get('title')->value;
          }

          $json_array['data'][$recepient->get('title')->value] = array(
            'name' => $recepient->get('title')->value,
            'description' => $recepient->get('body')->value,
            'asset' => $asset_name,
            'address' => $recepient->get('field_recipient_wallet_address')->value,
          );
        }
      }
    }
    return new JsonResponse($json_array);
  }

  public function exportAssets(String $nodeId = '') {
    $json_array = array(
      'data' => array()
    );

    $node = $this->multidasherNodeLoad($nodeId);
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
        if($result){
        foreach ($result as $key => $value) {
          $asset = Node::load(($value->nid));
          $json_array['data'][$asset->get('title')->value] = array(
            'description' => $asset->get('field_asset_description')->value,
            'name' => $asset->get('title')->value
          );
        }
      }
    }
    return new JsonResponse($json_array);
  }

  public function loadWallets(String $nodeId = '') {
    $json_array = array(
      'data' => array()
    );

    $node = $this->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $nid = $node->id();

    $exec = $this->blockchainController->constructSystemCommand('list_addresses',$blockchain);
    $response = json_decode(shell_exec($exec." &"), true);

    if(!$response){
      $json_array['data']['status'] = 0;
      return new JsonResponse($json_array);
    }else{
      $json_array['data']['status'] = 1;
    }

    foreach ($response as $key => $value) {
      if($value['address']){
      $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_wallet_address' => $value['address']]);
      if ($node = reset($nodes)) {
        // $this->updateAddressBalances($blockchain, $value['address'], $wallet_id);
        $wallet_id = $node->id();
        $wallet = Node::load($wallet_id);
        $balance = array();
        foreach ($wallet->field_wallet_asset_reference->getValue(['target_id']) as $key => $value) {
          $asset = node::load($value['target_id']);
          $balance_object = array(
            'target_id' => $value['target_id'],
            'value' => $wallet->field_wallet_asset_balance->getValue(['value'])[$key]['value'],
            'name' => $asset->get('title')->value
          );
          array_push($balance,$balance_object);
        }
        $json_array['data']['wallet'][] = array(
          'wallet_id' => $wallet->get('nid')->value,
          'name' => $wallet->get('title')->value,
          'address' => $wallet->get('title')->value,
          'balance' => $balance,
        );
      }
    }
    }
    return new JsonResponse($json_array);
  }

    /**
   *
   */
  public function updateBlockchainOptions() {
    $json_array = array(
      'data' => array()
    );

    $directory = '/var/www/.multichain';
    $scanned_directory = array_diff(scandir($directory), ['..', '.', '.cli_history', 'multichain.conf','params.dat']);
    $nids = [];
    foreach ($scanned_directory as $key => $value) {
      $nids[$value] = $this->blockchainController->createLoadNode($value);
    }        
    if(!$nids){
      $json_array['data']['status'] = 0;
      return new JsonResponse($json_array);
    }else{
      $json_array['data']['status'] = 1;
      return new JsonResponse($json_array);
    }
  }

  public function addWallet(Request $request) {
    $json_array = array(
      'data' => array()
    );

    $node = $this->multidasherNodeLoad('');
    $blockchain = $node->field_blockchain_id->getString();
    $blockchain_nid = $node->id();

    $params = array();
    $content = $request->getContent();

    if (!empty($content)) {
      $params = json_decode($content, TRUE);
    }

    $title = $params['title'];
    $permissions_list = $params['permissions'];

    $exec = 'get_new_address';
    $multichain = new BlockchainController();
    $command = $multichain->constructSystemCommand($exec, $blockchain);
    $result = shell_exec($command." &");

    $wallet = Node::create(['type' => 'blockchain_wallet']);
    $wallet->set('title', $title);
    $wallet->set('field_wallet_ismine', true);
    $address =  preg_replace('/\s+/', '', $result);
    $wallet->set('field_wallet_address', $address);
    $wallet->field_wallet_blockchain_ref = ['target_id' => $blockchain_nid];
    $wallet->status = 1;
    $wallet->enforceIsNew();

    // Grant permissions to wallet.
    $exec = 'grant';
    $parameters[0] = $address;
    $parameters[1] = $permissions_list;

    $request = new ManageRequestsController();
    $message = $request->executeRequest($blockchain, 'grant', $parameters);
    $wallet->save();
    $json_array['data']['message'] = $message;
    $json_array['status'] = 1;
    return new JsonResponse($json_array);
  }

  private function multidasherNodeLoad(String $nodeId) {
    if ($nodeId == '') {
      $route_match = \Drupal::service('current_route_match');
      $nodeId = $route_match->getParameter('node');
    }

    $node = Node::load($nodeId);
    return $node;
  }

  public function addAsset(Request $request) {
    $json_array = array(
      'data' => array()
    );

    $node = $this->multidasherNodeLoad('');
    $blockchain = $node->field_blockchain_id->getString();
    $blockchain_nid = $node->id();

    $params = array();
    $content = $request->getContent();

    if (!empty($content)) {
      $params = json_decode($content, TRUE);
    }

    $title = $params['title'];
    $asset_name = $params['title'];
    $asset_quantity = $params['asset_quantity'];
    $asset_open = $params['open'];
    $recepient = $params['recepient'];
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
      ->loadByProperties(['field_wallet_address' => $recepient]);
    if ($wallet = reset($wallets)) {
      $wallet_id = $wallet->id();
      $asset->field_asset_issue_address = ['target_id' => $wallet_id];
    }
    $asset->enforceIsNew();
    $asset->save();

    $command = 'issue';
    $parameters[0] = $recepient;
    $parameters[1] = $asset_name;
    $parameters[2] = +$asset_quantity;

    $request = new ManageRequestsController();
    $response = $request->executeRequest($blockchain, $command, $parameters);

    if($response['error']['message']){
      $json_array['data']['message'] = $response['error']['message'];
      $json_array['status'] = 0;
      $json_array['parameters'] = $parameters;
    }else{
      $json_array['data']['message'] = $response;
      $json_array['status'] = 1;
      $json_array['quantity'] = $asset_quantity;

    }
    return new JsonResponse($json_array);
  }

  public function addRecepient(Request $request) {
    $json_array = array(
      'data' => array()
    );

    $node = $this->multidasherNodeLoad('');
    $blockchain = $node->field_blockchain_id->getString();
    $blockchain_nid = $node->id();

    $params = array();
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
      ->loadByProperties(['field_asset_name' =>$asset_name]);
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