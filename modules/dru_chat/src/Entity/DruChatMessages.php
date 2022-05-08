<?php

namespace Drupal\dru_chat\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines DruChatMessages entity.
 *
 * @ingroup dru_chat_messages
 *
 * @ContentEntityType(
 *   id = "dru_chat_messages",
 *   label = @Translation("DruChatMessages"),
 *   base_table = "dru_chat_messages",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "type",
 *     "uuid" = "uuid",
 *   },
 * )
 */


class DruChatMessages extends ContentEntityBase implements ContentEntityInterface {

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  public function getFrom() {
    return $this->get('from')->value;
  }


  public function setFrom($from) {
    $this->set('from', $from);
    return $this;
  }


  public function getTo() {
    return $this->get('to')->value;
  }


  public function setTo($to) {
    $this->set('to', $to);
    return $this;
  }



  public function getMessage() {
    return $this->get('message')->value;
  }


  public function setMessage($message) {
    $this->set('message', $message);
    return $this;
  }


  public function getIsRead() {
    return $this->get('is_read')->value;
  }


  public function setIsRead($is_read) {
    $this->set('is_read', $is_read);
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

    $fields['from'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User sending'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDescription(t('The user sending the message'));

    $fields['to'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Target recipient user'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDescription(t('The user message is sent to .'));

    $fields['message'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Message to users'))
      ->setDescription(t('Message about'));

    $fields['is_read'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is Read'))
      ->setDescription(t('State if message is read by the recipient'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    return $fields;
  }

}
