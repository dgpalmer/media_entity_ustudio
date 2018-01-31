<?php

namespace Drupal\media_entity_ustudio\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'path' widget.
 *
 * @FieldWidget(
 *   id = "path",
 *   label = @Translation("URL alias"),
 *   field_types = {
 *     "path"
 *   }
 * )
 */
class uStudioVideoUploadWidget extends WidgetBase {

}
