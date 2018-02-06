<?php

namespace Drupal\media_entity_ustudio\uStudio;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Utility\Error;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\file\FileInterface;
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
   * Query Options
   */
  protected $options;

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

  /**
   * {@inheritdoc}
   */
  public function createVideo($access_token, $studio, $attributes) {
    dpm('createVideo');
    $options = [
      'token' => $access_token
    ];
    $queryParameter = UrlHelper::buildQuery($options);

    $video = json_encode($attributes);
    try {

      $response = $this->httpClient->request(
        'POST',
        self::USTUDIO_API . '/studios/' . $studio . '/videos?' . $queryParameter,
        ['timeout' => 5, 'body' => $video]
      );

      $status = $response->getStatusCode();
      if ($response->getStatusCode() === 201) {
        $data = Json::decode($response->getBody()->getContents());
        dpm($data);
        return $data;
      }
    }
    catch (RequestException $e) {
      dpm(Error::decodeException($e));
      $this->loggerFactory->get('media_entity_ustudio')->error("Could not post video.", Error::decodeException($e));
    }

  }
  /**
   * {@inheritdoc}
   */
  public function uploadVideo($access_token, $upload_url, FileInterface $file) {
    dpm('uploadVideo');
    $options = [
      'token' => $access_token,
      'X-Progress-ID' => 'upload_progress',
    ];
    $queryParameter = UrlHelper::buildQuery($options);

    $multipart_form = [
      [
        'name' => 'file',
        'contents' => fopen($file->getFileUri(), 'r'),
        'filename' => $file->getFilename()
      ]
    ];
    try {
      $response = $this->httpClient->request(
        'POST',
        $upload_url . '?' . $queryParameter,
        ['multipart' => $multipart_form]
      );

      $status = $response->getStatusCode();
      if ($response->getStatusCode() === 201) {
        $data = Json::decode($response->getBody()->getContents());
        return $data;
      }
    }
    catch (RequestException $e) {
      dpm(Error::decodeException($e));
      $this->loggerFactory->get('media_entity_ustudio')->error("Could not post video.", Error::decodeException($e));
    }
  }
  /**
   * {@inheritdoc}
   */
  public function uploadVideoProgress($access_token, $studio, $video) {
    dpm('uploadVideoProgress');
    $options = [
      'token' => $access_token,
      'X-Progress-ID' => 'upload_progress',
      'callback' => 'trackUpload',
    ];
    $queryParameter = UrlHelper::buildQuery($options);

    try {
      $response = $this->httpClient->request(
        'GET',
        self::USTUDIO_API . '/studios/' . $studio . '/videos/' . $video. '/asset/progress?' . $queryParameter
      );

      if ($response->getStatusCode() === 200) {
        $data = Json::decode($response->getBody()->getContents());
        return $data;
      }
    }
    catch (RequestException $e) {
      dpm(Error::decodeException($e));
      $this->loggerFactory->get('media_entity_ustudio')->error("Could not post video.", Error::decodeException($e));
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function publishVideo($access_token, $studio, $destination, $video) {
    $options = [
      'token' => $access_token
    ];
    $queryParameter = UrlHelper::buildQuery($options);

    $body = json_encode(['video_uid' => $video]);

    try {
      $response = $this->httpClient->request(
        'POST',
        self::USTUDIO_API . '/studios/' . $studio . '/destinations/' . $destination . '/videos?' . $queryParameter,
        ['body' => $body]
      );

      $status = $response->getStatusCode();
      dpm($status);
      if ($response->getStatusCode() === 201) {
        $data = Json::decode($response->getBody()->getContents());
      }
    }
    catch (RequestException $e) {
      dpm(Error::decodeException($e));
      $this->loggerFactory->get('media_entity_ustudio')->error("Could not post video.", Error::decodeException($e));
    }

    if (isset($data)) {
      return $data;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchVideoConfig($destination, $video) {
    dpm('fetchVideoConfig');
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

