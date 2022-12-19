<?php

namespace Drupal\onsched_api\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for OnSched API routes.
 */
class OnSchedApiController extends ControllerBase {

  /**
   * Create a page to test retrieval of scheduled and past appointments
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('List of all appointments'),
    ];

    // TODO obv grab some appointments!

    return $build;
  }

}
