<?php

namespace Drupal\media_entity_ustudio\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\file\Entity\File;

/**
 * Defines the 'path' entity field type.
 *
 * @FieldType(
 *   id = "ustudio_upload",
 *   label = @Translation("uStudio Upload"),
 *   description = @Translation("An entity field for uploading a uStudio Video."),
 *   no_ui = TRUE,
 *   default_widget = "ustudio_upload",
 *   list_class = "\Drupal\media_entity_ustudio\Plugin\Field\FieldType\uStudioUploadFieldItemList",
 * )
 */
class uStudioUpload extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['video_uid'] = DataDefinition::create('string')
      ->setLabel(t('Video UID'));
    $properties['destination_uid'] = DataDefinition::create('string')
      ->setLabel(t('Destination UID'));
    $properties['studio_uid'] = DataDefinition::create('string')
      ->setLabel(t('Studio UID'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return ($this->video_uid === NULL || $this->video_uid === '') && ($this->destination_uid === NULL || $this->destination_uid === '') && ($this->studio_uid === NULL || $this->studio_uid === '');
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
  }

  /**
   * {@inheritdoc}
   */
  public function postSave($update) {
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'video_uid';
  }
}

