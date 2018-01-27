<?php

namespace Drupal\media_entity_ustudio\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media_entity_ustudio\uStudio\StudiosFetcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;

/**
 * Class uStudioSettings.
 */
class uStudioSettings extends ConfigFormBase {

  /**
   * the Studios Fetcher
   *
   * @var \Drupal\media_entity_ustudio\uStudio\StudiosFetcher
   */
  protected $studios_fetcher;


  public function __construct(ConfigFactoryInterface $config_factory, StudiosFetcher $studios_fetcher)
  {
    $this->studios_fetcher = $studios_fetcher;
    parent::__construct($config_factory);
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('media_entity_ustudio.studios_fetcher')
    );
  }

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
      '#ajax' => [
        'callback' => '::retrieveStudios',
        'event' => 'change',
        'wrapper' => 'edit-studio',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Verifying uStudio Access Token...'),
        ],
      ],
    ];
    $form['studio_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['edit-studio']
      ]
    ];
    $form['studio_container']['studio'] = [
      '#type' => 'select',
      '#title' => $this->t('Studio'),
      '#options' => ['test studio' => $this->t('test studio')],
      '#size' => 1,
      '#default_value' =>  !empty($config->get('studio')) ? $config->get('studio') : '',
      '#required' => TRUE,
      '#empty_value' => '',
      '#ajax' => [
        'callback' => '::retrieveCollections',
        'event' => 'change',
        'wrapper' => 'edit-collection',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Grabbing Collections for Studio...'),
        ],
      ],
    ];
    $form['collection_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['edit-colection']
      ]
    ];
    $form['collection_container']['collection'] = [
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
      ->set('access_token', $form_state->getValue('access_token'))
      ->set('studio', $form_state->getValue('studio'))
      ->set('collection', $form_state->getValue('collection'))
      ->set('destination', $form_state->getValue('destination'))
      ->save();
  }


  public function retrieveStudios(array &$form, FormStateInterface $form_state) : array {
    $access_token = $form_state->getValue('access_token');
    $studios = $this->studios_fetcher->retrieveStudios($access_token);
    $elem = [
      '#type' => 'select',
      '#title' => t('Studio'),
      '#options' => $studios,
      '#size' => 1,
      '#required' => TRUE,
      '#empty_value' => '',
      '#default_value' => '',
      '#ajax' => [
        'callback' => '::retrieveCollections',
        'event' => 'change',
        'wrapper' => 'edit-collection',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Grabbing Collections for Studio...'),
        ],
      ],
    ];
    $form['studio_container']['studio'] = $elem;
    return $form['studio_container'];
  }

  public function retrieveCollections(array &$form, FormStateInterface $form_state) : array {
    dpm('retrieve collections called in form');
    error_log('retrieve collections');
    $access_token = $form_state->getValue('access_token');
    $elem = [
      '#type' => 'select',
      '#title' => t('Collection'),
      '#options' => [1 => 1, 2 => 2, 3 => 3],
      '#size' => 1,
    ];
    $form['collection_container']['collection'] = $elem;
    return $form['collection_container'];
  }
}
