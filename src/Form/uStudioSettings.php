<?php

namespace Drupal\media_entity_ustudio\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class uStudioSettings.
 */
class uStudioSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'media_entity_ustudio.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ustudio_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('media_entity_ustudio.settings');
    $form['access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('uStudio Access Token'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('access_token'),
    ];
    $form['studio'] = [
      '#type' => 'select',
      '#title' => $this->t('Studio'),
      '#options' => ['test studio' => $this->t('test studio')],
      '#size' => 1,
      '#default_value' => $config->get('studio'),
    ];
    $form['collection'] = [
      '#type' => 'select',
      '#title' => $this->t('Collection'),
      '#options' => ['test collection' => $this->t('test collection')],
      '#size' => 1,
      '#default_value' => $config->get('collection'),
    ];
    $form['destination'] = [
      '#type' => 'select',
      '#title' => $this->t('Destination'),
      '#options' => ['test destination' => $this->t('test destination')],
      '#size' => 1,
      '#default_value' => $config->get('destination'),
    ];
    return parent::buildForm($form, $form_state);
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
    parent::submitForm($form, $form_state);

    $this->config('media_entity_ustudio.settings')
      ->set('access_token', $form_state->getValue('access_tokens'))
      ->set('studio', $form_state->getValue('studio'))
      ->set('collection', $form_state->getValue('collection'))
      ->set('destination', $form_state->getValue('destination'))
      ->save();
  }
}
