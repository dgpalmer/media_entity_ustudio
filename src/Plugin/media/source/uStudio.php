<?php

namespace Drupal\media_entity_ustudio\Plugin\media\source;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media\MediaInterface;
use Drupal\media\MediaSourceBase;
use Drupal\media\MediaTypeInterface;
use Drupal\media_entity_ustudio\uStudioEmbedFetcher;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\media\MediaSourceFieldConstraintsInterface;

/**
 * uStudio entity media source
 *
 * @MediaSource(
 *   id = "uStudio",
 *   label = @Translation("uStudio"),
 *   description = @Translation("Provides business logic and metadata for uStudio.")
 * )
 */
class uStudio extends MediaSourceBase implements MediaSourceFieldConstraintsInterface {

  /**
   * The uStudio fetcher.
   *
   * @var \Drupal\media_entity_ustudio\uStudioFetcher
   */
  protected $fetcher;

  /**
   * Guzzle client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The field type plugin manager service.
   * @param \Drupal\media_entity_ustudio\uStudioEmbedFetcher $fetcher
   *   uStudio fetcher service.
   * @param \GuzzleHttp\Client $httpClient
   *   Guzzle client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, ConfigFactoryInterface $config_factory, FieldTypePluginManagerInterface $field_type_manager, uStudioEmbedFetcher $fetcher, Client $httpClient) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $entity_field_manager, $field_type_manager, $config_factory);
    $this->fetcher = $fetcher;
    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('config.factory'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('media_entity_ustudio.ustudio_embed_fetcher'),
      $container->get('http_client')
    );
  }

  /**
   * List of validation regular expressions.
   *
   * @var array
   */
  public static $validationRegexp = [
//    '@((http|https):){0,1}//(www\.){0,1}embed\.ustudio\.com/embed/(?<shortcode>[a-z0-9_-]+)@i' => 'shortcode',
//    '@((http|https):){0,1}//(www\.){0,1}embed\.ustudio\.com/embed/(?[a-z0-9_-])/(?<shortcode>[a-z0-9_-]+)@i' => 'shortcode'
      '@((http|https):){0,1}//(www\.){0,1}embed\.ustudio\.com/embed/(?<destination>[^=&?/\r\n]{10,12})@i' => 'destination',
      '@((http|https):){0,1}//(www\.){0,1}embed\.ustudio\.com/embed/[^=&?/\r\n]{10,12}/(?<video>[^=&?/\r\n]{10,12})@i' => 'video',
      '@((http|https):){0,1}//(www\.){0,1}authorized\-embed\.ustudio\.com/embed/(?<destination>[^=&?/\r\n]{10,12})@i' => 'destination',
      '@((http|https):){0,1}//(www\.){0,1}authorized\-embed\.ustudio\.com/embed/[^=&?/\r\n]{10,12}/(?<video>[^=&?/\r\n]{10,12})@i' => 'video'
  ];

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes() {
    return [
      'shortcode' => $this->t('uStudio shortcode'),
      'config' => $this->t('uStudio configuration'),
      'id' => $this->t('Media ID'),
      'type' => $this->t('Media type: livestream or video'),
      'thumbnail' => $this->t('Link to the thumbnail'),
      'thumbnail_local' => $this->t("Copies thumbnail locally and return it's URI"),
      'thumbnail_local_uri' => $this->t('Returns local URI of the thumbnail'),
      'username' => $this->t('Author of the post'),
      'caption' => $this->t('Caption'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(MediaInterface $media, $name) {
    $matches = $this->matchRegexp($media);

    if (!$matches['destination'] || !$matches['video']) {
      return FALSE;
    }

    if ($name == 'destination') {
      return $matches['destination'];
    }

    // If we have auth settings return the other fields.
    if ($ustudio = $this->fetcher->fetchUStudioEmbed($matches['destination'], $matches['video'])) {
      switch ($name) {
        case 'id':
          if (isset($ustudio['media_id'])) {
            return $ustudio['media_id'];
          }
          return FALSE;

        case 'type':
          if (isset($ustudio['type'])) {
            return $ustudio['type'];
          }
          return FALSE;

        case 'thumbnail':
          return (string) $ustudio['thumbnail_url'];

        case 'thumbnail_local':
          $local_uri = $this->getMetadata($media, 'thumbnail_local_uri');

          if ($local_uri) {
            if (file_exists($local_uri)) {
              return $local_uri;
            }
            else {

              $directory = dirname($local_uri);
              file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);

              $image_url = $this->getMetadata($media, 'thumbnail');
              // $image_url = "https://i.imgur.com/Ii7m3W7.jpg";

              try {
                $options = [
                 'verify' => FALSE,
                ];
                $thumbnail = $this->httpClient->request('GET', $image_url, $options);

                file_unmanaged_save_data((string) $thumbnail->getBody(), $local_uri, FILE_EXISTS_REPLACE);
                return $local_uri;
              } catch (\Exception $e) {
                dpm($e->getTraceAsString());
              }
            }
          }
          return FALSE;

        case 'thumbnail_local_uri':
          if (isset($ustudio['thumbnail_url'])) {

            return 'public://' . $this->configFactory->get('media_entity_ustudio.settings')->get('local_images') . '/' . $matches['destination'] . '.' . $matches['video'] . '.' . pathinfo(parse_url($ustudio['thumbnail_url'], PHP_URL_PATH), PATHINFO_EXTENSION);
          }
          return FALSE;

        case 'username':
          if (isset($ustudio['author_name'])) {
            return $ustudio['author_name'];
          }
          return FALSE;

        case 'caption':
          if (isset($ustudio['title'])) {
            return $ustudio['title'];
          }
          return FALSE;
        case 'config':
          if (isset($ustudio['config'])) {
            return $ustudio['config'];
          }

      }
    }

    return FALSE;
  }

  public function getSourceFieldConstraints() {
    return ['uStudioEmbedCode' => []];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $options = [];
    $bundle = $form_state->getFormObject()->getEntity();
    $allowed_field_types = ['string', 'string_long', 'link'];
    foreach ($this->entityFieldManager->getFieldDefinitions('media', $bundle->id()) as $field_name => $field) {
      if (in_array($field->getType(), $allowed_field_types) && !$field->getFieldStorageDefinition()->isBaseField()) {
        $options[$field_name] = $field->getLabel();
      }
    }

    $form['source_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field with source information'),
      '#description' => $this->t('Field on media entity that stores uStudio embed code or URL. You can create a bundle without selecting a value for this dropdown initially. This dropdown can be populated after adding fields to the bundle.'),
      '#default_value' => empty($this->configuration['source_field']) ? NULL : $this->configuration['source_field'],
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function attachConstraints(MediaInterface $media) {
    parent::attachConstraints($media);

    if (isset($this->configuration['source_field'])) {
      $source_field_name = $this->configuration['source_field'];
      if ($media->hasField($source_field_name)) {
        foreach ($media->get($source_field_name) as &$embed_code) {
          /** @var \Drupal\Core\TypedData\DataDefinitionInterface $typed_data */
          $typed_data = $embed_code->getDataDefinition();
          $typed_data->addConstraint('uStudioEmbedCode');
        }
      }
    }
  }

  /**
   * Runs preg_match on embed code/URL.
   *
   * @param \Drupal\media_entity_ustudio\Plugin\MediaEntity\Type\uStudio $media
   *   Media object.
   *
   * @return array|bool
   *   Array of preg matches or FALSE if no match.
   *
   * @see preg_match()
   */
  protected function matchRegexp(MediaInterface $media) {
    $matches = [];
    $_matches = [];

    if (isset($this->configuration['source_field'])) {
      $source_field = $this->configuration['source_field'];
      if ($media->hasField($source_field)) {
        $property_name = $media->{$source_field}->first()->mainPropertyName();
        foreach (static::$validationRegexp as $pattern => $key) {
          if (preg_match($pattern, $media->{$source_field}->{$property_name}, $_matches)) {
            $matches[$key] = $_matches[$key];
            if (!empty($matches['destination']) && !empty($matches['video'])) {
              return $matches;
            }
          }
        }

      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultThumbnail() {
    return $this->config->get('icon_base') . '/ustudio.png';
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnail(MediaInterface $media) {
    if ($local_image = $this->getField($media, 'thumbnail_local')) {
      return $local_image;
    }

    return $this->getDefaultThumbnail();
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultName(MediaInterface $media) {
    // Try to get some fields that need the API, if not available, just use the
    // shortcode as default name.
    $username = $this->getField($media, 'username');
    $id = $this->getField($media, 'id');
    if ($username && $id) {
      return $username . ' - ' . $id;
    }
    else {
      $code = $this->getField($media, 'destination');
      if (!empty($code)) {
        return $code;
      }
    }

    return parent::getDefaultName($media);
  }

}
