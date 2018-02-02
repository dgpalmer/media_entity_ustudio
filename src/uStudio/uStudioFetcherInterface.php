<?php

namespace Drupal\media_entity_ustudio\uStudio;

/**
 * uStudio Studios
 */
interface uStudioFetcherInterface {

  /**
   * Perform GET Request to retrieve list of Studios
   *
   * @param string $access_token
   *
   * @return array
   *  List of uStudio Studios available for this access token.
   */
  public function retrieveStudios($access_token);

  /**
   * Perform GET Request to retrieve list of Collections
   *
   * @param string $access_token
   *
   * @param string $studio
   *
   * @return array
   *  List of uStudio Collections available for this studio
   */
  public function retrieveCollections($access_token, $studio);

  /**
   * Perform GET Request to retrieve list of Destinations
   *
   * @param string $access_token
   *
   * @param string $studio
   *
   * @return array
   *  List of uStudio Destinations available for this studio
   */
  public function retrieveDestinations($access_token, $studio);

  /**
   * Perform GET Request to retrieve list of Destinations
   *
   * @param string $access_token
   *
   * @param string $attributes
   *
   * @return array
   *  Representation of the new Video resource
   */
  public function createVideo($access_token, $studio, $attributes);
}
