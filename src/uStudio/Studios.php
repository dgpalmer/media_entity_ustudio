<?php

namespace Drupal\media_entity_ustudio\uStudio;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Serialization\Json;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Utility\Error;

/**
 * uStudio Studios
 */
class Studios extends uStudioAPI implements StudiosInterface {

  /**
   * {@inheritdoc}
   */
  public function retrieveStudios() {
    $options = [
      'url' => self::USTUDIO_API . '/studios'
    ];


    // uStudio videos don't change much, so pull it out of the cache (if we have one)
    // if this one has already been fetched.
    $cacheKey = md5(serialize($options));
    if ($this->cache && $cached_ustudio_post = $this->cache->get($cacheKey)) {
      return $cached_ustudio_post->data;
    }

    $queryParameter = UrlHelper::buildQuery($options);

    try {
      $response = $this->httpClient->request(
        'GET',
        self::USTUDIO_API . '/oembed?' . $queryParameter,
        ['timeout' => 5]
      );
      if ($response->getStatusCode() === 200) {
        $data = Json::decode($response->getBody()->getContents());
      }
    }
    catch (RequestException $e) {
      $this->loggerFactory->get('media_entity_ustudio')->error("Could not retrieve studios.", Error::decodeException($e));
    }


    // If we got data from uStudio oEmbed request, return data.
    if (isset($data)) {

      // If we have a cache, store the response for future use.
      if ($this->cache) {
        // uStudio posts don't change often, so the response should expire
        // from the cache on its own in 90 days.
        $this->cache->set($cacheKey, $data, time() + (86400 * 90));
      }

      return $data;
    }
    return FALSE;
  }

}
