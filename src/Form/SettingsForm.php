<?php

namespace Drupal\onsched_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure OnSched API settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'onsched_api_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['onsched_api.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['token_api_url_sandbox'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OAuth Token Endpoint (Sandbox)'),
      '#default_value' => $this->config('onsched_api.settings')->get('token_api_url_sandbox'),
    ];
    $form['token_api_url_live'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OAuth Token Endpoint (LIVE)'),
      '#default_value' => $this->config('onsched_api.settings')->get('token_api_url_live'),
    ];
    $form['rest_api_url_sandbox'] = [
      '#type' => 'textfield',
      '#title' => $this->t('REST API Endpoint (Sandbox)'),
      '#default_value' => $this->config('onsched_api.settings')->get('rest_api_url_sandbox'),
    ];
    $form['rest_api_url_live'] = [
      '#type' => 'textfield',
      '#title' => $this->t('REST API Endpoint (LIVE)'),
      '#default_value' => $this->config('onsched_api.settings')->get('rest_api_url_live'),
    ];
    $form['client_id_sandbox'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OAuth Client ID (Sandbox)'),
      '#default_value' => $this->config('onsched_api.settings')->get('client_id_sandbox'),
    ];
    $form['client_id_live'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OAuth Client ID (LIVE)'),
      '#default_value' => $this->config('onsched_api.settings')->get('client_id_live'),
    ];
    $form['client_secret_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret 1 (Sandbox)'),
      '#default_value' => $this->config('onsched_api.settings')->get('client_secret_1'),
    ];
    $form['client_secret_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret 2 (LIVE)'),
      '#default_value' => $this->config('onsched_api.settings')->get('client_secret_2'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if ($form_state->getValue('token_api_url_sandbox')) {
      $form_state->setErrorByName('token_api_url_sandbox', $this->t('The value is not correct.'));
    }
    if ($form_state->getValue('token_api_url_live')) {
      $form_state->setErrorByName('token_api_url_live', $this->t('The value is not correct.'));
    }
    if ($form_state->getValue('rest_api_url_sandbox')) {
      $form_state->setErrorByName('rest_api_url_sandbox', $this->t('The value is not correct.'));
    }
    if ($form_state->getValue('rest_api_url_live')) {
      $form_state->setErrorByName('rest_api_url_live', $this->t('The value is not correct.'));
    }
    if ($form_state->getValue('client_id_sandbox')) {
      $form_state->setErrorByName('client_id_sandbox', $this->t('The value is not correct.'));
    }
    if ($form_state->getValue('client_id_live')) {
      $form_state->setErrorByName('client_id_live', $this->t('The value is not correct.'));
    }
    if ($form_state->getValue('client_secret_1')) {
      $form_state->setErrorByName('client_secret_1', $this->t('The value is not correct.'));
    }
    if ($form_state->getValue('client_secret_2')) {
      $form_state->setErrorByName('client_secret_2', $this->t('The value is not correct.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('onsched_api.settings')
      ->set('token_api_auth_url_sandbox', $form_state->getValue('token_api_auth_url_sandbox'))
      ->set('token_api_auth_url_sandbox', $form_state->getValue('token_api_auth_url_sandbox'))
      ->set('token_api_url_sandbox', $form_state->getValue('token_api_url_sandbox'))
      ->set('token_api_url_live', $form_state->getValue('token_api_url_live'))
      ->set('client_id_sandbox', $form_state->getValue('client_id_sandbox'))
      ->set('client_id_live', $form_state->getValue('client_id_live'))
      ->set('client_secret_1', $form_state->getValue('client_secret_1'))
      ->set('client_secret_2', $form_state->getValue('client_secret_2'))

      ->save();
    parent::submitForm($form, $form_state);
  }

}
