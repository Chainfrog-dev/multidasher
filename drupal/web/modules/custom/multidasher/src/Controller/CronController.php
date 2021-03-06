<?php

namespace Drupal\multidasher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\views\Views;

/**
 * Defines BlockchainController class.
 */
class CronController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->blockchainController = new RequestsController();
  }

  /**
   * Helper function create Drupal nodeas if blockchain exists.
   */
  public function createDrupalBlockchains(Request $request) {
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
      $json_array['data']['message'] = 'blockchains on disk have been mapped to Drupal';
      return new JsonResponse($json_array);
    }
  }

  /**
   *
   */
  public function returnBlockchainNid(Request $request) {
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
    $blockchains = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_blockchain_id' => $blockchain]);
    if ($blockchain_node = reset($blockchains)) {
      $blockchain_nid = $blockchain_node->id();
      $json_array['data']['result'] = $blockchain_nid;
      $json_array['status'] = 1;
      return new JsonResponse($json_array);
    }
    else {
      $json_array['message'] = 'couldnt find blockchain in Drupal';
      $json_array['status'] = 0;
      return new JsonResponse($json_array);
    }

  }

  /**
   * Delete blockchain.
   */
  public function deleteBlockchain(Request $request) {
    $json_array = [
      'data' => [],
    ];

    // Get your POST parameter.
    $params = [];
    $content = $request->getContent();
    if (!empty($content)) {
      $params = json_decode($content, TRUE);
    }

    $nid = $params['nid'];
    $blockchain_node = Node::load($nid);
    $blockchain = $blockchain_node->field_blockchain_id->getString();
    $nid = $blockchain_node->id();

    $exec = $this->blockchainController->constructSystemCommand('stop_multichain', $blockchain);
    $result = shell_exec($exec);
    $json_array['data']['result'] = $result;

    // Delete assets.
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
          $asset->delete();
        }
      }
    }

    // Delete Wallets.
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
          $wallet->delete();
        }
      }
    }

    // Delete recipients.
    $view = Views::getView('recipients');
    if (is_object($view)) {
      $view->setArguments([$nid]);
      $view->setDisplay('page_1');
      $view->preExecute();
      $view->execute();
      $result = $view->result;
      if ($result) {
        foreach ($result as $key => $value) {
          $recipient = Node::load(($value->nid));
          $recipient->delete();
        }
      }
    }

    $blockchain_node->delete();
    $dirPath = '/var/www/.multichain/' . $blockchain;
    shell_exec('rm -rf ' . $dirPath);
    $json_array['data']['status'] = 1;
    $json_array['data']['message'] = $result;
    return new JsonResponse($json_array);
  }

  /**
   * Helper function to launch multichain.
   */
  public function launchMultichain(String $blockchain) {
    $exec = $this->constructSystemCommand('create_multichain', $blockchain);
    $result = shell_exec($exec . " &");
  }

  /**
   * Helper function to start multichain deamon.
   */
  public function startMultichainDaemon(String $nodeId = '') {
    $node = $this->blockchainController->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();

    $exec = $this->constructSystemCommand('connect_multichain', $blockchain);
    $result = shell_exec($exec . " 2>&1 &");
  }

  /**
   * Helper function to check multichain status.
   */
  public function checkMultichainStatus(String $blockchain) {
    $exec = $this->constructSystemCommand('list_addresses', $blockchain);
    $result = shell_exec($exec . " 2>&1 &");
  }

  /**
   * Helper function to update wallets.
   */
  public function updateAddresses(String $nodeId = '') {

    $json_array = [
      'data' => [],
    ];

    $node = $this->blockchainController->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $nid = $node->id();

    $exec = $this->blockchainController->constructSystemCommand('list_addresses', $blockchain);
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
          }else{
            $wallet = Node::create(['type' => 'blockchain_wallet']);
            $wallet->set('title', $value['address']);
            $wallet->set('field_wallet_ismine', TRUE);
            $address = preg_replace('/\s+/', '', $value['address']);
            $wallet->set('field_wallet_address', $address);
            $wallet->field_wallet_blockchain_ref = ['target_id' => $nid];
            $wallet->status = 1;
            $wallet->enforceIsNew();
            $wallet->save();
            $wallet_id = $wallet->id();
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
   * * Returns Json object of all multichain blockchain information. If can't connect to multichain, restarts.
   */
  public function bootstrapBlockchain(Request $request) {
    $json_array = [
      'data' => [],
    ];

    $route_match = \Drupal::service('current_route_match');
    $blockchain = $route_match->getParameter('blockchain');

    $exec = $this->blockchainController->constructSystemCommand('get_info', $blockchain);
    $response = shell_exec($exec);
    if (!$response) {
      $exec = $this->blockchainController->constructSystemCommand('connect_multichain', $blockchain);
      $result = shell_exec($exec . " 2>&1 &");
      $json_array['data']['status'] = 0;
      $json_array['data']['message'] = 'booting Blockchain';
      $json_array['data']['response'] = $result;
      return new JsonResponse($json_array);
    }
    if ($response) {
      $json_array['data']['status'] = 1;
      $json_array['data']['message'] = 'blockchain was already started';
      $json_array['data']['response'] = $response;
      return new JsonResponse($json_array);
    }
  }

  /**
   * Helper function to update balances of addresses.
   */
  private function updateAddressBalances(String $blockchain, String $address, String $wallet_id) {
    $exec = $this->blockchainController->constructSystemCommandParameters('get_address_balances', $blockchain, [$address]);
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
   * Helper function to create Drupal nodes if peer exists.
   */
  public function getPeerInfo() {
    $node = $this->blockchainController->multidasherNodeLoad('');
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
   * Helper function to stop multichain running locally.
   */
  public function stopMultichainDaemon(String $nodeId = '') {
    $node = $this->blockchainController->multidasherNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $exec = $this->constructSystemCommand('stop_multichain', $blockchain);
    $result = shell_exec($exec . " &");
    drupal_set_message($result);
    $node->field_status->setValue(FALSE);
    $node->save();
  }

  /**
   * Helper function to create Drupal nodes if blockchain exists.
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
