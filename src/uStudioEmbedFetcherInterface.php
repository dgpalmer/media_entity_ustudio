<?php

namespace Drupal\media_entity_ustudio;

/**
 * Defines a wrapper around the uStudio oEmbed call.
 */
interface uStudioEmbedFetcherInterface {

  /**
   * Retrieves a uStudio post by its shortcode.
   *
   * @param int $shortcode
   *   The uStudio post shortcode.
   * @param bool $hidecaption
   *   Indicates if the caption should be hidden in the html.
   * @param bool $maxWidth
   *   Max width of the instagram widget.
   *
   * @return array
   *   The instagram oEmbed information.
   */
  public function fetchUStudioEmbed($shortcode, $hidecaption = FALSE, $maxWidth = NULL);

}
