<?php

namespace Drupal\simple_like_button\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Simple like entities.
 *
 * @ingroup simple_like
 */
interface SimpleLikeInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Simple like name.
   *
   * @return string
   *   Name of the Simple like.
   */
  public function getName();

  /**
   * Sets the Simple like name.
   *
   * @param string $name
   *   The Simple like name.
   *
   * @return \Drupal\simple_like\Entity\SimpleLikeInterface
   *   The called Simple like entity.
   */
  public function setName($name);

  /**
   * Gets the Simple like creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Simple like.
   */
  public function getCreatedTime();

  /**
   * Sets the Simple like creation timestamp.
   *
   * @param int $timestamp
   *   The Simple like creation timestamp.
   *
   * @return \Drupal\simple_like\Entity\SimpleLikeInterface
   *   The called Simple like entity.
   */
  public function setCreatedTime($timestamp);

}
