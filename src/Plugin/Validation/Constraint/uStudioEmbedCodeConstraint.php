<?php

namespace Drupal\media_entity_ustudio\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Check if a value is a valid uStudio embed code/URL.
 *
 * @constraint(
 *   id = "uStudioEmbedCode",
 *   label = @Translation("uStudio embed code", context = "Validation"),
 *   type = { "link", "string", "string_long" }
 * )
 */
class uStudioEmbedCodeConstraint extends Constraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'Not valid uStudio URL/Embed code.';

}
