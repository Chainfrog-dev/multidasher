<?php

namespace Drupal\multidasher\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ReadStdoutController.
 */
class ReadStdoutController extends ControllerBase {

  /**
  *
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

  public function retrieveWalletAddress(String $blockchain) {
    $directory = '/var/www/multidasher/' . $blockchain . '.dat';
    drupal_set_message($directory);
    ksm(file($directory));
    if ($fh = fopen($directory, 'r')) {
      drupal_set_message('file opened');
      while (!feof($fh)) {
        $line = fgets($fh);
        drupal_set_message('LINE '.$line);
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
