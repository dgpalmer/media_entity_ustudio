<?php

namespace Drupal\media_entity_ustudio\uStudio;

/**
 * uStudio Studios
 */
interface StudiosFetcherInterface {

  /**
   * Perform GET Request to retrieve list of Studios
   *
   * @param string $access_token
   *
   * @return array
   *  List of uStudio Studios available for this access token.
   */
  public function retrieveStudios($access_token);
}
