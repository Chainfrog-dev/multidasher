<?php

namespace Drupal\blockpal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\blockpal\Controller\BlockchainController;
use Drupal\Core\Block\BlockPluginInterface;

/**
 *
 */
class BlockchainFormFindpeer extends ConfigFormBase {
  /**
   *
   */
  public function __construct() {
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
      'blockchain_ip' => 'blah',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Default settings.
    // Form constructor.

    $form = parent::buildForm($form, $form_state);
    $config = $this->config('blockchain.settings');
    drupal_set_message($config->get('blockchain_ip'));
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

    // Page title field.
    $form['launch_blockchain']['wallet_address'] = [
      '#type' => 'textfield',
      // '#default_value' => isset($config['blockchain_ip']) ? $config['blockchain_ip'] : '',
      '#description' => $this->t('Your wallet address is.'),
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
    // $url = Url::fromRoute('view.dashboard.page_1');
    // $form_state->setRedirectUrl($url);
    if($form_state->getValue('blockchain_wallet') === null){
      $result = $this->multichain->connectMultichainIp($port, $ip, $name);
      $this->config('blockchain.settings')
      ->set('blockchain_port', $form_state->getValue('blockchain_port'))
      ->set('blockchain_ip', $form_state->getValue('blockchain_ip'))
      ->set('blockchain_name', $form_state->getValue('blockchain_name'))
      ->set('blockchain_wallet', $result)
      ->save();
    }else{
      $result = $this->multichain->launchMultichainDaemon($name);
      $this->createLoadNode($name);
      $this->updateAddresses($name);
      parent::submitForm($form, $form_state);
      drupal_set_message('you have connected to the blockchain')
      ksm($result);
      $this->config('blockchain.settings')
      ->reset();
    }
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
