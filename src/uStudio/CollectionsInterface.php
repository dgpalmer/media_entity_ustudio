<?php

namespace Drupal\media_entity_ustudio\uStudio;

/**
 * Defines wrapper around uStudio Collections Calls
 */
interface CollectionsInterace {

  /**
   * Perform GET Request to retrieve list of Collections for the Studio
   *
   * @param string $studioID
   *    The uSudio studio UID
   *
   * @return array
   *  List of uStudio Collections available for this studio
   */
  public function retrieveCollections($studioID);

}
