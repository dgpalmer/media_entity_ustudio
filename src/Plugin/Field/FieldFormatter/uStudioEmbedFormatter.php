<?php

namespace Drupal\media_entity_ustudio\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\media_entity_ustudio\Plugin\media\Source\uStudio;
use Drupal\media_entity_ustudio\uStudioEmbedFetcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'uStudio_embed' formatter.
 *
 * @FieldFormatter(
 *   id = "ustudio_embed",
 *   label = @Translation("uStudio embed"),
 *   field_types = {
 *     "link", "string", "string_long"
 *   }
 * )
 */
class uStudioEmbedFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The uStudio fetcher.
   *
   * @var \Drupal\media_entity_ustudio\uStudioEmbedFetcher
   */
  protected $fetcher;

  /**
   * Constructs a uStudioEmbedFormatter instance.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, uStudioEmbedFetcher $fetcher) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->fetcher = $fetcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('media_entity_ustudio.ustudio_embed_fetcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $settings = $this->getSettings();
    foreach ($items as $delta => $item) {
      $matches = [];

      foreach (uStudio::$validationRegexp as $pattern => $key) {
        if (preg_match($pattern, $this->getEmbedCode($item), $item_matches)) {
          $matches[$key] = $item_matches[$key];
        }
      }

      if (!empty($matches['destination']) && !empty($matches['video'])) {

        if ($ustudio = $this->fetcher->fetchUStudioEmbed($matches['destination'], $matches['video'])) {
          $element = [
            '#theme' => 'media_entity_ustudio',
            '#embed' => $ustudio['html'],
            '#destination' => $matches['destination'],
            '#video' => $matches['video'],
          ];
        }
      }
    }

    if (!empty($element)) {
      $element['#attached'] = [
        'library' => [
          'media_entity_ustudio/integration',
        ],
      ];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'width' => NULL,
      'hidecaption' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();

    $summary = [];

    return $summary;
  }

  /**
   * Extracts the raw embed code from input which may or may not be wrapped.
   *
   * @param mixed $value
   *   The input value. Can be a normal string or a value wrapped by the
   *   Typed Data API.
   *
   * @return string|null
   *   The raw embed code.
   */
  protected function getEmbedCode($value) {
    if (is_string($value)) {
      return $value;
    }
    elseif ($value instanceof FieldItemInterface) {
      $class = get_class($value);
      $property = $class::mainPropertyName();
      if ($property) {
        return $value->$property;
      }
    }
  }
}

