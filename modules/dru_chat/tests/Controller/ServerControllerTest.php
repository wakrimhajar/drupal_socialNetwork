<?php

namespace Drupal\dru_chat\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the dru_chat module.
 */
class ServerControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "dru_chat ServerController's controller functionality",
      'description' => 'Test Unit for module dru_chat and controller ServerController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests dru_chat functionality.
   */
  public function testServerController() {
    // Check that the basic functions of module dru_chat.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
