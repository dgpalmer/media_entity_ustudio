<?php

namespace Drupal\media_entity_ustudio;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Utility\Error;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Fetches uStudio post via oembed.
 *
 * Fetches (and caches) uStudio data from uStudio's API
 */
class uStudioEmbedFetcher implements uStudioEmbedFetcherInterface {

  const USTUDIO_URL = 'http://app.ustudio.com';

  const USTUDIO_API = 'http://app.ustudio.com/api/v2';

  /**
   * The optional cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Guzzle client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * uStudioEmbedFetcher constructor.
   *
   * @param \GuzzleHttp\Client $client
   *   A HTTP Client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   A logger factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface|null $cache
   *   (optional) A cache bin for storing fetched uStudio posts.
   */
  public function __construct(Client $client, LoggerChannelFactoryInterface $loggerFactory, CacheBackendInterface $cache = NULL) {
    $this->httpClient = $client;
    $this->loggerFactory = $loggerFactory;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchUStudioEmbed($destination, $video) {

    $options = [
      'url' => self::USTUDIO_URL . '/embed/' . $destination. '/' . $video,
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
      $this->loggerFactory->get('media_entity_ustudio')->error("Could not retrieve uStudio embed $destination/$video.", Error::decodeException($e));
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

  public function fetchUStudioConfig($destination, $video) {
    dpm('fetchUStudioConfig');
    try {
      $response = $this->httpClient->request(
        'GET',
        self::USTUDIO_URL . '/embed/' . $destination. '/' . $video . '/config.json',
        ['timeout' => 5]
      );
      if ($response->getStatusCode() === 200) {
        $data = Json::decode($response->getBody()->getContents());
      }
    }
    catch (RequestException $e) {
      $this->loggerFactory->get('media_entity_ustudio')->error('Could not retrieve config.json');
    }

    if (isset($data)) {
      return $data;
    }
    return FALSE;

  }

}

