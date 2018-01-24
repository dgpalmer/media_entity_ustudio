<?php

namespace Drupal\media_entity_ustudio\uStudio;

/**
 * uStudio Studios
 */
interface StudiosInterface {

  /**
   * Perform GET Request to retrieve list of Studios
   *
   * @return array
   *  List of uStudio Studios available for this access token.
   */
  public function retrieveStudios();
}
