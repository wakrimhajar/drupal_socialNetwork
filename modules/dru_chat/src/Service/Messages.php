<?php

namespace Drupal\dru_chat\Service;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\InvalidQueryException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dru_chat\Entity\Message;
use Drupal\dru_chat\Event\DruChatEvent;
use Pusher\Pusher;

/**
 * Class for dru_chat.messages service.
 */
class Messages {

  /**
   * The EntityTypeManagerInterface class.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The ConfigFactoryInterface service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Setter for EntityTypeManagerInterface class.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The EntityTypeManagerInterface class.
   */
  public function setEntity(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Setter for the ConfigFactoryInterface service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The ConfigFactoryInterface service.
   */
  public function setConfig(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Returns an array of messages.
   *
   * @param string $user_id
   *   User id.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of message entities, relating to
   *   current user and the user param passed.
   */
  public function getMessages($user_id) {

    /** @var \Drupal\Core\Session\AccountProxy $current_user */
    $current_user = \Drupal::currentUser();

    // @todo for guests.
    $users = [$current_user->id(), $user_id];

    // Update unread.
    $update_query = \Drupal::database()->update('dru_chat_messages');
    $update_query->fields(['is_read' => TRUE])
      ->condition('from', $user_id)
      ->condition('to', $current_user->id())
      ->execute();

    // @todo optimize in one query if possible.
    // return messages which are from current_user->id()
    // and to = $user_id, OR from = $user_id and to = $current_user->id()
    try {
      $entity = $this->entityTypeManager->getStorage('dru_chat_messages');
      $query = $entity->getQuery();
      $data = $query
        ->condition('from', $users, 'IN')
        ->condition('to', $users, 'IN')
        ->sort('created', 'DESC')
        ->pager(30)
        ->execute();
      return $entity->loadMultiple(array_reverse($data));
    }
    catch (InvalidPluginDefinitionException $ex) {
      \Drupal::messenger()->addError($ex->getMessage());
      return NULL;
    }
  }

  /**
   * Returns a pusher instance we can reuse.
   *
   * @return \Pusher\Pusher
   * @throws \Pusher\PusherException
   */
  public function pusherInstance() {
    $config = $this->configFactory->getEditable('dru_chat.settings');
    $cluster = $config->get('cluster');
    $app_id = $config->get('app_id');
    $secret = $config->get('secret');
    $auth_key = $config->get('auth_key');

    $options = [
      'cluster' => $cluster,
      //'useTLS' => TRUE,
    ];
    $pusher = new Pusher(
      $auth_key,
      $secret,
      $app_id,
      $options
    );

    return $pusher;
  }

  /**
   * Saves and sends a message between users.
   *
   * @param string $to
   *   The user id to send to.
   * @param string $message
   *   The message string to send.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Pusher\ApiErrorException
   * @throws \Pusher\PusherException
   */
  public function createMessage($to, $message) {

    /** @var \Drupal\Core\Session\AccountProxy $current_user */
    $current_user = \Drupal::currentUser();
    $from = $current_user->id();

    // @todo for guest users!!
    if (!$current_user->isAuthenticated()) {
      return NULL;
    }

    $entity = $this->entityTypeManager->getStorage('dru_chat_messages');

    $message_obj = new Message(
      $from,
      $to,
      $message,
      FALSE
    );

    // Message event.
    $event = new DruChatEvent($message_obj);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch(DruChatEvent::NEW_MESSAGE_EVENT, $event);
    $event_item = $event->getMessageItem();

    // @todo track sessions for guests and start there.
    // If is is_read is true or set.
    if ($event_item->from && $event_item->to && $event_item->is_read == FALSE) {
      // Save item.
      $values = [
        'from' => $event_item->from,
        'to' => $event_item->to,
        'message' => htmlentities($event_item->message),
        'is_read' => FALSE,
      ];
      $entity->create($values)->save();

      $data = [
        'from' => $event_item->from,
        'to' => $event_item->to,

      ];

      $pusher = $this->pusherInstance();
      $pusher->trigger('my-channel', 'dru-chat-event', $data);

      return $entity;
    }
    return NULL;
  }

  /**
   * Returns the total number of unread.
   *
   * @param array $users
   *   An array of users on the listing block.
   * @param string $current_user
   *   The current user id.
   *
   * @return array|null
   *   The total unread for a user.
   */
  public function countUnread($users, $current_user) {
    try {
      $query = \Drupal::entityQueryAggregate('dru_chat_messages');
      return $query
        ->condition('from', $users, 'IN')
        ->condition('to', $current_user)
        ->condition('is_read', FALSE)
        ->groupBy('from')
        ->aggregate('message', 'COUNT')
        ->execute();
    }
    catch (InvalidQueryException $ex) {
      \Drupal::messenger()->addError($ex->getMessage());
      return NULL;
    }

  }

  public function sendMessage() {
    return intval('500');
  }

}
