<?php

namespace Drupal\media_entity_ustudio\uStudio;

/**
 * uStudio Destinations
 */
interface DestinationsInterface {

  /**
   * Perform GET Request to retrieve list of Destinations for the Studio
   *
   * @param string $studioID
   *    The uSudio studio UID
   *
   * @return array
   *  List of uStudio Destinations available for this studio.
   */
  public function retrieveDestinations($studioID);
}
