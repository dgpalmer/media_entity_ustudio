<?php

namespace Drupal\media_entity_ustudio;

/**
 * Defines a wrapper around the uStudio oEmbed call.
 */
interface uStudioEmbedFetcherInterface {

  /**
   * Retrieves a uStudio post by its shortcode.
   *
   * @param int $destination
   *   The uStudio destination id.
   * @param int $video
   *   The uStudio video id.
   *
   * @return array
   *   The uStudio oEmbed information.
   */
  public function fetchUStudioEmbed($destination, $video);


  public function fetchUStudioConfig($destination, $video);
}
