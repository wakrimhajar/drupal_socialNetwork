<?php


namespace Drupal\dru_chat\Event;


use Symfony\Component\EventDispatcher\Event;

/**
 * Event fired when a new user presence is registered.
 *
 * @see \Drupal\dru_chat\Controller\MessagesController::presence
 */
class DruChatPresenceEvent extends Event {

  const EVENT_NAME = 'presence_subscription_succeeded';

  /**
   * The presence array item.
   *
   * @var array
   */
  private $presenceItem;

  /**
   * DruChatPresenceEvent constructor.
   *
   * @param array $presenceItem
   *   The presence array item with keys,
   *   'channelName','socketId' and 'sessionId'.
   */
  public function __construct($presenceItem) {
    $this->presenceItem = $presenceItem;
  }

  /**
   * @return array
   *   The current presence item.
   */
  public function getPresenceItem() {
    return $this->presenceItem;
  }
}
