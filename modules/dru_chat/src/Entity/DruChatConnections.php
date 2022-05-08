<?php

namespace Drupal\dru_chat\Entity;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines DruChatConnections entity.
 *
 * @ingroup dru_chat_connections
 *
 * @ContentEntityType(
 *   id = "dru_chat_connections",
 *   label = @Translation("DruChatConnections"),
 *   base_table = "dru_chat_connections",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "type",
 *     "uuid" = "uuid",
 *   },
 * )
 */


class DruChatConnections extends ContentEntityBase implements ContentEntityInterface {

  public function getRevisionId() {
    return $this->get('c_resource_id')->value;
  }


  public function setResourceId($c_resource_id) {
    $this->get('c_resource_id')->value = $c_resource_id;
    return $this;
  }


  public function geUser() {
    return $this->get('c_user_id')->value;
  }


  public function setUser($c_user) {
    $this->get('c_user_id')->value = $c_user;
    return $this;
  }



  public function getUsers() {
    $users = $this->get('c_users')->value;
    return Json::decode($users);

  }


  public function setUsers($c_users) {
    $users = Json::encode($c_users);
    $this->get('c_users')->value = $users;
    return $this;
  }

  public function getMessage() {
    return $this->get('c_message')->value;

  }


  public function setMessage($message) {
    $this->get('c_message')->value = $message;
    return $this;

  }


  /**
   * Determines the schema for slack_settings entity table
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the content entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the content entity.'))
      ->setReadOnly(TRUE);

    // if using socket server
    $fields['c_resource_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Resource Id'))
      ->setDescription(t('The resource id assigned by websockets/Ratchet.'));

    $fields['c_user_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('User'))
      ->setDescription(t('The user assigned this resource .'));

    $fields['c_users'] = BaseFieldDefinition::create('string')
      ->setLabel(t('to users'))
      ->setDescription(t('Array of users message is sent to'));

    $fields['c_message'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Message to users'))
      ->setDescription(t('Message about'));

    return $fields;
  }

}
