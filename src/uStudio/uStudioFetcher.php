<?php

namespace Drupal\media_entity_ustudio\uStudio;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Utility\Error;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * uStudio API
 */
class uStudioFetcher implements uStudioFetcherInterface {

  const USTUDIO_URL = 'https://app.ustudio.com';

  const USTUDIO_API = 'https://app.ustudio.com/api/v2';

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
   * uStudioAPI constructor.
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
  public function retrieveStudios($access_token) {
    dpm('retrieveStudios Fetcher');
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

  /**
   * {@inheritdoc}
   */
  public function retrieveCollections($access_token, $studio) {
    dpm('retrieveCollections fetcher');
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
      $test = self::USTUDIO_API . '/studios/' . $studio . '/collections?' . $queryParameter;

      $response = $this->httpClient->request(
        'GET',
        self::USTUDIO_API . '/studios/' . $studio . '/collections?' . $queryParameter,
        ['timeout' => 5]
      );

      $status = $response->getStatusCode();
      if ($response->getStatusCode() === 200) {
        $data = Json::decode($response->getBody()->getContents());
      }
    }
    catch (RequestException $e) {
      $this->loggerFactory->get('media_entity_ustudio')->error("Could not retrieve collections.", Error::decodeException($e));
    }


    // If we got data from uStudio oEmbed request, return data.
    if (isset($data)) {

      // If we have a cache, store the response for future use.
      /*if ($this->cache) {
        // uStudio posts don't change often, so the response should expire
        // from the cache on its own in 90 days.
        $this->cache->set($cacheKey, $data, time() + (86400 * 90));
      }*/
      $collections = [];
      foreach($data['collections'] as $collection) {
        $uid = $collection['uid'];
        $collections[$uid] = $collection['name'];
      }

      return $collections;
    }
    return FALSE;
  }


  /**
   * {@inheritdoc}
   */
  public function retrieveDestinations($access_token, $studio) {
    dpm('retrieveDestinations fetcher');
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

      $response = $this->httpClient->request(
        'GET',
        self::USTUDIO_API . '/studios/' . $studio . '/destinations?' . $queryParameter,
        ['timeout' => 5]
      );

      $status = $response->getStatusCode();
      if ($response->getStatusCode() === 200) {
        $data = Json::decode($response->getBody()->getContents());
      }
    }
    catch (RequestException $e) {
      $this->loggerFactory->get('media_entity_ustudio')->error("Could not retrieve destinations.", Error::decodeException($e));
    }

    // If we got data from uStudio oEmbed request, return data.
    if (isset($data)) {

      // If we have a cache, store the response for future use.
      /*if ($this->cache) {
        // uStudio posts don't change often, so the response should expire
        // from the cache on its own in 90 days.
        $this->cache->set($cacheKey, $data, time() + (86400 * 90));
      }*/
      $destinations = [];
      foreach($data['destinations'] as $destination) {
        $uid = $destination['uid'];
        $destinations[$uid] = $destination['name'];
      }

      return $destinations;
    }
    return FALSE;
  }

}
