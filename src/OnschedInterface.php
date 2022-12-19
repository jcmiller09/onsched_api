<?php

namespace Drupal\onsched_api;

/**
 * Scheduler API Interface.
 */
interface OnschedInterface {

  /**
   * List All Appointments, optionally by user.
   * @param null $user
   */
  public function listAppointments($user = NULL);

  /**
   * Get An Appointment
   * @param $id
   */
  public function getAppointment($id);

  /**
   * Create Appointment.
   * @param $user
   * @param $target
   */
  public function createAppointment($user, $target);

  /**
   * Delete an Appointment.
   * @param $id
   */
  public function deleteAppointment($id);
}
