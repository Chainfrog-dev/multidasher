<?php

namespace Drupal\blockpal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
* Defines BlockchainController class.
*/
class BlockchainController extends ControllerBase {

  public function launchMultichain(String $blockchain) {
    $launch_util = $this->launchMultichainUtil($blockchain);
    $launch_daemon = $this->launchMultichainDaemon($blockchain);
    return TRUE;
  }

  public function checkMultichainStatus(String $blockchain){
    system('multichain-cli '.$blockchain. ' -datadir="/var/www/.multichain" listaddresses',  $status);
    return $status;
  }

  public function updateAddresses(String $nodeId = ''){
    $node = $this->blockpalNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $nid = $node->id();

    $result = $this->executeRequest($blockchain,'listaddresses',[]);

      foreach ($result['result'] as $key => $value) {
        $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties(['field_wallet_address' => $value['address']]);
        if (!$node = reset($nodes)) {
          $node = Node::create(['type' => 'blockchain_wallet']);
          $node->set('title', $value['address']);
          $node->set('field_wallet_ismine', TRUE);
          $node->set('field_wallet_address', $value['address']);
          $node->field_wallet_blockchain_ref->target_id = $nid;
          $node->set('uid', 1);
          $node->status = 1;
          $node->enforceIsNew();
          $node->save();
          $wallet_id = $node->id();
        }
        if($node = reset($nodes)){
          $wallet_id = $node->id();
        }
        $this->updateAddressBalances($blockchain, $value['address'], $wallet_id);
      }
    return new RedirectResponse(base_path().'multidash');
  }

