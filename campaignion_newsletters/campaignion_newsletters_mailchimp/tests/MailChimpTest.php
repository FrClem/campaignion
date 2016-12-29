<?php

namespace Drupal\campaignion_newsletters_mailchimp;

use \Drupal\campaignion_newsletters\NewsletterList;

class MailChimpTest extends \DrupalUnitTestCase {

  public function test_key2dc_validKey() {
    $this->assertEquals('us12', MailChimp::key2dc('testkey-us12'));
  }

  protected function mockChimp() {
    $api = $this->getMockBuilder(Rest\MailChimpClient::class)
      ->setMethods(['send'])
      ->disableOriginalConstructor()
      ->getMock();
    return [$api, new MailChimp($api, 'testname')];
  }

  public function test_getLists_noLists() {
    list($api, $provider) = $this->mockChimp();
    $api->expects($this->once())->method('send')->willReturn(['lists' => []]);
    $this->assertEquals([], $provider->getLists());
  }

  public function test_getLists_oneList() {
    list($api, $provider) = $this->mockChimp();
    $paging = ['offset' => 0, 'count' => 100];
    $list = ['id' => 'a1234', 'name' => 'mocknewsletters'];
    $list_query = ['fields' => 'lists.id,lists.name'] + $paging;
    $merge_query = ['fields' => 'merge_fields.tag'] + $paging;
    $webhook_query = ['fields' => 'webhooks.url'] + $paging;
    $api->expects($this->exactly(5))->method('send')->withConsecutive(
      [$this->equalTo('/lists'), $this->equalTo($list_query)],
      [$this->equalTo('/lists'), $this->equalTo(['offset' => 100] + $list_query)],
      [$this->equalTo('/lists/a1234/merge-fields'), $this->equalTo($merge_query)],
      [$this->equalTo('/lists/a1234/webhooks'), $this->equalTo($webhook_query)],
      [$this->equalTo('/lists/a1234/webhooks')]
    )->will($this->onConsecutiveCalls(
      ['lists' => [$list]],
      ['lists' => []],
      ['merge_fields' => []],
      ['webhooks' => []],
      $this->throwException(Rest\ApiError::fromHttpError(new Rest\HttpError((object) [
        'code' => 400,
        'status_message' => 'Bad Request',
        'data' => json_encode(['title' => '', 'detail' => '', 'errors' => []]),
      ]), 'POST', '/lists/a1234/webhooks'))
    ));
    $this->assertEquals([NewsletterList::fromData([
      'identifier' => $list['id'],
      'title'      => $list['name'],
      'source'     => 'MailChimp-testname',
      'data'       => (object) ($list + ['merge_vars' => []]),
    ])], $provider->getLists());
  }

}

