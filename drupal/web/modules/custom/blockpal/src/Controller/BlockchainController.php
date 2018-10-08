<?php

namespace Drupal\blockpal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines BlockchainController class.
 */
class BlockchainController extends ControllerBase {

  /**
   *
   */
  public function launchMultichain(String $blockchain) {
    $launch_util = $this->launchMultichainUtil($blockchain);
    $launch_daemon = $this->launchMultichainDaemon($blockchain);
    return TRUE;
  }

  /**
   *
   */
  public function checkMultichainStatus(String $blockchain) {
    system('multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" listaddresses', $status);
    return $status;
  }

  public function connectMultichainIp(String $port, String $ip, String $name) {
    system('multichaind '.$name.'@'.$ip.':'.$port.' -datadir="/var/www/.multichain" > /var/www/.multichain/startup.dat 2>&1 &', $status);
    drupal_set_message('executed '. 'multichaind '.$name.'@'.$ip.':'.$port.' -datadir="/var/www/.multichain" > /var/www/.multichain/startup.dat 2>&1 &');
    return $status;
    // multichaind edtest@207.154.216.254:2893 -datadir="/var/www/.multichain" > ./debug.log 2>&1 & 
  }



  /**
   *
   */
  public function updateAddresses(String $nodeId = '') {
    $node = $this->blockpalNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $nid = $node->id();

    $result = $this->executeRequest($blockchain, 'listaddresses', []);

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
      if ($node = reset($nodes)) {
        $wallet_id = $node->id();
      }
      $this->updateAddressBalances($blockchain, $value['address'], $wallet_id);
    }
    return new RedirectResponse(base_path() . 'multidash');
  }

  /**
   *
   */
  public function getPeerInfo() {
    $node = $this->blockpalNodeLoad('');
    $blockchain = $node->field_blockchain_id->getString();
    $blockchain_nid = $node->id();
    // $result = $this->executeRequest($blockchain, 'getpeerinfo', []);.
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
    return new RedirectResponse(base_path() . 'multidash');
  }

  /**
   *
   */
  private function updateAddressBalances(String $blockchain, String $address, String $wallet_id) {
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

  /**
   *
   */
  public function launchMultichainUtil(String $blockchain) {
    system('multichain-util create ' . $blockchain . ' -datadir="/var/www/.multichain"', $status);
    return $status;
  }

  /**
   *
   */
  public function launchMultichainDaemon(String $blockchain) {
    system('multichaind ' . $blockchain . ' -datadir="/var/www/.multichain" -daemon > /dev/null 2>&1 &', $status);
    return $status;
  }

  /**
   *
   */
  public function stopMultichainDaemon(String $nodeId = '') {
    $node = $this->blockpalNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    system('multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" stop', $status);
    $node->field_status->setValue(FALSE);
    $node->save();
    return new RedirectResponse(base_path() . 'multidash');
  }

  /**
   *
   */
  public function startMultichainDaemon(String $nodeId = '') {
    $node = $this->blockpalNodeLoad($nodeId);
    $blockchain = $node->field_blockchain_id->getString();
    $result = system('multichaind ' . $blockchain . ' -datadir="~/.multichain" -daemon > /dev/null 2>&1 &');
    $node->field_status->setValue(TRUE);
    $node->save();
    return new RedirectResponse(base_path() . 'multidash');
  }

  /**
   *
   */
  public function updateParameters() {
    $node = $this->blockpalNodeLoad('');
    $status = $node->field_status->getValue();

    if (!$node) {

      drupal_set_message('Failed to load node', 'error');
      return new RedirectResponse(base_path() . 'multidash');

    }

    if ($status[0]['value'] == FALSE) {

      drupal_set_message('Starting blockchain, Please try again', 'error');
      $this->startMultichainDaemon($node->id());
      return new RedirectResponse(base_path() . 'multidash');

    }

    $type_name = $node->type->entity->label();
    if ($type_name == 'Blockchain') {

      $userPasswordObject = $this->retrieveUserPassword($node->field_blockchain_id->getString());
      $user = $userPasswordObject['user'];
      $password = $userPasswordObject['password'];

      $portUrlObject = $this->retrievePortUrl($node->field_blockchain_id->getString());
      $port = $portUrlObject['port'];
      $url = $portUrlObject['url'];

      $payload = $this->preparePayload('getinfo');
      $result = $this->sendRequest($url, $payload, $user, $password);

      if (!$result['result']) {
        drupal_set_message('No results returned, something went wrong', 'error');
        return new RedirectResponse(base_path() . 'multidash');
      }

      foreach ($result['result'] as $key => $value) {
        $node->set('field_' . $key, $value);
      }

      $node->save();
      drupal_set_message("Node with nid " . $node->id() . " saved!\n");
      return new RedirectResponse(base_path() . 'multidash');

    }
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

  /**
   *
   */
  public function executeRequest(String $blockchain, String $command, array $parameters) {
    $userPasswordObject = $this->retrieveUserPassword($blockchain);
    $user = $userPasswordObject['user'];
    $password = $userPasswordObject['password'];

    $portUrlObject = $this->retrievePortUrl($blockchain);
    $port = $portUrlObject['port'];
    $url = $portUrlObject['url'];

    $payload = $this->preparePayload($command, $parameters);
    $response = $this->sendRequest($url, $payload, $user, $password);

    return $response;
  }

  /**
   *
   */
  private function preparePayload(String $method, array $params = []) {

    return json_encode([
      'id' => time(),
      'method' => $method,
      'params' => $params,
    ]);

  }

  /**
   *
   */
  private function sendRequest($url, $payload, $user, $password) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $password);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Content-Length: ' . strlen($payload),
    ]);

    $response = curl_exec($ch);
    $result = json_decode($response, TRUE);
    return $result;
  }

  /**
   *
   */
  private function retrieveUserPassword(String $blockchain) {
    $directory = '/var/www/.multichain/' . $blockchain . '/';

    if ($fh = fopen($directory . 'multichain.conf', 'r')) {
      while (!feof($fh)) {
        $line = fgets($fh);
        if (strpos($line, 'rpcuser=') !== FALSE) {
          $user = preg_replace('/\s+/', '', str_replace('rpcuser=', '', $line));
        }
        if (strpos($line, 'rpcpassword=') !== FALSE) {
          $password = preg_replace('/\s+/', '', str_replace('rpcpassword=', '', $line));
        }
      }
      fclose($fh);
    }

    $result['user'] = $user;
    $result['password'] = $password;

    return $result;
  }

  public function retrieveWalletAddress(String $blockchain) {
    $directory = '/var/www/.multichain/' . $blockchain . '/';
    drupal_set_message($directory);
    if ($fh = fopen($directory . 'startup.dat', 'r')) {
      drupal_set_message('file opened');
      while (!feof($fh)) {
        $line = fgets($fh);
        if (strpos($line, 'multichain-cli') !== FALSE) {
          $array = explode(" ", $line);
          $wallet_address = $array[3];
        }
      }
      fclose($fh);
    }

    drupal_set_message('retrieveWalletAddress RESULT: '.$wallet_address);
    return $wallet_address;
  }

  /**
   *
   */
  private function retrievePortUrl(String $blockchain) {
    $directory = '/var/www/.multichain/' . $blockchain . '/';

    if ($fh = fopen($directory . 'params.dat', 'r')) {
      while (!feof($fh)) {
        $line = fgets($fh);
        if (strpos($line, 'default-rpc-port =') !== FALSE) {
          $port = substr(str_replace('default-rpc-port = ', '', $line), 0, 4);
          $url = 'http://localhost:' . $port;
        }
      }
      fclose($fh);
    }

    $result['port'] = $port;
    $result['url'] = $url;

    return $result;
  }

}
