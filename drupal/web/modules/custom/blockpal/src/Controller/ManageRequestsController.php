<?php

namespace Drupal\blockpal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\blockpal\Controller\ReadStdoutController;

/**
 * Class ManageRequestsController.
 */
class ManageRequestsController extends ControllerBase {

  public function __construct() {
    $this->readStdout = new ReadStdoutController();
  }


  /**
   *
   */
  public function executeRequest(String $blockchain, String $command, array $parameters) {
    $userPasswordObject = $this->readStdout->retrieveUserPassword($blockchain);
    $user = $userPasswordObject['user'];
    $password = $userPasswordObject['password'];

    $portUrlObject = $this->readStdout->retrievePortUrl($blockchain);
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
    ksm($url);
    ksm($payload);
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

}
