<?php

namespace Drupal\media_entity_ustudio\uStudio;

use Drupal\file\FileInterface;

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
   * Perform POST Request to create video
   *
   * @param string $access_token
   *
   * @param string studio
   *
   * @param string $attributes
   *
   * @return array
   *  Representation of the new Video resource
   */
  public function createVideo($access_token, $studio, $attributes);

  /**
   * Perform POST Request to upload video
   *
   * @param string $access_token
   *
   * @param string $upload_url
   *
   * @param File @file
   *
   * @return array
   *  Representation of the uploaded video resource
   */
  public function uploadVideo($access_token, $upload_url, FileInterface $file);

  /**
   * Perform GET Request to retrieve list of Destinations
   *
   * @param string $access_token
   *
   * @param string $studio
   *
   * @param string $destination
   *
   * @param string $video
   *
   * @return array
   *  Representation of the new Video resource
   */
  public function publishVideo($access_token, $studio, $destination, $video);
}
