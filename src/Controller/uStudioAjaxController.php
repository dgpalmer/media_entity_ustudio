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
   * AJAX controller endpoint to perform a user_reaction.
   */
  public function createVideo() {
    dpm('create video ajax request');
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
  public function uploadVideo() {
    dpm('uploadVideoAjaxRequest');
    $ajax = new AjaxResponse();
    return $ajax;
  }
  public function publishVideo($studio, $video) {
    $ajax = new AjaxResponse();
    return $ajax;
  }
/*    try {
      $attributes = $content['attributes'];
      $data = $this->fetcher->createVideo($studio, $attributes);
      if (isset($data['error'])) {
        $this->ajaxError($ajax, $selector, $data['error']['message'], $data['error']['code']);
      } else if (!empty($data['violations'])) {
        /** @var EntityConstraintViolationListInterface $violations */
/*        foreach ($data['violations'] as $violation) {
          // Return the first violation as an error.
          /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
/*          $this->ajaxError($ajax, $selector, $violation->getMessage(), self::ERROR_ENTITY_VIOLATION);
          break;
        }
      } else {
        $ajax->addCommand(new InvokeCommand($selector, 'addClass', ['success']));
        $ajax->addCommand(new InvokeCommand($selector, 'trigger', ['success', $data]));
      }
    } catch(\Exception $e) {
      $this->ajaxError($ajax, $selector, 'An unknown error has occurred.');
      Drupal::logger('asf_reactions')->error($e->getMessage());
    } finally {
      return $ajax;
    }
  }*/

  /**
   * Create a user_reaction and return status info.
   *
   * @param $reaction
   * @param $entity_type
   * @param $entity_id
   * @return array
   * @throws \Exception
   */
  public function performReaction($reaction, $entity_type, $entity_id) {
    /** @var \Drupal\asf_reactions\Entity\UserReactionTypeInterface $userReactionType */
    $userReactionType = $this->entityTypeManager->getStorage('user_reaction_type')->load($reaction);
    $userReactionType->context();
    /** @var UserReaction $userReaction */
    $userReaction = UserReaction::create([
      'reaction' => $reaction,
      'context' => $userReactionType->context(['entity_type' => $entity_type, 'entity_id' => $entity_id]),
      'uid' => $this->currentUser()->id(),
      'value' => $this->getReactionValue($userReactionType),
      // target entity
      'entity_type' => $entity_type,
      'entity_id' => $entity_id,
    ]);

    // Check the entity for any constraint violations;
    /** @var EntityConstraintViolationListInterface $violations */
    $violations = $userReaction->validate();
    if ($violations->count() > 0) {
      $data['violations'] = $violations;
      return $data;
    }
    // and then save the entity.
    $userReaction->save();

    // ## Build the status response data.
    $data = [
      'entity_type' => $entity_type,
      'entity_id' => $entity_id,
      'reaction' => $reaction,
      'value' => $userReaction->value(),
    ];
    $message = $userReactionType->message();
    if (!empty($message)) {
      $data['message'] = $this->t($message);
    }

    // If the user cannot perform the reaction again return the cancel link.
    $HAS_REACHED_LIMIT = $userReaction->hasReachedLimit();
    $data['limit'] = $HAS_REACHED_LIMIT;
    $route_name = $HAS_REACHED_LIMIT ? UserReaction::ROUTE_CANCEL : UserReaction::ROUTE_PERFORM;
    $route_name_api = $HAS_REACHED_LIMIT ? UserReaction::ROUTE_API_CANCEL : UserReaction::ROUTE_API_PERFORM;
    if ($route_name !== $this->routeMatch->getRouteName()) {
      $data['href'] = Url::fromRoute($route_name, [
        'reaction' => $reaction, 'entity_type' => $entity_type, 'entity_id' => $entity_id,
      ])->toString();
      $data['api'] = Url::fromRoute($route_name_api, [
        'reaction' => $reaction, 'entity_type' => $entity_type, 'entity_id' => $entity_id,
      ])->toString();
      $data['label'] = $HAS_REACHED_LIMIT ? $userReactionType->labelUndo() : $userReactionType->labelDisplay();
    }

    // ## Find reactions deleted by this reaction
    $conflicts = $userReaction->getConflictingReactions();
    if (!empty($conflicts)) {
      // and return those conflicts.
      $data['conflicts'] = [];
      foreach ($conflicts as $conflict) {
        /** @var UserReaction $conflict */
        $conflict_reaction = $conflict->type();
        if (isset($data['conflicts'][$conflict_reaction])) {
          continue;
        }
        /** @var UserReactionTypeInterface $conflictType */
        $conflictType = $this->entityTypeManager->getStorage('user_reaction_type')->load($conflict_reaction);
        $conflict_entity_type = $conflict->targetEntityType();
        $conflict_entity_id = $conflict->targetEntityId();
        $conflict_selector = "a.user-reaction--{$conflict_entity_type}--{$conflict_entity_id}--{$conflict_reaction}";
        $conflict_url = Url::fromRoute(UserReaction::ROUTE_PERFORM, [
          'reaction' => $conflict_reaction, 'entity_type' => $conflict_entity_type, 'entity_id' => $conflict_entity_id,
        ])->toString();
        $conflict_api_url = Url::fromRoute(UserReaction::ROUTE_API_PERFORM, [
          'reaction' => $conflict_reaction, 'entity_type' => $conflict_entity_type, 'entity_id' => $conflict_entity_id,
        ])->toString();
        $data['conflicts'][$conflict_reaction] = [
          'selector' => $conflict_selector,
          'href' => $conflict_url,
          'api' => $conflict_api_url,
          'label' => $conflictType->labelDisplay(),
          'entity_type' => $conflict_entity_type,
          'entity_id' => $conflict_entity_id,
          'reaction' => $conflict_reaction,
          'value' => $conflict->value(),
        ];
      }
    }

    return $data;
  }

  /**
   * AJAX controller endpoint to cancel a user_reaction.
   */
  public function ajaxCancelReaction($reaction, $entity_type, $entity_id) {
    $selector = Drupal::request()->request->get('selector');
    $ajax = new AjaxResponse();
    try {
      $data = $this->cancelReaction($reaction, $entity_type, $entity_id);
      if (isset($data['error'])) {
        $this->ajaxError($ajax, $selector, $data['error']['message'], $data['error']['code']);
      } else {
        $ajax->addCommand(new InvokeCommand($selector, 'addClass', ['success']));
        $ajax->addCommand(new InvokeCommand($selector, 'trigger', ['success', $data]));
      }
    } catch (\Exception $e) {
      $this->ajaxError($ajax, $selector, 'An unknown error has occurred.');
      Drupal::logger('asf_reactions')->error($e->getMessage());
    } finally {
      return $ajax;
    }
  }

  /**
   * REST API controller endpoint to cancel a user_reaction.
   */
  public function restCancelReaction($reaction, $entity_type, $entity_id) {
    $response = new JsonResponse();
    try {
      $data = $this->cancelReaction($reaction, $entity_type, $entity_id);
      if (isset($data['error'])) {
        $response->setData($data['error'])->setStatusCode(400);
      } else {
        $response->setData($data);
      }
    } catch(\Exception $e) {
      $response->setData($this->error($e->getMessage()))->setStatusCode(400);
      Drupal::logger('asf_reactions')->error($e->getMessage());
    } finally {
      return $response;
    }
  }

  /**
   * Cancel / delete a user_reaction and return status info.
   *
   * @param $reaction
   * @param $entity_type
   * @param $entity_id
   * @return array
   * @throws \Exception
   */
  public function cancelReaction($reaction, $entity_type, $entity_id) {
    // TODO optional parameter delete all?
    /** @var \Drupal\asf_reactions\Entity\UserReactionTypeInterface $userReactionType */
    $userReactionType = $this->entityTypeManager->getStorage('user_reaction_type')->load($reaction);

    /** @var Drupal\asf_reactions\Entity\Storage\UserReactionStorage $userReactionStorage */
    $userReactionStorage = $this->entityTypeManager->getStorage('user_reaction');
    // ## Find the user reactions on this entity.
    /** @var UserReaction[] $userReactions */
    $context = $this->userReactionService->getContext($userReactionType, ['entity_type' => $entity_type, 'entity_id' => $entity_id]);
    $user_reactions = $userReactionStorage->findUserReactions($this->currentUser(), $entity_type, $entity_id, $reaction, $context);
    if (isset($user_reactions[$reaction])) {
      // Remove all of the user's reactions of the specified type.
      // TODO support removing a single reaction at a time? As a user_reaction_type setting?
      foreach($user_reactions[$reaction] as $user_reaction) {
        $userReaction = $userReactionStorage->load($user_reaction->id);
        $userReaction->delete();
      }
      $message = $userReactionType->messageUndo();
      // Build the status info.
      $data = [
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'reaction' => $reaction,
        'value' => -1 * (int) $userReaction->value(),
      ];
      if (!empty($message)) {
        $data['message'] = $this->t($message);
      }
      $data['label'] = $userReactionType->labelDisplay();
      // Return urls to perform the reaction again.
      $data['href'] = Url::fromRoute(UserReaction::ROUTE_PERFORM, [
        'reaction' => $reaction, 'entity_type' => $entity_type, 'entity_id' => $entity_id,
      ])->toString();
      $data['api'] = Url::fromRoute(UserReaction::ROUTE_API_PERFORM, [
        'reaction' => $reaction, 'entity_type' => $entity_type, 'entity_id' => $entity_id,
      ])->toString();
      return $data;
    } else {
      return $this->error($userReactionType->messageCannotUndo(), self::ERROR_UNDO);
    }
  }

  // # Helper functions.

  protected function getReactionValue(UserReactionTypeInterface $userReactionType) {
    $value = $userReactionType->value();
    $allowed_values = $userReactionType->allowedValues();
    $submittedValue = NULL;
    if (!empty($allowed_values) && $submittedValue = Drupal::request()->attributes->get('value') && in_array($allowed_values, $submittedValue)) {
      // TODO support custom value from post )
      // $value = $submittedValue;
    }
    return $value;
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

  /**
   * Check if the user has access to perform this reaction.
   * @param AccountInterface $account
   * @return AccessResult|Drupal\Core\Access\AccessResultForbidden
   */
  public function access(AccountInterface $account) {
    // Find the reaction type & get it's permission.
    $reaction = Drupal::routeMatch()->getParameter('reaction');
    /** @var \Drupal\asf_reactions\Entity\UserReactionTypeInterface $userReactionType */
    $userReactionType = $this->entityTypeManager->getStorage('user_reaction_type')->load($reaction);
    if (!isset($userReactionType) || $account->isAnonymous()) {
      return AccessResult::forbidden();
    }
    return AccessResult::allowedIfHasPermission($account, $userReactionType->permission());
  }

}
