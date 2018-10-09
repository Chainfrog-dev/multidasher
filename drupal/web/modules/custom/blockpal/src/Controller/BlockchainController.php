<?php

namespace Drupal\blockpal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\blockpal\Controller\ReadStdoutController;
use Drupal\blockpal\Controller\ManageRequestsController;

/**
 * Defines BlockchainController class.
 */
class BlockchainController extends ControllerBase {

  public function __construct() {
    $this->readStdout = new ReadStdoutController();
    $this->manageRequests = new ManageRequestsController();
  }

  public function constructSystemCommand(String $identifier, String $blockchain) {
    $commands = array(
      'connect_multichain' => 'multichaind ' . $blockchain . ' -datadir="/var/www/.multichain" -daemon > /dev/null 2>&1 &',
      'create_multichain' => 'multichain-util create ' . $blockchain . ' -datadir="/var/www/.multichain"',
      'get_new_address' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" getnewaddress',
      'get_balances' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" getmultibalances',      
      'get_info' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" getinfo',
      'get_peer_info' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" getinfo',
      'list_addresses' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" listaddresses',
      'stop_multichain' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" stop',
    );
    return $commands[$identifier];
  }

  public function constructSystemCommandParameters(String $identifier, String $blockchain, Array $parameters) {
    switch ($identifier) {
      case 'get_address_balances':
        return 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" getaddressbalances "' . $parameters[0] . '"';
        break;
      case 'grant':
        return 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" grant "' . $parameters[0] . '" ' . $parameters[1];
        break;
      case 'revoke':
        return 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" revoke "' . $parameters[0] . '" ' . $parameters[1];
        break;
      default:
        return null;
        break;
    }  
  }

  /**
   *
   */
  public function launchMultichain(String $blockchain) {
    $exec = $this->constructSystemCommand('create_multichain',$blockchain);
    $result = shell_exec($exec." &");
    drupal_set_message($result);

    $exec = $this->constructSystemCommand('connect_multichain',$blockchain);
    $result = shell_exec($exec." &");
    drupal_set_message($result);

    return new RedirectResponse(base_path() . 'multidasher');
  }

  /**
   *
   */
  public function startMultichainDaemon(String $nodeId = '') {
    $node = $this->blockpalNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();

    $exec = $this->constructSystemCommand('connect_multichain',$blockchain);
    $result = shell_exec($exec." 2>&1 &");
    drupal_set_message($result);

    return new RedirectResponse(base_path() . 'multidasher');
  }


  /**
   *
   */
  public function checkMultichainStatus(String $blockchain) {
    $exec = $this->constructSystemCommand('list_addresses',$blockchain);
    $result = shell_exec($exec." 2>&1 &");
    drupal_set_message($result);

    return $result;
  }

  public function connectMultichainIp(String $port, String $ip, String $name) {
    // Special construction as requires extra parameters
    $command = 'multichaind '.$name.'@'.$ip.':'.$port.' -datadir="/var/www/.multichain"';
    $result = shell_exec($command." 2>&1 &" );
    return TRUE;
  }

  /**
   *
   */
  public function updateAddresses(String $nodeId = '') {
    $node = $this->blockpalNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $nid = $node->id();

    $exec = $this->constructSystemCommand('list_addresses',$blockchain);
    $result = json_decode(shell_exec($exec." &"), true);
    if(!$result) return new RedirectResponse(base_path() . 'multidasher/'.$nid.'/wallets');;
    foreach ($result as $key => $value) {
      if($value['address']){
      $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_wallet_address' => $value['address']]);
      if ($node = reset($nodes)) {
        $wallet_id = $node->id();
        $this->updateAddressBalances($blockchain, $value['address'], $wallet_id);
      }
    }
  }
    return new RedirectResponse(base_path() . 'multidasher/'.$nid.'/wallets');
  }

