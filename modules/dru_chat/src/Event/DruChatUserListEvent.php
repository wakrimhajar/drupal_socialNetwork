<?php

namespace Drupal\dru_chat\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Defines DruChatUserListEvent.
 *   Fired when user list is being loaded, to the Dru Chat block.
 */
class DruChatUserListEvent extends Event {

  const DRU_CHAT_USER_LIST_EVENT = 'dru_chat_user_list_event';

  /**
   * List of user ids.
   *
   * @var array
   */
  protected $users;

  /**
   * DruChatUserListEvent constructor.
   *
   * @param array $users
   *   An array of user ids.
   */
  public function __construct(array $users) {
    $this->users = $users;
  }

  // Should return an array of
  // auth users as well as an array
  // of guest users.
  // guest user ids are handled differently, from
  // list display and message table
  // see service at ..TODO
  // still looking for ideas to better manage the
  // guest functionalities for this module.
  public function getUsers() : array{
    if (!isset($this->users['guests'])) {
      $this->users['guests'] = [];
    }
    return $this->users;
  }

  /**
   * The $users['auth'] = queried user ids, developers should
   *   modify to exclude some roles as needed/required,
   *   $users['guests'] = 'TODO:: implement your own guest session
   *   manager at the moment'.
   *
   * @param array $users
   *   An array of users.
   *
   * @return $this
   *   This event and its definitions.
   */
  public function setUsers(array $users): DruChatUserListEvent {
    $this->users = $users;
    return $this;
  }
}
