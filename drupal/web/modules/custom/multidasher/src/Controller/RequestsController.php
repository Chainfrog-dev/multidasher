<?php

namespace Drupal\multidasher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Controller for export json.
 */
class RequestsController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function constructSystemCommand(String $identifier, String $blockchain) {
    $commands = [
      'connect_multichain' => 'multichaind ' . $blockchain . ' -datadir="/var/www/.multichain" -daemon > /dev/null 2>&1 &',
      'connect_external_multichain' => 'multichaind ' . $blockchain . ' -datadir="/var/www/.multichain" -daemon',
      'create_multichain' => 'multichain-util create ' . $blockchain . ' -datadir="/var/www/.multichain"',
      'get_new_address' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" getnewaddress',
      'get_balances' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" getmultibalances',
      'get_info' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" getinfo',
      'get_peer_info' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" getpeerinfo',
      'list_addresses' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" listaddresses',
      'stop_multichain' => 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" stop',
    ];
    return $commands[$identifier];
  }

  /**
   * Construct complicated requests
   */
  public function constructSystemCommandParameters(String $identifier, String $blockchain, array $parameters) {
    switch ($identifier) {
      case 'get_address_balances':
        return 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" getaddressbalances "' . $parameters[0] . '"';

      break;
      case 'list_asset_transactions':
        return 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" listassettransactions "' . $parameters[0] . '"';
      break;

      case 'list_stream_items':
        return 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" liststreamitems "' . $parameters[0] . '"';
      break;

      case 'grant':
        return 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" grant "' . $parameters[0] . '" ' . $parameters[1];
      break;

      case 'revoke':
        return 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" revoke "' . $parameters[0] . '" ' . $parameters[1];
      case 'publish':
        return 'multichain-cli ' . $blockchain . ' -datadir="/var/www/.multichain" publish ' . $parameters[0] . ' "'.$parameters[1] . '" '.  $parameters[2];
      break;
      default:
        return NULL;
      break;
    }
  }

  /**
   * Execture requests
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
   * Prepare payload
   */
  private function preparePayload(String $method, array $params = []) {

    return json_encode([
      'id' => time(),
      'method' => $method,
      'params' => $params,
    ]);

  }

  /**
   * Send request
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
   * Load the Blockchain based on route
   */
  public function multidasherNodeLoad(String $nodeId) {
    if ($nodeId == '') {
      $route_match = \Drupal::service('current_route_match');
      $nodeId = $route_match->getParameter('node');
    }
    $node = Node::load($nodeId);
    return $node;
  }

  /**
   * Retrieve user / password from file
   */
  public function retrieveUserPassword(String $blockchain) {
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

  /**
   * retrieve wallet address from file
   */
  public function retrieveWalletAddress(String $message) {

    $separator = "\r\n";
    $line = strtok($message, $separator);

    while ($line !== FALSE) {
      // Do something with $line.
      $line = strtok($separator);
      if (strpos($line, 'multichain-cli') !== FALSE) {
        $array = explode(" ", $line);
        $wallet_address = $array[3];
      }
    }

    if (!$wallet_address) {
      drupal_set_message('retrieveWalletAddress didnt get it', 'error');
    }
    return $wallet_address;
  }

  /**
   * Retrieve port url from file
   */
  public function retrievePortUrl(String $blockchain) {
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
