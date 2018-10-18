<?php

namespace Drupal\multidasher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Defines BlockchainController class.
 */
class CronController extends ControllerBase {

  /**
   *
   */
  public function __construct() {
    $this->readStdout = new ReadStdoutController();
    $this->manageRequests = new ManageRequestsController();
  }

  /**
   *
   */
  public function createDrupalBlockchains() {
    $json_array = [
      'data' => [],
    ];

    $directory = '/var/www/.multichain';
    $scanned_directory = array_diff(scandir($directory), ['..', '.', '.cli_history', 'multichain.conf', 'params.dat']);
    $nids = [];
    foreach ($scanned_directory as $key => $value) {
      $nids[$value] = $this->createLoadNode($value);
    }
    if (!$nids) {
      $json_array['data']['status'] = 0;
      return new JsonResponse($json_array);
    }
    else {
      $json_array['data']['status'] = 1;
      return new JsonResponse($json_array);
    }
  }

  /**
   *
   */
  public function launchMultichain(String $blockchain) {
    $exec = $this->constructSystemCommand('create_multichain', $blockchain);
    $result = shell_exec($exec . " &");
  }

  /**
   *
   */
  public function startMultichainDaemon(String $nodeId = '') {
    $node = $this->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();

    $exec = $this->constructSystemCommand('connect_multichain', $blockchain);
    $result = shell_exec($exec . " 2>&1 &");
  }

  /**
   *
   */
  public function checkMultichainStatus(String $blockchain) {
    $exec = $this->constructSystemCommand('list_addresses', $blockchain);
    $result = shell_exec($exec . " 2>&1 &");
  }

  /**
   *
   */
  public function updateAddresses(String $nodeId = '') {

    $json_array = [
      'data' => [],
    ];

    $node = $this->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $nid = $node->id();

    $exec = $this->constructSystemCommand('list_addresses', $blockchain);
    $result = json_decode(shell_exec($exec . " &"), TRUE);
    if (!$result) {
      $json_array['status'] = 0;
      $json_array['message'] = 'failed :(';
    }
    else {
      foreach ($result as $key => $value) {
        if ($value['address']) {
          $nodes = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->loadByProperties(['field_wallet_address' => $value['address']]);
          if ($node = reset($nodes)) {
            $wallet_id = $node->id();
            $this->updateAddressBalances($blockchain, $value['address'], $wallet_id);
          }
        }
      }
    }
    $json_array['status'] = 1;
    $json_array['message'] = 'worked!';
    return new JsonResponse($json_array);
  }

  /**
   *
   */
  private function updateAddressBalances(String $blockchain, String $address, String $wallet_id) {
    $exec = $this->constructSystemCommandParameters('get_address_balances', $blockchain, [$address]);
    $result = json_decode(shell_exec($exec . " &"), TRUE);

    foreach ($result as $key => $value) {
      if ($value['name']) {
        $nodes = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadByProperties(['field_asset_name' => $value['name']]);
        if ($node = reset($nodes)) {
          $asset_nid = $node->id();
          $wallet = Node::load($wallet_id);
          $wallet->field_wallet_asset_reference[$key] = ['target_id' => $asset_nid];
          $wallet->field_wallet_asset_balance[$key] = $value['qty'];
          $wallet->save();
        }
      }
    }
  }

  /**
   *
   */
  public function getPeerInfo() {
    $node = $this->multidasherNodeLoad('');
    $blockchain = $node->field_blockchain_id->getString();
    $blockchain_nid = $node->id();

    $exec = $this->constructSystemCommand('get_peer_info', $blockchain);
    $result = json_decode(shell_exec($exec . " &"), TRUE);
    foreach ($result as $key => $value) {

      $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties(['field_peer_address' => $value['addr']]);

      if ($node = reset($nodes)) {
        $node->set('field_peer_address', $value['addr']);
        $node->set('field_peer_address_local', $value['addrlocal']);
        $node->set('field_peer_id', $value['id']);
        $node->field_peer_blockchain_ref = ['target_id' => $blockchain_nid];
      }

      else {

        $node = Node::create(['type' => 'blockchain_peer']);
        $node->set('title', $value['id']);
        $node->set('field_peer_address', $value['addr']);
        $node->set('field_peer_address_local', $value['addrlocal']);
        $node->set('field_peer_id', $value['id']);
        $node->field_peer_blockchain_ref = ['target_id' => $blockchain_nid];
        $node->status = 1;
        $node->enforceIsNew();

      }

      $node->save();

    }
  }

  /**
   *
   */
  public function stopMultichainDaemon(String $nodeId = '') {
    $node = $this->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $exec = $this->constructSystemCommand('stop_multichain', $blockchain);
    $result = shell_exec($exec . " &");
    drupal_set_message($result);
    $node->field_status->setValue(FALSE);
    $node->save();
  }

  /**
   *
   */
  private function createLoadNode($blockchain_id) {
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_blockchain_id' => $blockchain_id]);
    if ($node = reset($nodes)) {
      return $node->id();
    }
    else {
      $node = Node::create(['type' => 'blockchain']);
      $node->set('title', t($blockchain_id));
      $node->set('field_blockchain_id', t($blockchain_id));
      $node->set('uid', 1);
      $node->status = 1;
      $node->enforceIsNew();
      $node->save();
      return $node->id();
    }
  }

}
