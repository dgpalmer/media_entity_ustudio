<?php

namespace Drupal\media_entity_ustudio\Plugin\Validation\Constraint;

use Drupal\media_entity_ustudio\Plugin\media\source\uStudio;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the uStudioEmbedCode constraint.
 */
class uStudioEmbedCodeConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    $data = '';
    if (is_string($value)) {
      $data = $value;
    }
    elseif ($value instanceof FieldItemInterface) {
      $class = get_class($value);
      $property = $class::mainPropertyName();
      if ($property) {
        $data = $value->{$property};
      }
    }

    if ($data) {
      $matches = [];
      foreach (uStudio::$validationRegexp as $pattern => $key) {
        if (preg_match($pattern, $value, $item_matches)) {
          $matches[] = $item_matches;
        }
      }

      if (empty($matches)) {
        $this->context->addViolation($constraint->message);
      }
    }
  }

}
