<?php

namespace Drupal\dru_chat\Plugin\Block;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\dru_chat\Event\DruChatUserListEvent;
use Drupal\dru_chat\Service\Messages;
use Drupal\dru_chat_guest\Service\Guests;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Provides a 'DruChatBlock' block.
 *
 * @Block(
 *  id = "dru_chat_block",
 *  admin_label = @Translation("Dru chat block"),
 * )
 */
class DruChatBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   *
   * @var \Drupal\dru_chat\Service\Messages
   */
  private $messages;

  /**
   * The module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $accountProxy;

  /**
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  private $csrfToken;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * DruChatBlock constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\dru_chat\Service\Messages $messages
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   * @param \Drupal\Core\Session\AccountProxyInterface $accountProxy
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrfToken
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              Messages $messages,
                              ConfigFactoryInterface $configFactory,
                              ModuleHandlerInterface $moduleHandler,
                              AccountProxyInterface $accountProxy,
                              CsrfTokenGenerator $csrfToken,
                              EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->messages = $messages;
    $this->configFactory = $configFactory;
    $this->moduleHandler = $moduleHandler;
    $this->accountProxy = $accountProxy;
    $this->csrfToken = $csrfToken;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return \Drupal\dru_chat\Plugin\Block\DruChatBlock|static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dru_chat.messages'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('csrf_token'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = $this->accountProxy;

    // If user->id() === 0 and dru_chat_guest module is enabled
    // we need to save the guest user.
    // @todo for guest
    /*
    $guest_enabled = $this->moduleHandler
      ->moduleExists('dru_chat_guest');


    if ($user->id() == 0 && $guest_enabled) {
      $guest_session = \Drupal::service('dru_chat.guest');
      $guest_session->guestSession();
    }
    */

    $config = $this->configFactory->getEditable('dru_chat.settings');
    $cluster = $config->get('cluster');
    $auth_key = $config->get('auth_key');

    $token = $this->csrfToken;


    $new_msg_url = Url::fromRoute('dru_chat.new_messages');
    $new_msg_url_token = $token->get($new_msg_url->getInternalPath());
    $new_msg_url->setOptions(
      ['absolute' => TRUE, 'query' => ['token' => $new_msg_url_token]]
    );
    $new_msg_url = $new_msg_url->toString();

    $msgs_url = Url::fromRoute('<front>');
    // $msgs_url_token = $token->get($msgs_url->getInternalPath());
    // $msgs_url->setOptions(['absolute' => TRUE, 'user' => NULL,
    // 'query' => ['token' => $msgs_url_token]]);
    $msgs_url->setOptions(['absolute' => TRUE]);
    $msgs_url = $msgs_url->toString();

    // Do better here, users online and such.
    $users = $this->entityTypeManager->getStorage('user');
    $user_ids = $users->getQuery()
      ->condition('status', 1)
      ->condition('uid', $user->id(), '!=')
      ->pager(15)
      ->execute();

    // Dispatch event for other listeners to alter the user array
    // including dru_chat_guest module, that manages guest users
    // when enabled.
    $user_list = new DruChatUserListEvent(['auth' => $user_ids]);
    $user_list_event = \Drupal::service('event_dispatcher');
    $user_list_event->dispatch(DruChatUserListEvent::DRU_CHAT_USER_LIST_EVENT, $user_list);

    // Get unread messages from user_ids.
    $unread_messages = $this->messages->countUnread($user_ids, $user->id());
    $total_unread = [];
    if ($unread_messages) {
      foreach ($unread_messages as $unread_message) {
        array_push($total_unread, $unread_message['message_count']);
      }
    }

    $users = $users->loadMultiple($user_list->getUsers()['auth']);
    $guests = $user_list->getUsers()['guests'];

    // Get CSRF token service.
    //$token_generator = \Drupal::csrfToken();
    // @todo token for guests

    $presence_url = Url::fromRoute('dru_chat.presence');
    //$presence_url_token = $token->get($presence_url->getInternalPath());
    $presence_url->setOptions(
      ['absolute' => TRUE, /*'query' => ['token' => $presence_url_token]*/]
    );

    /*$slack_service = \Drupal::service('slack.slack_service');
    $client = \Drupal::service('http_client');
    $slack_service->sendMessage('HELLLOOOhjkhjlkhjkjhkvdxcvfdssgfdgdgfdsij', '#general', 'NICHOILASSS');
    dump($slack_service);*/

    /*$client = \Drupal::service('http_client');
    $payload = json_encode(
      [
        'channel'    => '#general',
        'text'       => 'HELLLOOO',
        'username'   => 'NICHOILASSS'
        //'icon_emoji' => $icon_emoji
      ]);

    $response = $client->post('https://hooks.slack.com/services/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',['body' => $payload]);*/

    return [
      '#theme' => 'dru_chat_block',
      '#cache' => ['max-age' => 0],
      '#data' => [
        'title' => 'Testing title',
        'total_unread' => array_sum($total_unread),
        'pusher_cluster' => $cluster,
        'pusher_app_key' => $auth_key,
        'users' => $users,
        'guests' => $guests,
        'current_id' => $user->id(),
        'unread_messages' => $unread_messages,
      ],
      '#attached' => [
        'library' => [
          'dru_chat/chat_libs',
        ],
        'drupalSettings' => [
          'dru_chat' => [
            'current_id' => $user->id(),
            'pusher_cluster' => $cluster,
            'pusher_app_key' => $auth_key,
            'new_msg_url' => $new_msg_url,
            'msgs_url' => $msgs_url . "dru-chat/messages/",
            'presence_url' => $presence_url->toString(),
          ],
        ],
      ],
    ];
  }

}
