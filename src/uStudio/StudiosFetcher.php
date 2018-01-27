<?php

namespace Drupal\media_entity_ustudio\uStudio;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Utility\Error;

/**
 * uStudio Studios
 */
class StudiosFetcher extends uStudioAPI implements StudiosFetcherInterface {

  public function __construct(Client $client, LoggerChannelFactoryInterface $loggerFactory, $cache = NULL)
  {
    parent::__construct($client, $loggerFactory, $cache);
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveStudios($access_token) {
    $options = [
      'token' => $access_token
    ];

    // uStudio videos don't change much, so pull it out of the cache (if we have one)
    // if this one has already been fetched.
    /*$cacheKey = md5(serialize($options));
    if ($this->cache && $cached_ustudio_post = $this->cache->get($cacheKey)) {
      return $cached_ustudio_post->data;
    }*/

    $queryParameter = UrlHelper::buildQuery($options);

    try {
      $test = self::USTUDIO_API . '/studios?' . $queryParameter;

      $response = $this->httpClient->request(
        'GET',
        self::USTUDIO_API . '/studios?' . $queryParameter,
        ['timeout' => 5]
      );

      $status = $response->getStatusCode();
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
      /*if ($this->cache) {
        // uStudio posts don't change often, so the response should expire
        // from the cache on its own in 90 days.
        $this->cache->set($cacheKey, $data, time() + (86400 * 90));
      }*/
      $studios = [];
      foreach($data['studios'] as $studio) {
        $uid = $studio['uid'];
        $studios[$uid] = $studio['name'];
      }

      return $studios;
    }
    return FALSE;
  }

}
