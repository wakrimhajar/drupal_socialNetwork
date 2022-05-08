<?php

namespace Drupal\dru_chat\Commands;

use Drush\Commands\DrushCommands;
use Faker\Factory;

/**
 * Class for "DruChatDrushCommands".
 *
 * @package Drupal\dru_chat\Commands
 */
class DruChatDrushCommands extends DrushCommands {

  /**
   * Drush command to generate dummy messages.
   *
   * @command dru_chat:generate-messages
   * @aliases dc-gen-msg dc-gn-msg
   * @usage dru_chat:generate-messages
   */
  public function generateMessages() {
    $entity = \Drupal::entityTypeManager()->getStorage('dru_chat_messages');

    try {
      // Create demos.
      $faker = Factory::create();

      for ($i = 0; $i < 500; ++$i) {

        do {

          $from = rand(1, 20);
          $to = rand(1, 20);
          $is_read = rand(0, 1);

        } while ($from === $to);

        $values = [
          'from' => $from,
          'to' => $to,
          'message' => $faker->sentence,
          'is_read' => $is_read,
        ];
        $entity->create($values)->save();


      }

      $text = '500 messages have been generated, you can run the command to generate 500 more.';
      $this->output()->writeln($text);

    } catch (\Throwable $ex) {

      $install = '<error>An Error occured, make sure to Install faker by running : `composer require fzaninotto/faker --dev`.</error>';
      $user_count = '<error>Also make sure to have at least 20 users in your Drupal app.</error>';
      $this->output()->writeln($ex->getMessage());
      $this->output()->writeln($install);
      $this->output()->writeln($user_count);
    }
  }

  /**
   * Drush command to delete messages (generated).
   *
   * @command dru_chat:delete-messages
   * @aliases dc-del-msg dc-dl-msg
   * @usage dru_chat:delete-messages
   */
  public function deleteGeneratedMessages() {
    $entity = \Drupal::entityTypeManager()->getStorage('dru_chat_messages');
    $query = $entity->getQuery();
  }
}
