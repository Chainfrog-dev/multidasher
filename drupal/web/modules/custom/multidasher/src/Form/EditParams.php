<?php

namespace Drupal\multidasher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\multidasher\Controller\BlockchainController;
use Drupal\Core\Url;


/**
 * Class EditParams.
 */
class EditParams extends FormBase {

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
    return 'edit_params';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $route_match = \Drupal::service('current_route_match');
    $blockchain = $route_match->getParameter('name');
    $text = shell_exec('cat < /var/www/.multichain/'.$blockchain.'/params.dat');
    ksm($text);

    $form['params'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Params'),
      '#default_value' => shell_exec('cat < /var/www/.multichain/'.$blockchain.'/params.dat'),
      '#rows' => 50,
      '#weight' => '0',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
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
    $route_match = \Drupal::service('current_route_match');
    $blockchain = $route_match->getParameter('name');
    $file = '/var/www/.multichain/'.$blockchain.'/params.dat';

    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }
    file_save_data($form_state->getValue('params'));
    $fp = fopen($file, 'w+');
    if ($fp) {
      fputs($fp,$form_state->getValue('params'));
      fclose($fp);
    } 
    else {
      drupal_set_message("@rse! That didn't work!");
    }
    $exec = $this->multichain->constructSystemCommand('connect_multichain',$blockchain);
    $result = shell_exec($exec." &");
    drupal_set_message($result);

    $url = Url::fromRoute('view.dashboard.page_1');
    $form_state->setRedirectUrl($url);
  }

}


    // $string = $form_state->getValue('params');
    // $command = 'unlink("'.$file.'")';
    // // $command = 'cat < /var/www/.multichain/'.$blockchain.'/params.dat';
    // drupal_set_message($command);
    // $rm = shell_exec($command);
    // drupal_set_message($rm);
    // $write = shell_exec('file_put_contents('.$file.','.$string.')'. ' &');
    // ksm($write);
