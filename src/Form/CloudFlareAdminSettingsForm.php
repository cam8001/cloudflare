<?php

/**
 * @file
 * Contains Drupal\cloudflare\Form\DefaultForm.
 */

namespace Drupal\cloudflare\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DefaultForm.
 *
 * @package Drupal\cloudflare\Form
 */
class CloudFlareAdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cloudflare.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cloudflare_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['api_credentials_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => t('Api Credentials'),
    ];

    $config = $this->config('cloudflare.settings');

    $form['api_credentials_fieldset']['apikey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CloudFlare API Key'),
      '#description' => $this->t('Your Api key.  Login to cloudflare here to get it.'),
      '#default_value' => $config->get('apikey'),
      '#required' => TRUE
    ];
    $form['api_credentials_fieldset']['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account Email Address'),
      '#description' => $this->t('Email '),
      '#default_value' => $config->get('email'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $is_email_valid = \Drupal::service('email.validator')->isValid($email);

    if (!$is_email_valid) {
      $form_state->setErrorByName('email', $this->t('Invalid Email Address.  Please enter a valid email address.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('cloudflare.settings')
      ->set('apikey', $form_state->getValue('apikey'))
      ->set('email', $form_state->getValue('email'))
      ->save();
  }

}
