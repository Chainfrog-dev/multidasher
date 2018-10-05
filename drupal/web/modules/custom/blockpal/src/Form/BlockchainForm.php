<?php

namespace Drupal\blockpal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Element;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\blockpal\Controller\BlockchainController;

class BlockchainForm extends ConfigFormBase {
  
  public function __construct(){
    $this->multichain = new BlockchainController;
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

    $form['advanced'] = array(
      '#type' => 'details',
      '#title' => t('Blockchains'),
      '#description' => t('Find all the Blockchains installed on your local machine.'),
      '#open' => TRUE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );

    foreach ($blockchains as $key => $value) {
      $form['advanced'][$key] = [
        '#title' => $this->t($key),
        '#type' => 'link',
        '#url' => Url::fromRoute('entity.node.canonical', ['node' => $value], $options),
        '#prefix' => '<br>',
        '#suffix' => '<br>',
      ];
    }

    $form['launch_blockchain'] = array(
      '#type' => 'details',
      '#title' => t('Blockchains'),
      '#description' => t('Launch a new blockchain!'),
      '#open' => TRUE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );

    // Page title field.
    $form['launch_blockchain']['blockchain_name'] = array(
      '#type' => 'textfield',
      '#description' => $this->t('Choose the name of your new blockchain.'),
    );

    // $form['launch_blockchain']['action'] = [
    //   '#title' => $this->t('Launch blockchain'),
    //   '#type' => 'link',
    //   '#url' => Url::fromRoute('blockchain.launch-blockchain', ['blockchain' => $form_state->getValue('blockchain_name')], $options),
    //   '#prefix' => '<br>',
    //   '#suffix' => '<br>',
    // ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  public function loadNode($blockchain_id) {
    $nodes = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->loadByProperties(['field_blockchain_id' => $blockchain_id]);

    if ($node = reset($nodes)) {
      return $node->id();
    }else{
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $result = $form_state->getValue('blockchain_name');
    drupal_set_message($result);

    $this->multichain->launchMultichain(t($result));
    $this->loadBlockchainOptions();
    drupal_set_message('worked');
    $url = Url::fromRoute('view.dashboard.page_1');
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

  protected function loadBlockchainOptions() {
    $directory = '/var/www/.multichain';
    $scanned_directory = array_diff(scandir($directory), array('..', '.','.cli_history','multichain.conf'));
    $nids = array();
    foreach ($scanned_directory as $key => $value) {
      $nids[$value] = $this->loadNode($value);
    }
    return $nids;
  }

}
