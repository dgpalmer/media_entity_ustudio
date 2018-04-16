<?php

namespace Drupal\media_entity_ustudio\uStudio;

/**
 * uStudio Videos
 */
interface VideosInterface {

  /**
   * Perform GET Request to retrieve list of Videos
   *
   * @return array
   *  List of uStudio videos available
   */
  public function retrieveVideos();
}
