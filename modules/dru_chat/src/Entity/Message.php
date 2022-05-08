<?php

namespace Drupal\dru_chat\Entity;

/**
 * Just a mutable object to pass through drupal events.
 */
class Message {
  public $from;
  public $to;
  public $is_read;
  public $message;

  public function __construct($from, $to, $message, $is_read) {
    $this->from = $from;
    $this->to = $to;
    $this->message = $message;
    $this->is_read = $is_read;
  }

  /**
   * @return mixed
   */
  public function getFrom() {
    return $this->from;
  }

  /**
   * @param mixed $from
   */
  public function setFrom($from): void {
    $this->from = $from;
  }

  /**
   * @return mixed
   */
  public function getTo() {
    return $this->to;
  }

  /**
   * @param mixed $to
   */
  public function setTo($to): void {
    $this->to = $to;
  }

  /**
   * @return mixed
   */
  public function getIsRead() {
    return $this->is_read;
  }

  /**
   * @param mixed $is_read
   */
  public function setIsRead($is_read): void {
    $this->is_read = $is_read;
  }

  /**
   * @return mixed
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * @param mixed $message
   */
  public function setMessage($message): void {
    $this->message = $message;
  }

}
