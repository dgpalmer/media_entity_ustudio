<?php

namespace Drupal\media_entity_ustudio\Controller;

use Drupal;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Entity\EntityConstraintViolationListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\media_entity_ustudio\uStudio\uStudioFetcher;
use Drupal\media_entity_ustudio\uStudio\uStudioFetcherInterface;
use Drupal\file\Entity\File;


/**
 * Class uStudioAjaxController.
 */
class uStudioAjaxController extends ControllerBase {

  const ERROR_UNKNOWN = 'unknown';
  const ERROR_ENTITY_VIOLATION = 'entity_violation';
  const ERROR_UNDO = 'undo';

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var Drupal\Core\Routing\RouteMatchInterface $routeMatch */
  protected $routeMatch;
  /** @var  Drupal\media_entity_ustudio\uStudio/uStudioFetcher */
  protected $fetcher;

  function __construct(EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $routeMatch, uStudioFetcherInterface $uStudioFetcher){
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $routeMatch;
    $this->fetcher = $uStudioFetcher;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('media_entity_ustudio.fetcher')
    );
  }

  /**
   * AJAX controller endpoint to create a video in UStudio
   */
  public function createVideo() {
    $selector = Drupal::request()->request->get('selector');
    $ajax = new AjaxResponse();


    $request = Drupal::request()->request;
    $studio = $request->get('studio');
    $attributes = [
      'title' => $request->get('title'),
      'description' => $request->get('description'),
      'keywords' => $request->get('tags'),
      'category' => $request->get('category')
    ];

    try {
      $video = $this->fetcher->createVideo($studio, $attributes);
      $ajax->setData(['video' => $video]);
    } catch(\Exception $e) {
      $this->ajaxError($ajax, $selector, 'An unknown error has occurred.');
      Drupal::logger('media_entity_ustudio')->error($e->getMessage());
    } finally {
      return $ajax;
    }
  }

  /**
   * AJAX Controller endpoint to upload a video file to uStudio
   */
  public function uploadVideo() {
    $ajax = new AjaxResponse();
    $request = Drupal::request()->request;
    $selector = $request->get('selector');
    $upload_url = $request->get('upload_url');
    $file = File::load($request->get('fid'));

    try {
      $upload = $this->fetcher->uploadVideo($upload_url, $file);

      $ajax->setData(['upload' => $upload]);
    } catch(\Exception $e) {
      $this->ajaxError($ajax, $selector, 'An unknown error has occurred.');
      Drupal::logger('media_entity_ustudio')->error($e->getMessage());
    } finally {
      return $ajax;
    }
  }

  /**
   * AJAX Controller endpoint to upload a video file to uStudio
   */
  public function uploadStatus() {
    $ajax = new AjaxResponse();
    $request = Drupal::request()->request;
    $selector = $request->get('selector');
    $signed_upload_url = $request->get('signed_upload_url');
    try {
      $progress = $this->fetcher->uploadStatus($signed_upload_url);

      $ajax->setData(['progress' => $progress]);
    } catch(\Exception $e) {
      $this->ajaxError($ajax, $selector, 'An unknown error has occurred.');
      Drupal::logger('media_entity_ustudio')->error($e->getMessage());
    } finally {
      return $ajax;
    }
  }


  public function publishVideo() {
    $ajax = new AjaxResponse();
    $request = Drupal::request()->request;
    $selector = $request->get('selector');
    $studio = $request->get('studio');
    $destination = $request->get('destination');
    $video = $request->get('video');

    // Call the uStudio Publish Video
    try {
      $video = $this->fetcher->publishVideo($studio, $destination, $video);
      $ajax->setData(['video' => $video]);
    } catch(\Exception $e) {
      $this->ajaxError($ajax, $selector, 'An unknown error has occurred.');
      Drupal::logger('media_entity_ustudio')->error($e->getMessage());
    } finally {
      return $ajax;
    }
  }


  protected function error($message, $code = self::ERROR_UNKNOWN) {
    return [
      'error' => [ 'code' => $code , 'message' => $message ]
    ];
  }

  /**
   * Set an ajax error
   */
  protected function ajaxError(AjaxResponse $ajax, $selector, $message, $code = self::ERROR_UNKNOWN) {
    $ajax->addCommand(new InvokeCommand($selector, 'addClass', ['error']));
    $ajax->addCommand(new InvokeCommand($selector, 'trigger', ['error', [
      'code' => $code,
      'message' => $this->t($message ?: 'An unknown error has occurred.'),
    ]]));
    $ajax->setStatusCode(400);
  }

}