  /**
   *
   */
  private function updateAddressBalances(String $blockchain, String $address, String $wallet_id) {
    $exec = $this->constructSystemCommandParameters('get_address_balances',$blockchain,[$address]);
    $result = json_decode(shell_exec($exec." &"), true);

    foreach ($result as $key => $value) {
      $json = json_decode($value['name'], true);
      ksm($json);
      if($json['name']){
        $nodes = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadByProperties(['field_asset_name' => $json['name']]);
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
    $node = $this->blockpalNodeLoad('');
    $blockchain = $node->field_blockchain_id->getString();
    $blockchain_nid = $node->id();

    $exec = $this->constructSystemCommand('get_peer_info', $blockchain);
    $result = json_decode(shell_exec($exec." &"));

    foreach ($result as $key => $value) {

      $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties(['field_peer_address' => $value->addr]);

      if ($node = reset($nodes)) {
        $node->set('field_peer_address', $value->addr);
        $node->set('field_peer_address_local', $value->addrlocal);
        $node->set('field_peer_id', $value->id);
        $node->field_peer_blockchain_ref = ['target_id' => $blockchain_nid];
      }

      else {

        $node = Node::create(['type' => 'blockchain_peer']);
        $node->set('title', $value->id);
        $node->set('field_peer_address', $value->addr);
        $node->set('field_peer_address_local', $value->addrlocal);
        $node->set('field_peer_id', $value->id);
        $node->field_peer_blockchain_ref = ['target_id' => $blockchain_nid];
        $node->status = 1;
        $node->enforceIsNew();

      }

      $node->save();

    }
    return new RedirectResponse(base_path() . 'multidasher');
  }


  /**
   *
   */
  public function stopMultichainDaemon(String $nodeId = '') {
    $node = $this->blockpalNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $exec = $this->constructSystemCommand('stop_multichain',$blockchain);
    $result = shell_exec($exec." &");
    drupal_set_message($result);
    $node->field_status->setValue(FALSE);
    $node->save();
    return new RedirectResponse(base_path() . 'multidasher');
  }

  /**
   *
   */
  public function updateParameters() {
    $node = $this->blockpalNodeLoad('');
    $type_name = $node->type->entity->label();
    $status = $node->field_status->getValue();
    $blockchain = $node->field_blockchain_id->getString();

    if (!$node || $type_name !== 'Blockchain') {
      drupal_set_message('Failed to load node', 'error');
      return new RedirectResponse(base_path() . 'multidasher');
    }

    if ($status[0]['value'] == FALSE) {
      $exec = 'connect_multichain';
      $command = $this->constructSystemCommand($exec, $blockchain);
      drupal_set_message('Starting blockchain, Please try again', 'error');
      $result = shell_exec($command." 2>&1 &");
      $node->field_status->setValue(TRUE);
      $node->save();
      return new RedirectResponse(base_path() . 'multidasher');
    }

    $exec = 'get_info';
    $parameters = [];
    $command = $this->constructSystemCommand($exec, $blockchain);
    $result = json_decode(shell_exec($command." &"));

    if (!$result) {
      drupal_set_message('No results returned, something went wrong', 'error');
      return new RedirectResponse(base_path() . 'multidasher');
    }

    foreach ($result as $key => $value) {
      $node->set('field_' . $key, $value);
      if($key == 'port'){
        $result = exec('ufw allow in '.$value.'/tcp comment "Multichain connections"');
        drupal_set_message('Multichain port '.$value.' opened in UFW');
      }
    }

    $node->save();
    drupal_set_message("Node with nid " . $node->id() . " saved!\n");
    return new RedirectResponse(base_path() . 'multidasher');

  }

  /**
   *
   */
  private function blockpalNodeLoad(String $nodeId) {
    if ($nodeId == '') {
      $route_match = \Drupal::service('current_route_match');
      $nodeId = $route_match->getParameter('node');
    }

    $node = Node::load($nodeId);
    return $node;
  }

  /**
   *
   */
  public function createLoadNode($blockchain_id) {
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
