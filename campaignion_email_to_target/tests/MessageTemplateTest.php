<?php

namespace Drupal\campaignion_email_to_target;


class MessageTemplateTest extends \DrupalUnitTestCase {
  public function test_toArray() {
    $t = new MessageTemplate([
      'subject' => 'Test Subject',
      'label' => 'Test label',
    ]);
    $this->assertEqual([
      'id' => NULL,
      'type' => 'message',
      'label' => 'Test label',
      'filters' => [],
      'subject' => 'Test Subject',
      'header' => '',
      'message' => '',
      'footer' => '',
    ], $t->toArray());
  }

  public function test_construct_fromArray() {
    $data = [
      'type' => 'message',
      'label' => 'Test label',
      'filters' => [],
      'subject' => 'Test Subject',
      'header' => '',
      'message' => '',
      'footer' => '',
    ];
    $t = new MessageTemplate($data);
    $this->assertEqual('Test Subject', $t->subject);
  }

  /**
   * Test cloning of message templates.
   */
  public function testMessageCloning() {
    $data = [
      'id' => 42,
      'type' => 'message',
      'label' => 'Test label',
      'filters' => [[
        'id' => 42,
        'type' => 'test',
        'config' => ['value' => 1],
      ]],
      'subject' => 'Test Subject',
      'header' => '',
      'message' => '',
      'footer' => '',
    ];
    $t1 = new MessageTemplate($data);
    $t2 = clone $t1;

    // Test that the cloned message counts as being new.
    $this->assertNull($t2->id);
    $this->assertTrue($t2->isNew());

    // Test that filters have been cloned too.
    $this->assertTrue($t2->filters[0]->isNew());
    $t2->filters[0]->config['value'] = 2;
    $this->assertEqual(1, $t1->filters[0]->config['value']);
  }

}
