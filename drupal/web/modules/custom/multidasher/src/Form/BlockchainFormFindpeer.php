<?php

namespace Drupal\multidasher\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\multidasher\Controller\BlockchainControllerFindpeer;
use Drupal\multidasher\Controller\BlockchainController;
use Drupal\multidasher\Controller\ReadStdoutController;
use Drupal\Core\Block\BlockPluginInterface;

/**
 *
 */
class BlockchainFormFindpeer extends ConfigFormBase {
  /**
   *
   */
  public function __construct() {
    $this->findPeer = new BlockchainControllerFindpeer();
    $this->readStdout = new ReadStdoutController();
    $this->multichain = new BlockchainController();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'blockchain_form_peer';
  }

  public function defaultConfiguration() {
    $default_config = \Drupal::config('blockchain.settings');
    return [
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Default settings.
    // Form constructor.
    $config = $this->config('blockchain.settings');
    $form = parent::buildForm($form, $form_state);
    // Will print 'en'.
    // print $config->get('langcode');

    // Page title field.
    $form['launch_blockchain']['blockchain_ip'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('blockchain_ip'),
      '#description' => $this->t('Enter the ip of the blockchain you wish to join.'),
    ];
        // Page title field.
    $form['launch_blockchain']['blockchain_port'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('blockchain_port'),
      '#description' => $this->t('Enter the port of the blockchain you wish to join.'),
    ];

    // Page title field.
    $form['launch_blockchain']['blockchain_name'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('blockchain_name'),
      '#description' => $this->t('Enter the name of the blockchain you wish to join.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $port = $form_state->getValue('blockchain_port');
    $ip = $form_state->getValue('blockchain_ip');
    $name = $form_state->getValue('blockchain_name');
    $url = Url::fromRoute('view.dashboard.page_1');
    $form_state->setRedirectUrl($url);
    $wallet = $this->readStdout->retrieveWalletAddress($name);
    $result = $this->findPeer->connectMultichainIp($port, $ip, $name);
    ksm($result);
    $this->config('blockchain.settings')
      ->set('blockchain_port', $form_state->getValue('blockchain_port'))
      ->set('blockchain_ip', $form_state->getValue('blockchain_ip'))
      ->set('blockchain_name', $form_state->getValue('blockchain_name'))
      ->save();
    drupal_set_message('RESULT: '.$wallet);
    parent::submitForm($form, $form_state);
    $result = $this->multichain->launchMultichainDaemon($name);
    $this->multichain->createLoadNode($name);
    $this->multichain->updateAddresses($name);
    parent::submitForm($form, $form_state);
    drupal_set_message('you have connected to the blockchain');
    $this->config('blockchain.settings')
    ->reset();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'blockchain.settings',
    ];
  }



}
