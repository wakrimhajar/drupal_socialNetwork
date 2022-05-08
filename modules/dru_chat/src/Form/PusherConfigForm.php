<?php

namespace Drupal\dru_chat\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Pusher Config setting form definitions.
 */
class PusherConfigForm extends ConfigFormBase {

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['dru_chat.settings'];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'dru_chat_pusher_settings_form';
  }

  /**
   * Builds pusher config form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dru_chat.settings');
    $form['app_id'] = [
      '#default_value' => $config->get('app_id'),
      '#description' => $this->t('The app ID as provided by Pusher'),
      '#maxlength' => 60,
      '#required' => TRUE,
      '#title' => $this->t('APP ID'),
      '#type' => 'textfield',
    ];

    $form['auth_key'] = [
      '#default_value' => $config->get('auth_key'),
      '#description' => $this->t('App auth key as provided by Pusher'),
      '#maxlength' => 120,
      '#required' => TRUE,
      '#title' => $this->t('App auth key'),
      '#type' => 'textfield',
    ];

    $form['secret'] = [
      '#default_value' => $config->get('secret'),
      '#description' => $this->t('The app secret as provided by Pusher'),
      '#title' => $this->t('app secret'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#maxlenght' => 120,
    ];


    $form['cluster'] = [
      '#default_value' => $config->get('cluster'),
      '#description' => $this->t('The app cluster key as provided by Pusher eg ap2'),
      '#title' => $this->t('Cluster'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#maxlenght' => 40,
    ];

    /*$form['chat_settings'] = [
      '#type' => 'select',
      '#title' => $this->t('Visitors Chat settings'),
      '#options' => ['yes' => 'Yes', 'no' => 'No'],
      '#description' => $this->t('Should un-logged in users use chat!?'),
      '#default_value' => $config->get('chat_settings'),
      '#required' => TRUE,
    ];*/
    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('dru_chat.settings');
    $config
      ->set('app_id', $form_state->getValue('app_id'))
      ->set('auth_key', $form_state->getValue('auth_key'))
      ->set('secret', $form_state->getValue('secret'))
      ->set('cluster', $form_state->getValue('cluster'))
      ->set('chat_settings', $form_state->getValue('chat_settings'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
