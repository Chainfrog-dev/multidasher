<?php

namespace Drupal\multidasher\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\multidasher\Controller\BlockchainController;
use Drupal\node\Entity\Node;
use Drupal\multidasher\Controller\ManageRequestsController;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\views\Views;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class SendAssetForm.
 */
class SendAssetForm extends FormBase {

  /**
   *
   */
  public function __construct() {
    $this->multichain = new BlockchainController();
    $this->execute = new ManageRequestsController();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'send_asset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['text_area'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajax-wrapper'],
      '#prefix' => '<h2>Address Book</h2>',
    ];

    $form['ajax_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajax-wrapper'],
    ];

    $blockchains = $this->loadBlockchainOptions();
    $options = array_flip($blockchains);
    $option_reset = array_keys($options);
    $first_key = reset($option_reset);
    $form_state->setValue('select_blockchain',$first_key);

    $form['ajax_wrapper']['select_blockchain'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the blockchain'),
      '#options' => $options,
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'ajax-wrapper',
      ],
    ];

    $form['ajax_wrapper']['select_address'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the sender'),
      '#options' => $this->loadAddressOptions($form, $form_state),
      '#attributes' => [
        'id' => ['select-address'],
      ],
    ];

    $form['ajax_wrapper']['select_recipient'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the Recipient'),
      '#options' => $this->loadRecipientOptions($form, $form_state),
      '#attributes' => [
        'id' => ['select-recipient'],
      ],
      '#ajax' => [
        'callback' => '::ajaxCallback2',
        'wrapper' => 'ajax-wrapper-2',
      ],

    ];

    $form['ajax_wrapper']['asset'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the asset to send'),
      '#options' => $this->loadAssetOptions($form, $form_state),
      '#attributes' => [
        'id' => ['select-asset'],
      ],
    ];


    $form['ajax_wrapper']['quantity'] = [
      '#type' => 'number',
      '#title' => $this->t('How much'),
      '#min' => 1,
      '#max' => 21000000,
      '#attributes' => [
        'id' => ['select-qty'],
      ],
    ];

    $form['ajax_wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $form['ajax_wrapper']['select_address'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the wallet address'),
      '#options' => $this->loadAddressOptions($form, $form_state),
      '#attributes' => [
        'id' => ['select-address'],
      ],
    ];
    $form['ajax_wrapper']['asset'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the asset to send'),
      '#options' => $this->loadAssetOptions($form, $form_state),
      '#attributes' => [
        'id' => ['select-asset'],
      ],
    ];
        // Default settings.
    $view = Views::getView('recipients');
    if (is_object($view)) {
        $view->setArguments([$form_state->getValue('select_blockchain')]);
        $view->setDisplay('default');
        $view->preExecute();
        $view->execute();
        $result = $view->result;
        if($result){
        foreach ($result as $key => $value) {
          $node = Node::load(($value->nid));
          $title = $node->label();
          $address = $node->field_recipient_wallet_address->getString();
          $asset = Node::load($node->field_recipient_asset->getString());
          $asset_title = $asset->label();
          $form['ajax_wrapper']['text_area'][$title]['#markup'] = '<div class="row">Title: '.$title.'</div><div class="row">Address: '.$address.'</div><div class="row">Asset: '.$asset_title.'</div>';
          $form['ajax_wrapper']['text_area'][$title]['#prefix'] = '<h3>'.$title.'</h3>';
          $form['ajax_wrapper']['text_area'][$title]['#suffix'] = '</br>';
        }
      }
    }

    $form_state->setRebuild(TRUE);
    return $form['ajax_wrapper'];
  }

  public function loadAddressOptions(array &$form, FormStateInterface &$form_state) {
    $nid = $form_state->getValue('select_blockchain');
    $options = [];

    if(!$nid){
      return $options;
    }
    $node = Node::load($nid);
    $blockchain = $node->field_blockchain_id->getString();
    $multichain = new BlockchainController();
    $exec = $multichain->constructSystemCommand('get_balances', $blockchain); 
    $result = json_decode(shell_exec($exec." &"),true);
    foreach ($result as $key => $value) {
      if($key !== 'total'){
        array_push($options,$key);
      }
    }
    $options = array_combine($options,$options);
    return $options;
  }

  public function loadRecipientOptions(array &$form, FormStateInterface &$form_state) {
    $nid = $form_state->getValue('select_blockchain');
    $options = [];

    if(!$nid){
      return $options;
    }
    $node = Node::load($nid);
    $blockchain = $node->field_blockchain_id->getString();

    $multichain = new BlockchainController();
    $exec = $multichain->constructSystemCommand('list_addresses', $blockchain); 
    $result = json_decode(shell_exec($exec." &"),true);
    foreach ($result as $key => $value) {
      array_push($options,$value['address']);
    }
    $options = array_combine($options,$options);
    return $options;
  }

  public function loadAssetOptions(array &$form, FormStateInterface &$form_state) {
    $nid = $form_state->getValue('select_blockchain');
    $options = [];
    $node = Node::load($nid);
    $blockchain = $node->field_blockchain_id->getString();

    $multichain = new BlockchainController();
    $exec = $multichain->constructSystemCommand('get_balances', $blockchain); 
    $result = json_decode(shell_exec($exec." &"),true);
    foreach ($result as $key => $value) {
      if($key !== 'total'){
        array_push($options,$value[0]['assetref']);
      }
    }
    $options = array_combine($options,$options);
    return $options;
  }



  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nid = $form_state->getValue('select_blockchain');
    $node = Node::load($nid);
    $blockchain = $node->field_blockchain_id->getString();

    $parameters[0] = $form_state->getValue('select_address');
    $parameters[1] = $form_state->getValue('select_recipient');
    $parameters[2] = $form_state->getValue('asset');
    $parameters[3] = +$form_state->getValue('quantity');
    $result = $this->execute->executeRequest($blockchain,'sendassetfrom',$parameters);
    ksm($result);
  }


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