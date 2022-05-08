<?php

namespace Drupal\dru_chat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountInterface;
use Drupal\dru_chat\Event\DruChatPresenceEvent;
use Drupal\dru_chat\Service\Messages;
use Pusher\PusherException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provides route responses for message pages and or requests.
 */
class MessagesController extends ControllerBase {

  /**
   * The dru_chat.messages service.
   *
   * @var \Drupal\dru_chat\Service\Messages
   */
  private $messages;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  private $renderer;

  /**
   * MessagesController constructor.
   *
   * @param \Drupal\dru_chat\Service\Messages $messages
   *   The dru_chat.messages service.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer service.
   */
  public function __construct(Messages $messages, Renderer $renderer) {
    $this->messages = $messages;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $messages = $container->get('dru_chat.messages');
    $renderer = $container->get('renderer');
    return new static($messages, $renderer);
  }

  /**
   * Builds a messages view related to the two users.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   User Account entity.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Symfony Request object from ajax request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function getMessages(AccountInterface $user, Request $request) {
    if (!$user) {
      throw new BadRequestHttpException('Missing items');
    }
    // Check request types get/post/ajax.
    $user_id = $user->id();
    if ($request->getMethod() === 'GET') {
      $messages = $this->messages->getMessages($user_id);
      $build = [
        '#type' => 'markup',
        '#cache' => ['max-age' => 0],
        '#theme' => 'dru_chat_messages',
        '#data' => [
          'messages' => $messages,
          'user_id' => $user->getDisplayName(),
        ],
      ];

      $html = $this->renderer->renderRoot($build);
      $response = new Response();
      $response->setContent($html);

      return $response;

    }
  }

  /**
   * Returns a json response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The symfony request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response, in json format.
   */
  public function newMessage(Request $request) {
    // Send the message from the request,
    // from current session user.
    $to = $request->get('receiver_id');
    $message = $request->get('message');

    $created = $this->messages->createMessage($to, $message);

    return new JsonResponse($created);

  }

  /**
   * Registers a user to pusher presence api.
   *   Easier to track online users this way, than implementing
   *   a custom session handler
   * @throws \Pusher\PusherException
   */
  public function presence(Request $request) {
    $channelName = $request->request->get('channel_name', NULL);
    $socketId =  $request->request->get('socket_id', NULL);

    if (!$channelName || !$socketId) {
      throw new BadRequestHttpException('Bad request object');
    }

    /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
    $session = $request->getSession();
    if (!$session->get('dru_chat_user')) {
      $user_id = $session->getId();
      $session->set('dru_chat_user', $user_id);
      try {
        $pusher = \Drupal::service('dru_chat.messages');
        // @todo doesn't work!!! because of issue @ https://github.com/pusher/pusher-js/issues/485
        /** @var \Pusher\Pusher $pusherInstance */
        $pusherInstance = $pusher->pusherInstance();

        $presenceItem = [
          'channelName' => $channelName,
          'socketId' => $socketId,
          'sessionId' => $user_id,
        ];
        $event = new DruChatPresenceEvent($presenceItem);
        $event_dispatcher = \Drupal::service('event_dispatcher');
        // Fire a new user presence event.
        $event_dispatcher->dispatch(DruChatPresenceEvent::EVENT_NAME, $event);
        $eventPresenceItem = $event->getPresenceItem();
        $pusherInstance->presence_auth(
          $eventPresenceItem['channelName'],
          $eventPresenceItem['socketId'],
          $eventPresenceItem['sessionId']
        );
      }
      catch (PusherException $ex) {
        throw new BadRequestHttpException('Pusher error: ' . $ex->getMessage());
      }
    }
    return new JsonResponse();

  }

}
