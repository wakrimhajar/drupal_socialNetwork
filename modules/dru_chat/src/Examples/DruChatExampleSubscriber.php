<?php

namespace Drupal\dru_chat\Examples;

use Drupal\dru_chat\Event\DruChatEvent;
use Drupal\dru_chat\Event\DruChatPresenceEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @todo remember to register your event listener in your module services.yml
 * see https://www.drupal.org/docs/creating-custom-modules/subscribe-to-and-dispatch-events
 * my_module_msg_subscriber:
 * class: Drupal\my_module\EventSubscriber\DruChatExampleSubscriber
 *  tags:
 *   - { name: 'event_subscriber' }
 */

/**
 * Class DruChatExampleSubscriber
 *
 * @package Drupal\dru_chat\Examples
 */
class DruChatExampleSubscriber implements EventSubscriberInterface {

  /**
   * @param \Drupal\dru_chat\Event\DruChatEvent $event
   */
  public function druChatNewMessage(DruChatEvent $event){

    // mutable message item, if setIsRead to TRUE or any other
    // value except FALSE, the message won't be sent OR saved.
    $event->getMessageItem()->setFrom($event->getMessageItem()->from);
    $event->getMessageItem()->setTo($event->getMessageItem()->to);
    $event->getMessageItem()->setMessage('ALTERED MESSAGE NOT CARBON');
    $event->getMessageItem()->setIsRead(FALSE);


  }

  /**
   * @param \Drupal\dru_chat\Event\DruChatPresenceEvent $event
   * "channelName" => "hohjohohohohoouxgsagF",
   * "socketId" => "KJHBCKJSGBKJDSBDJMAsgdsagdkasgddgk",
   * "userId" => 1629375665,
   */
  public function presenceChannelEvent(DruChatPresenceEvent $event) {
    $event['channelName'];
    $event['socketId'];
    $event['sessionId'];
  }

  public static function getSubscribedEvents() {
    return [
      DruChatEvent::NEW_MESSAGE_EVENT => 'druChatNewMessage',
      DruChatPresenceEvent::EVENT_NAME => 'presenceChannelEvent',
    ];
  }

}
