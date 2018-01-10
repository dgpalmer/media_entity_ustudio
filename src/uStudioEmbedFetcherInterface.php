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
   * @param bool $hidecaption
   *   Indicates if the caption should be hidden in the html.
   * @param bool $maxWidth
   *   Max width of the instagram widget.
   *
   * @return array
   *   The instagram oEmbed information.
   */
  public function fetchUStudioEmbed($destination, $video, $hidecaption = FALSE, $maxWidth = NULL);

}
