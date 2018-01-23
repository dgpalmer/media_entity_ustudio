<?php

namespace Drupal\media_entity_ustudio\Plugin\Normalizer;

use Drupal;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\asf_common\Plugin\ASF\Normalizer\ASFNormalizerInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * A plugin to add fields from the media entity to the media reference field.
 *
 * @uStudioMediaReferenceNormalizer(
 *   id = "media_entity_ustudio_media_normalizer",
 *   label = @Translation("uStudio MediaReference Normalizer"),
 *   supported = { "Drupal\Core\Entity\ContentEntityInterface" },
 * )
 */
class uStudioMediaReferenceNormalizer implements ASFNormalizerInterface {

  /** @var \Symfony\Component\Serializer\Serializer */
  protected $serializer;

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  function __construct($configuration) {
    $this->serializer = $configuration['serializer'];
    $this->entityTypeManager = Drupal::service('entity_type.manager');
  }

  /**
   * {@inheritdoc}
   */
  public function normalize(&$attributes, $object, $format, array $context = []) {
    if (!($object instanceof ContentEntityInterface)) { return; }

    // ## Add media entity fields to the media reference field.
    $fields = array_keys($object->getFields(FALSE));
    foreach ($fields as $key => $field_name) {
      $field = $object->get($field_name);
      if ($field instanceof EntityReferenceFieldItemList) {
        $itemDefinition = $field->getItemDefinition();
        $target_type = $itemDefinition->getSetting('target_type');
        if ($target_type !== 'media') { continue; }
        foreach ($field->getValue() as $delta => $item) {
          if (isset($item['target_id'])) {
            $mediaEntity = $this->entityTypeManager->getStorage($target_type)->load($item['target_id']);
            $media = $this->serializer->normalize($mediaEntity->getFields(FALSE), $format, $context);
            $attributes[$field_name][$delta]['media'] = $media;
          }
        }
      }
    }
  }
}