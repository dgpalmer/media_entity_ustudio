<?php

namespace Drupal\media_entity_ustudio\Plugin\Validation\Constraint;

use Drupal\media_entity\EmbedCodeValueTrait;
use Drupal\media_entity_ustudio\Plugin\MediaEntity\Type\uStudio;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the uStudioEmbedCode constraint.
 */
class uStudioEmbedCodeConstraintValidator extends ConstraintValidator {

  use EmbedCodeValueTrait;

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    $value = $this->getEmbedCode($value);
    if (!isset($value)) {
      return;
    }

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
