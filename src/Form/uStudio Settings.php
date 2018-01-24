<?php

namespace Drupal\media_entity_ustudio\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class uStudio Settings.
 */
class uStudio Settings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'media_entity_ustudio.ustudio settings',
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
    $config = $this->config('media_entity_ustudio.ustudio settings');
    $form['ustudio_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('uStudio API Key'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('ustudio_api_key'),
    ];
    $form['studio'] = [
      '#type' => 'select',
      '#title' => $this->t('Studio'),
      '#options' => ['test' => $this->t('test')],
      '#size' => 3,
      '#default_value' => $config->get('studio'),
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

    $this->config('media_entity_ustudio.ustudio settings')
      ->set('ustudio_api_key', $form_state->getValue('ustudio_api_key'))
      ->set('studio', $form_state->getValue('studio'))
      ->set('destination', $form_state->getValue('destination'))
      ->save();
  }

}
