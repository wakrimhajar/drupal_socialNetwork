<?php
namespace Drupal\dru_chat\Event;

use Drupal\dru_chat\Entity\Message;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines DruChatEvent class.
 */
class DruChatEvent extends Event {

  const NEW_MESSAGE_EVENT = 'dru_chat_send_message_event';

  /**
   * The current message instance.
   *
   * @var \Drupal\dru_chat\Entity\Message
   */
  protected $msg_item;

  /**
   * DruChatEvent constructor.
   *
   * @param \Drupal\dru_chat\Entity\Message $msg_item
   *   The Message class.
   */
  public function __construct(Message $msg_item) {
    $this->msg_item = $msg_item;
  }

  /**
   * @return \Drupal\dru_chat\Entity\Message
   *   The current message object instance.
   */
  public function getMessageItem(){
    return $this->msg_item;
  }

}
