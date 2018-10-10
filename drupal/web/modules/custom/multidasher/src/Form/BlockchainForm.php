<?php

namespace Drupal\multidasher\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\multidasher\Controller\BlockchainController;

/**
 *
 */
class BlockchainForm extends ConfigFormBase {

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
    return 'blockchain_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Default settings.
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    $options = ['absolute' => TRUE];
    $blockchains = $this->loadBlockchainOptions();

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => t('Blockchains'),
      '#description' => t('Find all the Blockchains installed on your local machine.'),
    // Controls the HTML5 'open' attribute. Defaults to FALSE.
      '#open' => TRUE,
    ];

    foreach ($blockchains as $key => $value) {
      $form['advanced'][$key] = [
        '#title' => $this->t($key),
        '#type' => 'link',
        '#url' => Url::fromRoute('entity.node.canonical', ['node' => $value], $options),
        '#prefix' => '<br>',
        '#suffix' => '<br>',
      ];
    }

    $form['launch_blockchain'] = [
      '#type' => 'details',
      '#title' => t('Blockchains'),
      '#description' => t('Launch a new blockchain!'),
      '#open' => TRUE,
    ];

    // Page title field.
    $form['launch_blockchain']['blockchain_name'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Choose the name of your new blockchain.'),
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
    $result = $form_state->getValue('blockchain_name');
    drupal_set_message($result);

    $this->multichain->launchMultichain($result);
    $this->loadBlockchainOptions();
    $url = Url::fromRoute('multidasher.edit_params',['name' => $form_state->getValue('blockchain_name')]);
    $form_state->setRedirectUrl($url);

  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'blockchain.settings',
    ];
  }

  /**
   *
   */
  protected function loadBlockchainOptions() {
    $directory = '/var/www/.multichain';
    $scanned_directory = array_diff(scandir($directory), ['..', '.', '.cli_history', 'multichain.conf']);
    $nids = [];
    foreach ($scanned_directory as $key => $value) {
      $nids[$value] = $this->multichain->createLoadNode($value);
    }
    return $nids;
  }

}
