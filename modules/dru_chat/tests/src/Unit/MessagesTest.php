<?php


namespace Drupal\Tests\dru_chat\Unit;


use Drupal\dru_chat\Service\Messages;
use Drupal\Tests\UnitTestCase;

class MessagesTest extends UnitTestCase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['dru_chat'];

  protected $Messages;

  public function setUp() : void {
    parent::setUp();

    $this->Messages = new Messages();
  }

  public function testSendMessage() {
    $expected = $this->Messages->sendMessage();
    $this->assertEquals($expected, intval('500'));
  }

}
// https://www.drupal.org/docs/automated-testing/phpunit-in-drupal/mocking-entities-and-services-with-phpunit-and-mocks