  public function getPeerInfo() {
    $node = $this->blockpalNodeLoad('');
    $blockchain = $node->field_blockchain_id->getString();
    $blockchain_nid = $node->id();
    // $result = $this->executeRequest($blockchain, 'getpeerinfo', []);
    $result['result'][0] = json_decode('{
        "id" : 144085,
        "addr" : "172.31.23.199:1985",
        "addrlocal" : "172.31.18.153:37104",
        "services" : "0000000000000001",
        "lastsend" : 1538571441,
        "lastrecv" : 1538571441,
        "bytessent" : 6741887,
        "bytesrecv" : 6745862,
        "conntime" : 1537528933,
        "pingtime" : 0.00694,
        "version" : 70002,
        "subver" : "/MultiChain:0.2.0.4/",
        "handshakelocal" : "1YCwzVAKXWQHivLUygx9QRgCx1yjmnQzhBcuJG",
        "handshake" : "177a32Rif1KVsjCMyQjHmNTWPaUhT8Hwo7US7u",
        "inbound" : false,
        "startingheight" : 29,
        "banscore" : 0,
        "synced_headers" : 200,
        "synced_blocks" : -1,
        "inflight" : [
        ],
        "whitelisted" : false
    }');

    foreach ($result['result'] as $key => $value) {

      $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_peer_address' => $value->addr]);

      if ($node = reset($nodes)) {
        $node->set('field_peer_address', $value->addr);
        $node->set('field_peer_address_local', $value->addrlocal);
        $node->set('field_peer_id', $value->id);
        $node->field_peer_blockchain_ref = ['target_id' => $blockchain_nid];
      } else {

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
    return new RedirectResponse(base_path().'multidash');
  }

  private function updateAddressBalances(String $blockchain, String $address, String $wallet_id){
    $result = $this->executeRequest($blockchain, 'getaddressbalances', [$address]);

    foreach ($result['result'] as $key => $value) {

      $json = json_decode($value['name']);

      $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_asset_name' => $json->name]);

      if ($node = reset($nodes)) {
        $asset_nid = $node->id();
      }

      $wallet = Node::load($wallet_id);
      $wallet->field_wallet_asset_reference[$key] = ['target_id' => $asset_nid];
      $wallet->field_wallet_asset_balance[$key] = $value['qty'];
      $wallet->save();

    }
  }

  public function launchMultichainUtil(String $blockchain){
    system('multichain-util create '.$blockchain. ' -datadir="/var/www/.multichain"',  $status);
    return $status;
  }

  public function launchMultichainDaemon(String $blockchain){
    system('multichaind '.$blockchain.' -datadir="/var/www/.multichain" -daemon > /dev/null 2>&1 &' ,  $status);
    return $status;
  }

  public function stopMultichainDaemon(String $nodeId = ''){
    $node = $this->blockpalNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    system('multichain-cli '.$blockchain.' -datadir="/var/www/.multichain" stop' ,  $status);
    $node->field_status->setValue(FALSE);
    $node->save();
    return new RedirectResponse(base_path().'multidash');
  }

  public function startMultichainDaemon(String $nodeId = ''){
    $node = $this->blockpalNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $result = system('multichaind '.$blockchain.' -datadir="~/.multichain" -daemon > /dev/null 2>&1 &');
    $node->field_status->setValue(TRUE);
    $node->save();
    return new RedirectResponse(base_path().'multidash');
  }

  public function updateParameters() {
    $node = $this->blockpalNodeLoad('');
    $status = $node->field_status->getValue();

    if(!$node) {

      drupal_set_message('Failed to load node','error');
      return new RedirectResponse(base_path().'multidash');

    }

    if($status[0]['value'] == FALSE){

      drupal_set_message('Starting blockchain, Please try again','error');
      $this->startMultichainDaemon($node->id());
      return new RedirectResponse(base_path().'multidash');

    }

    $type_name = $node->type->entity->label();
    if($type_name == 'Blockchain'){

      $userPasswordObject = $this->retrieveUserPassword($node->field_blockchain_id->getString());
      $user = $userPasswordObject['user'];
      $password = $userPasswordObject['password'];

      $portUrlObject = $this->retrievePortUrl($node->field_blockchain_id->getString());
      $port = $portUrlObject['port'];
      $url = $portUrlObject['url'];

      $payload= $this->preparePayload('getinfo');
      $result = $this->sendRequest($url, $payload, $user, $password);

      if(!$result['result']){
        drupal_set_message('No results returned, something went wrong','error');
        return new RedirectResponse(base_path().'multidash');
      }

      foreach ($result['result'] as $key => $value) {
        $node->set('field_'.$key, $value);
      }

      $node->save();
      drupal_set_message( "Node with nid " . $node->id() . " saved!\n");
      return new RedirectResponse(base_path().'multidash');

    }
  }

  private function blockpalNodeLoad(String $nodeId){
    if($nodeId == ''){
      $route_match = \Drupal::service('current_route_match');
      $nodeId = $route_match->getParameter('node');
    }

    $node = Node::load($nodeId);
    return $node;
  }

  public function executeRequest(String $blockchain, String $command, Array $parameters) {
    $userPasswordObject = $this->retrieveUserPassword($blockchain);
    $user = $userPasswordObject['user'];
    $password = $userPasswordObject['password'];

    $portUrlObject = $this->retrievePortUrl($blockchain);
    $port = $portUrlObject['port'];
    $url = $portUrlObject['url'];

    $payload= $this->preparePayload($command, $parameters);
    $response = $this->sendRequest($url,$payload,$user,$password);

    return $response;
  }

  private function preparePayload(String $method, Array $params = []) {

    return json_encode(array(
      'id' => time(),
      'method' => $method,
      'params' => $params,
    ));

  }

  private function sendRequest($url, $payload, $user, $password ) {
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$password);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: '.strlen($payload)
    ));

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    return $result;
  }

  private function retrieveUserPassword(String $blockchain) {
    $directory = '/var/www/.multichain/'.$blockchain.'/';

    if ($fh = fopen($directory.'multichain.conf', 'r')) {
      while (!feof($fh)) {
        $line = fgets($fh);
        if (strpos($line, 'rpcuser=') !== false) {
          $user = preg_replace('/\s+/', '', str_replace('rpcuser=','',$line));
        }
        if (strpos($line, 'rpcpassword=') !== false) {
          $password = preg_replace('/\s+/', '', str_replace('rpcpassword=','',$line));
        }
      }
      fclose($fh);
    }

    $result['user'] = $user;
    $result['password'] = $password;

    return $result;
  }

  private function retrievePortUrl(String $blockchain) {
    $directory = '/var/www/.multichain/'.$blockchain.'/';

    if ($fh = fopen($directory.'params.dat', 'r')) {
      while (!feof($fh)) {
        $line = fgets($fh);
        if (strpos($line, 'default-rpc-port =') !== false) {
          $port = substr(str_replace('default-rpc-port = ','',$line), 0, 4);
          $url='http://localhost:'.$port;
        }
      }
      fclose($fh);
    }

    $result['port'] = $port;
    $result['url'] = $url;

    return $result;
  }

}