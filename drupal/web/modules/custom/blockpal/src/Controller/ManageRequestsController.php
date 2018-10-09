<?php

namespace Drupal\blockpal\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ManageRequestsController.
 */
class ManageRequestsController extends ControllerBase {

  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function hello($name) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: hello with parameter(s): $name'),
    ];
  }

}
