<?php

namespace Drupal\media_entity_ustudio\Form;

use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media_entity_ustudio\uStudio\uStudioFetcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;
use Drupal\Core\Ajax\AjaxResponse;

/**
 * Class uStudioSettings.
 */
class uStudioSettings extends ConfigFormBase {

  /**
   * uStudio Fetcher
   *
   * @var \Drupal\media_entity_ustudio\uStudio\uStudioFetcher
   */
  protected $fetcher;


  public function __construct(ConfigFactoryInterface $config_factory, uStudioFetcher $fetcher)
  {
    $this->fetcher = $fetcher;
    parent::__construct($config_factory);
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('media_entity_ustudio.fetcher')
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
    $form = parent::buildForm($form, $form_state);

    // Helps with Ajax
    $form['#tree'] = TRUE;
    $form_state->setCached(FALSE);

    /**
     * Access Token
     */
    $form['access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('uStudio Access Token'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('access_token'),
    ];

    /**
     * Studios
     */

    // Check for an existing access token and store it for later usage
    $access_token = $config->get('access_token');
    if ($access_token) {

      // Check which studios this token has access to
      $studios = $this->fetcher->retrieveStudios($access_token);

      // If this token has access to studios, show them in a list
      if (!empty($studios)) {
        $form['studio'] = $this->studioSelect($studios);
        if ($studio = $config->get('studio')) {
          $form['studio']['#default_value'] = $studio;
        }
      }
    } else {
      $form['actions']['submit']['#value'] = $this->t('Validate Acccess Token');
    }

    /**
     * Collections And Destinations
     */

    $form['collections_destinations'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['edit-collections-destinations']
      ]
    ];
    // Check for an existing studio and store it for later usage
    $studio = $config->get('studio');
    if ($studio && $access_token) {

      // Collections
      $collections = $this->fetcher->retrieveCollections($access_token, $studio);
      $form['collections_destinations']['collection'] = $this->collectionSelect($collections);
      // If we have an existing collection
      if ($collection = $config->get('collection')) {
        $form['collections_destinations']['collection']['#default_value'] = $collection;
      }

      // Destinations
      $destinations = $this->fetcher->retrieveDestinations($access_token, $studio);
      $form['collections_destinations']['destination'] = $this->destinationSelect($destinations);
      // If we have an existing configuration
      if ($destination = $config->get('destination')) {
        $form['collections_destinations']['destination']['#default_value'] = $destination;
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $access_token = $form_state->getValue('access_token');
    if ($access_token) {
      // Check which studios this token has access to
      $studios = $this->fetcher->retrieveStudios($access_token);
      if (empty($studios)) {
        $form_state->setErrorByName('access_token', $this->t('Invalid Access Token'));
      }
    } else {
      $form_state->setErrorByName('access_token', $this->t('Missing Access Token'));
    }

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


  /**
   * Helper Function to Retrieve Studios and Render the Form Element
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function retrieveStudios(array &$form, FormStateInterface $form_state) : array {

    $access_token = $form_state->getValue('access_token');
    $studios = $this->fetcher->retrieveStudios($access_token);
    $form['studio'] = $this->studioSelect($studios);
    return $form['studio'];
  }

  /**
   * Helper function to build a dropdown select for Studios
   *
   * @param $studios
   * @return array
   */
  protected function studioSelect($studios) {
    return [
      '#type' => 'select',
      '#title' => $this->t('Default Studio'),
      '#options' => $studios,
      '#size' => 1,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::retrieveDestinationsAndCollections',
        'event' => 'change',
        'wrapper' => 'edit-collections-destinations',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Grabbing Studio Destinations and Collections...'),
        ],
      ],
    ];
  }

  /**
   * Helper Function to Retrieve Destinations and Render the Form Element
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function retrieveDestinationsAndCollections(array &$form, FormStateInterface $form_state) : array
  {
    $values = $form_state->getValues();
    $access_token = $values['access_token'];
    $studio = $values['studio'];
    $collections = $this->fetcher->retrieveCollections($access_token, $studio);
    if (!empty($collections)) {
      $form['collections_destinations']['collection'] = $this->collectionSelect($collections);
    }
    $destinations = $this->fetcher->retrieveDestinations($access_token, $studio);
    if (!empty($destinations)) {
      $form['collections_destinations']['destination'] = $this->destinationSelect($destinations);
    }
    return $form['collections_destinations'];
  }

  /**
   * Helper function to build a dropdown select for Collections
   *
   * @param $studios
   * @return array
   */
  protected function collectionSelect($collections) {
    return [
      '#type' => 'select',
      '#title' => $this->t('Default Collection'),
      '#options' => $collections,
      '#size' => 1,
      '#required' => TRUE,
      '#empty_value' => "",
      '#ajax' => FALSE,
    ];
  }

  /**
   * Helper function to build a dropdown select for Destinations
   *
   * @param $studios
   * @return array
   */
  protected function destinationSelect($destinations) {
    dpm('destinationSelect');
    return [
      '#type' => 'select',
      '#title' => $this->t('Default Destination'),
      '#options' => $destinations,
      '#size' => 1,
      '#required' => TRUE,
      '#empty_value' => "",
      '#ajax' => FALSE,
    ];
  }
}
