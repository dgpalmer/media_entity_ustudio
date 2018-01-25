<?php

namespace Drupal\media_entity_ustudio\uStudio;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;

/**
 * uStudio API
 */
abstract class uStudioAPI {

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

}
