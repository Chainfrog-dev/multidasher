<?php

namespace Drupal\multidasher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\multidasher\Controller\BlockchainController;
use Symfony\Component\HttpFoundation\Request;


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


    /**
   *
   */
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


  // /**
  //  *
  //  */
  // private function returnWallets(String $blockchain, String $address, String $wallet_id) {
  //   $exec = $this->blockchainController->constructSystemCommandParameters('get_address_balances',$blockchain,[$address]);
  //   $result = json_decode(shell_exec($exec." &"), true);

  //   foreach ($result as $key => $value) {
  //     $json = json_decode($value['name'], true);
  //     if($json['name']){
  //       $nodes = \Drupal::entityTypeManager()
  //         ->getStorage('node')
  //         ->loadByProperties(['field_asset_name' => $json['name']]);
  //       if ($node = reset($nodes)) {
  //         $asset_nid = $node->id();
  //         $wallet = Node::load($wallet_id);
  //         $wallet->field_wallet_asset_reference[$key] = ['target_id' => $asset_nid];
  //         $wallet->field_wallet_asset_balance[$key] = $value['qty'];
  //         $wallet->save();
  //       }
  //     }
  //   }
  //   return $json;
  // }


  /**
   *
   */
  private function multidasherNodeLoad(String $nodeId) {
    if ($nodeId == '') {
      $route_match = \Drupal::service('current_route_match');
      $nodeId = $route_match->getParameter('node');
    }

    $node = Node::load($nodeId);
    return $node;
  }

}
