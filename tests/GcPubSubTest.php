<?php

namespace TwipiGroup\Tests;

use Google\Cloud\Core\Iterator\ItemIterator;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use Mockery;
use PHPUnit\Framework\TestCase;
use TwipiGroup\GoogleCloudPubSubPhpAdapter\GcPubSub;

class GcPubSubTest extends TestCase
{
    public static $randomExampleObject;

    /** @var \Mockery\MockInterface|PubSubClient */
    private static $ramdomExampleClient;

    public function setUp()
    {
        self::$ramdomExampleClient = Mockery::mock(PubSubClient::class);
    }

    /**
     * Test constructor
     */
    public function testConstruct()
    {
        self::$randomExampleObject = new GcPubSub(self::$ramdomExampleClient);
        $this->assertSame(self::$ramdomExampleClient, self::$randomExampleObject->getClient());
    }

    /**
     * Test default values
     */
    public function testDefaultValues()
    {
        $this->assertFalse(self::$randomExampleObject->getDebug());
        $this->assertEquals(0, self::$randomExampleObject->getDelay());
        $this->assertEquals(100, self::$randomExampleObject->getMaxMessages());
        $this->assertEquals(100, self::$randomExampleObject->getBatchSize());
        $this->assertEquals(1.0, self::$randomExampleObject->getCallPeriod());
        $this->assertFalse(self::$randomExampleObject->getReturnImmediately());
        $this->assertTrue(self::$randomExampleObject->getAutoCreateTopic());
        $this->assertTrue(self::$randomExampleObject->getAutoCreateSubscription());
        $this->assertTrue(self::$randomExampleObject->getAutoCreateTopicFromSubscription());
        $this->assertEmpty(self::$randomExampleObject->getTopicSuffix());
        $this->assertEmpty(self::$randomExampleObject->getSubscriptionSuffix());
    }

    /**
     * Set Delay
     * Get Delay
     */
    public function testSetGetDelay()
    {
        self::$randomExampleObject->setDelay(5);
        $this->assertEquals(5, self::$randomExampleObject->getDelay());
    }

    /**
     * Set MaxMessages
     * Get MaxMessages
     */
    public function testSetGetMaxMessages()
    {
        self::$randomExampleObject->setMaxMessages(50);
        $this->assertEquals(50, self::$randomExampleObject->getMaxMessages());
    }

    /**
     * Set BatchSize
     * Get BatchSize
     */
    public function testSetGetBatchSize()
    {
        self::$randomExampleObject->setBatchSize(100);
        $this->assertEquals(100, self::$randomExampleObject->getBatchSize());
    }

    /**
     * Set CallPeriod
     * Get CallPeriod
     */
    public function testSetGetCallPeriod()
    {
        self::$randomExampleObject->setCallPeriod(10);
        $this->assertEquals(10, self::$randomExampleObject->getCallPeriod());
    }

    /**
     * Set ReturnImmediately
     * Get ReturnImmediately
     */
    public function testSetGetReturnImmediately()
    {
        self::$randomExampleObject->setReturnImmediately(false);
        $this->assertFalse(self::$randomExampleObject->getReturnImmediately());

        self::$randomExampleObject->setReturnImmediately(true);
        $this->assertTrue(self::$randomExampleObject->getReturnImmediately());
    }

    /**
     * Set AutoCreateTopicFromSubscription
     * Get AutoCreateTopicFromSubscription
     */
    public function testSetGetAutoCreateTopicFromSubscription()
    {
        self::$randomExampleObject->setAutoCreateTopicFromSubscription(false);
        $this->assertFalse(self::$randomExampleObject->getAutoCreateTopicFromSubscription());

        self::$randomExampleObject->setAutoCreateTopicFromSubscription(true);
        $this->assertTrue(self::$randomExampleObject->getAutoCreateTopicFromSubscription());
    }

    /**
     * Set AutoCreateTopic
     * Get AutoCreateTopic
     */
    public function testSetGetAutoCreateTopic()
    {
        self::$randomExampleObject->setAutoCreateTopic(false);
        $this->assertFalse(self::$randomExampleObject->getAutoCreateTopic());

        self::$randomExampleObject->setAutoCreateTopic(true);
        $this->assertTrue(self::$randomExampleObject->getAutoCreateTopic());
    }

    /**
     * Set AutoCreateTopic
     * Get AutoCreateSubscription
     */
    public function testSetGetAutoCreateSubscription()
    {
        self::$randomExampleObject->setAutoCreateSubscription(false);
        $this->assertFalse(self::$randomExampleObject->getAutoCreateSubscription());

        self::$randomExampleObject->setAutoCreateSubscription(true);
        $this->assertTrue(self::$randomExampleObject->getAutoCreateSubscription());
    }

    /**
     * Set Debug
     * Get Debug
     */
    public function testSetGetDebug()
    {
        self::$randomExampleObject->setDebug(true);
        $this->assertTrue(self::$randomExampleObject->getDebug());

        self::$randomExampleObject->setDebug(false);
        $this->assertFalse(self::$randomExampleObject->getDebug());
    }

    /**
     * Get SubscriptionName
     */
    public function testGetSubscriptionName()
    {
        $this->assertEquals(self::$randomExampleObject->getSubscriptionSuffix() . 'mysubscriber', self::$randomExampleObject->getSubscriptionName('mysubscriber'));
    }

    /**
     * Get TopicName
     */
    public function testGetTopicName()
    {
        $this->assertEquals(self::$randomExampleObject->getTopicSuffix() . 'mytopic', self::$randomExampleObject->getTopicName('mytopic'));
    }

    /**
     * Create Topic
     */
    public function testCreateTopic()
    {
        $topicName = 'mytopic_' . uniqid();
        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('createTopic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateTopic(true);
        $pubsub->createTopic($topicName);
    }

    /**
     * Get topic
     */
    public function testGetTopicIfAutoCreateTopicIsEnabledAndTopicUnexisting()
    {
        $topicName = 'mytopic_' . uniqid();
        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);

        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $client->expects($this->once())
            ->method('createTopic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateTopic(true);
        $mytopic = $pubsub->getTopic($topicName);
    }

    public function testGetTopicIfAutoCreateTopicIsDisabledAndTopicUnexisting()
    {
        $topicName = 'mytopic_' . uniqid();
        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unexisting topic');

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);

        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateTopic(false);
        $mytopic = $pubsub->getTopic($topicName);
    }

    public function testGetTopicIfTopicExisting()
    {
        $topicName = 'mytopic_' . uniqid();
        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);

        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $mytopic = $pubsub->getTopic($topicName);
    }

    /**
     * Get topics
     */
    public function testGetTopics()
    {
        /** @var \Mockery\MockInterface|ItemIterator */
        $itemIterator = $this->createMock(ItemIterator::class);

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('topics')
            ->willReturn($itemIterator);

        $pubsub = new GcPubSub($client);
        $mytopics = $pubsub->getTopics();
    }

    /**
     * Update topic
     */
    public function testUpdateTopic()
    {
        $topicName = 'mytopic_' . uniqid();
        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;
        $propertiesToUpdate = [
            'labels' => [
                'foo' => 'bar',
            ],
        ];

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $topic->expects($this->once())
            ->method('update')
            ->with($this->callback(function ($arg) {
                if ($arg == [
                    'labels' => [
                        'foo' => 'bar',
                    ],
                ]) {
                    return true;
                }
                return false;
            }))
            ->willReturn($topic);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->updateTopic($topicName, $propertiesToUpdate);
    }

    /**
     * Delete topic
     */
    public function testDeleteTopic()
    {
        $topicName = 'mytopic_' . uniqid();
        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $topic->expects($this->once())
            ->method('delete');

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->deleteTopic($topicName);
    }

    /**
     * Create Subscription with non empty topic name
     * and existing topic
     */
    public function testCreateSubscriptionWithNonEmptyTopicNameAndExistingTopic()
    {
        $topicName = 'mytopic_' . uniqid();
        $subscriptionName = 'mysubscriber_' . uniqid();

        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;
        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('create');

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $topic->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);

        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $subscriber = $pubsub->createSubscription($subscriptionName, $topicName);
    }

    /**
     * Create Subscription with non empty topic name
     * and unexisting topic
     * and autocreatetopic enabled
     */
    public function testCreateSubscriptionWithNonEmptyTopicNameAndUnexistingTopicAndEnabledAutoCreateTopic()
    {
        $topicName = 'mytopic_' . uniqid();
        $subscriptionName = 'mysubscriber_' . uniqid();

        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;
        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('create');

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $topic->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);

        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $client->expects($this->once())
            ->method('createTopic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateTopic(true);
        $subscriber = $pubsub->createSubscription($subscriptionName, $topicName);
    }

    /**
     * Create Subscription with non empty topic name
     * and unexisting topic
     * and autocreatetopic disabled
     */
    public function testCreateSubscriptionWithNonEmptyTopicNameAndUnexistingTopicAndDisabledAutoCreateTopic()
    {
        $topicName = 'mytopic_' . uniqid();
        $subscriptionName = 'mysubscriber_' . uniqid();

        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unexisting topic');

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);

        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateTopic(false);
        $subscriber = $pubsub->createSubscription($subscriptionName, $topicName);
    }

    /**
     * Create Subscription with empty topic name
     * and autoCreateTopicNameFromSubscriptionName enabled
     */
    public function testCreateSubscriptionWithEmptyTopicNameAndEnabledAutoCreateTopicNameFromSubscriptionName()
    {
        $topicName = '';
        $subscriptionName = 'mytopicfromsubscription_' . uniqid();

        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $subscriptionName;
        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('create');

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $topic->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);

        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $client->expects($this->once())
            ->method('createTopic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateTopicFromSubscription(true);
        $subscriber = $pubsub->createSubscription($subscriptionName, $topicName);
    }

    /**
     * Create Subscription with empty topic name
     * and autoCreateTopicNameFromSubscriptionName disabled
     */
    public function testCreateSubscriptionWithEmptyTopicNameAndDisabledAutoCreateTopicNameFromSubscriptionName()
    {
        $topicName = '';
        $subscriptionName = 'mysubscriber_' . uniqid();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Empty topic name');

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateTopicFromSubscription(false);
        $subscriber = $pubsub->createSubscription($subscriptionName, $topicName);
    }

    /**
     * Get Subscriptions
     */
    public function testGetSubscriptions()
    {
        /** @var \Mockery\MockInterface|ItemIterator */
        $itemIterator = $this->createMock(ItemIterator::class);

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('subscriptions')
            ->willReturn($itemIterator);

        $pubsub = new GcPubSub($client);
        $mysubscribers = $pubsub->getSubscriptions();
    }

    /**
     * Update Subscription
     */
    public function testUpdateSubscription()
    {
        $subscriptionName = 'mysubscriber_' . uniqid();
        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;
        $propertiesToUpdate = [
            'labels' => [
                'label-1' => 'value',
            ],
        ];

        /** @var \Mockery\MockInterface|subscription */
        $subscription = $this->createMock(subscription::class);
        $subscription->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $subscription->expects($this->once())
            ->method('update')
            ->with($this->callback(function ($arg) {
                if ($arg == [
                    'labels' => [
                        'label-1' => 'value',
                    ],
                ]) {
                    return true;
                }
                return false;
            }))
            ->willReturn($subscription);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);

        $pubsub = new GcPubSub($client);
        $pubsub->updateSubscription($subscriptionName, $propertiesToUpdate);
    }

    /**
     * Delete Subscription
     */
    public function testDeleteSubscription()
    {
        $subscriptionName = 'mysubscriber_' . uniqid();
        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $subscription->expects($this->once())
            ->method('delete');

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);

        $pubsub = new GcPubSub($client);
        $pubsub->deleteSubscription($subscriptionName);
    }

    /**
     * Get Subscription with non empty topic name
     * and unexisting topic
     * and unexisting subscription
     * and autocreatetopic enabled
     * and autocreatesubscription enabled
     */
    public function testGetSubscriptionWithNonEmptyTopicNameAndUnexistingTopicAndEnabledAutoCreateSubscriptionAndEnabledAutoCreateTopicAndUnexistingSubscription()
    {
        $topicName = 'mytopic_' . uniqid();
        $subscriptionName = 'mysubscriber_' . uniqid();

        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;
        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('exists')
            ->willReturn(false);
        $subscription->expects($this->once())
            ->method('create');

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $topic->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);
        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);
        $client->expects($this->once())
            ->method('createTopic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateTopic(true);
        $pubsub->setAutoCreateSubscription(true);
        $subscriber = $pubsub->getSubscription($subscriptionName, $topicName);
    }

    /**
     * Get Subscription with non empty topic name
     * and unexisting topic
     * and unexisting subscription
     * and autocreatetopic disabled
     * and autocreatesubscription enabled
     */
    public function testGetSubscriptionWithNonEmptyTopicNameAndUnexistingTopicAndEnabledAutoCreateSubscriptionAndDisabledAutoCreateTopicAndUnexistingSubscription()
    {
        $topicName = 'mytopic_' . uniqid();
        $subscriptionName = 'mysubscriber_' . uniqid();

        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;
        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unexisting topic');

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);
        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateTopic(false);
        $subscriber = $pubsub->getSubscription($subscriptionName, $topicName);
    }

    /**
     * Get Subscription with non empty topic name
     * and existing topic
     * and unexisting subscription
     * and autocreatesubscription enabled
     */
    public function testGetSubscriptionWithNonEmptyTopicNameAndExistingTopicAndEnabledAutoCreateSubscriptionAndUnexistingSubscription()
    {
        $topicName = 'mytopic_' . uniqid();
        $subscriptionName = 'mysubscriber_' . uniqid();

        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;
        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('exists')
            ->willReturn(false);
        $subscription->expects($this->once())
            ->method('create');

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $topic->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);
        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateSubscription(true);
        $subscriber = $pubsub->getSubscription($subscriptionName, $topicName);
    }

    /**
     * Get Subscription with non empty topic name
     * and existing topic
     * and unexisting subscription
     * and autocreatesubscription disabled
     */
    public function testGetSubscriptionWithNonEmptyTopicNameAndExistingTopicAndDisabledAutoCreateSubscriptionAndUnexistingSubscription()
    {
        $topicName = 'mytopic_' . uniqid();
        $subscriptionName = 'mysubscriber_' . uniqid();

        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unexisting subscription');

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateSubscription(false);
        $subscriber = $pubsub->getSubscription($subscriptionName, $topicName);
    }

    /**
     * Get Subscription with empty topic name
     * and unexisting topic
     * and unexisting subscription
     * and autocreateTopic enabled
     * and autocreatesubscription enabled
     */
    public function testGetSubscriptionWithEmptyTopicNameAndEnabledAutoCreateTopicAndEnabledAutoCreateSubscriptionAndUnexistingSubscription()
    {
        $topicName = '';
        $subscriptionName = 'mytopicfromsubscription_' . uniqid();

        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $subscriptionName;
        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('create');

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $topic->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);
        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);
        $client->expects($this->once())
            ->method('createTopic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $pubsub->setAutoCreateTopicFromSubscription(true);
        $subscriber = $pubsub->getSubscription($subscriptionName, $topicName);
    }

    /**
     * Publish string data
     */
    public function testPublishWithStringData()
    {
        $topicName = 'mytopic_' . uniqid();
        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;
        $datas = 'test';

        /** @var \Mockery\MockInterface|Message */
        $message = $this->createMock(Message::class);

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $topic->expects($this->once())
            ->method('publish')
            ->with($this->callback(function ($arg) {
                if ($arg == [
                    'data' => 'test',
                ]) {
                    return true;
                }
                return false;
            }))
            ->will($this->returnValue([]));

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $subscriber = $pubsub->publish($topicName, $datas);
    }

    /**
     * Publish array of datas
     */
    public function testPublishWithArrayData()
    {
        $topicName = 'mytopic_' . uniqid();
        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;
        $datas = [
            'test' => 'test',
        ];

        /** @var \Mockery\MockInterface|Message */
        $message = $this->createMock(Message::class);

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $topic->expects($this->once())
            ->method('publish')
            ->with($this->callback(function ($arg) {
                if ($arg == [
                    'data' => json_encode([
                        'test' => 'test',
                    ]),
                ]) {
                    return true;
                }
                return false;
            }))
            ->will($this->returnValue([]));

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $subscriber = $pubsub->publish($topicName, $datas);
    }

    /**
     * Publish with attributes
     */
    public function testPublishWithAttributes()
    {
        $topicName = 'mytopic_' . uniqid();
        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;
        $datas = 'test';
        $attributes = [
            'attribute1' => 'value',
        ];

        /** @var \Mockery\MockInterface|Message */
        $message = $this->createMock(Message::class);

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $topic->expects($this->once())
            ->method('publish')
            ->with($this->callback(function ($arg) {
                if ($arg == [
                    'data' => 'test',
                    'attributes' => [
                        'attribute1' => 'value',
                    ],
                ]) {
                    return true;
                }
                return false;
            }))
            ->will($this->returnValue([]));

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        $pubsub = new GcPubSub($client);
        $subscriber = $pubsub->publish($topicName, $datas, $attributes);
    }

    /**
     * Publish with wrong attributes
     */
    public function testPublishWithWrongAttributes()
    {
        $topicName = 'mytopic_' . uniqid();
        $topicFullName = self::$randomExampleObject->getTopicSuffix() . $topicName;
        $datas = 'test';
        $attributes = [
            'attribute1' => [
                'test' => 'wrong',
            ],
        ];

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('PubSubMessage attributes only accept key-value string pairs');

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);

        $pubsub = new GcPubSub($client);
        $subscriber = $pubsub->publish($topicName, $datas, $attributes);
    }

    /**
     * Subscribe
     */
    public function testSubscribeWithMessagesAndOnePull()
    {
        $message1 = new Message(['data' => '{"test":"array"}'], ['ackId' => 1]);
        $message2 = new Message(['data' => '"test string"'], ['ackId' => 2]);
        $messageBatch = [
            $message1,
            $message2,
        ];

        $topicName = 'mytopic_' . uniqid();
        $subscriptionName = 'mysubscription_' . uniqid();
        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $subscription->expects($this->once())
            ->method('pull')
            ->with($this->callback(function ($arg) {
                if ($arg == [
                    'maxMessages' => 10,
                    'returnImmediately' => true,
                ]) {
                    return true;
                }
                return false;
            }))
            ->willReturn($messageBatch);

        $subscription->expects($this->exactly(2))
            ->method('acknowledge')
            ->withConsecutive([$this->callback(function ($arg) {
                if ($arg->data() == '{"test":"array"}' && $arg->ackId() == 1) {
                    return true;
                }
                return false;
            })], [$this->callback(function ($arg) {
                if ($arg->data() == '"test string"' && $arg->ackId() == 2) {
                    return true;
                }
                return false;
            })]);

        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);

        /** @var \Mockery\MockInterface|stdClass */
        $handler = $this->createPartialMock(\stdClass::class, ['handle']);
        $handler->expects($this->exactly(2))
            ->method('handle')
            ->withConsecutive([$this->callback(function ($arg) {
                if ($arg == [
                    'test' => 'array',
                ]) {
                    return true;
                }
                return false;
            })], ['test string']);

        $pubsub = new GcPubSub($client);
        $pubsub->setMaxMessages(10);
        $pubsub->setReturnImmediately(true);
        $subscriber = $pubsub->subscribe($subscriptionName, [$handler, 'handle'], $topicName, false);
    }

    /**
     * Subscribe
     */
    public function testSubscribeWithMessagesAndOnePullAndUnexistingSubscriptionAndUnexistingTopic()
    {
        $message1 = new Message(['data' => '{"test":"array"}'], ['ackId' => 1]);
        $message2 = new Message(['data' => '"test string"'], ['ackId' => 2]);
        $messageBatch = [
            $message1,
            $message2,
        ];

        $uniqid = uniqid();
        $subscriptionName = $uniqid;
        $topicFullName = $uniqid;
        $subscriptionFullName = self::$randomExampleObject->getSubscriptionSuffix() . $subscriptionName;

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('exists')
            ->willReturn(false);
        $subscription->expects($this->once())
            ->method('create')
            ->willReturn($subscription);

        $subscription->expects($this->once())
            ->method('pull')
            ->with($this->callback(function ($arg) {
                if ($arg == [
                    'maxMessages' => 10,
                    'returnImmediately' => true,
                ]) {
                    return true;
                }
                return false;
            }))
            ->willReturn($messageBatch);

        $subscription->expects($this->exactly(2))
            ->method('acknowledge')
            ->withConsecutive([$this->callback(function ($arg) {
                if ($arg->data() == '{"test":"array"}' && $arg->ackId() == 1) {
                    return true;
                }
                return false;
            })], [$this->callback(function ($arg) {
                if ($arg->data() == '"test string"' && $arg->ackId() == 2) {
                    return true;
                }
                return false;
            })]);

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('subscription')
            ->willReturn($subscription);
            
        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);
        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);
        $client->expects($this->once())
            ->method('createTopic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        /** @var \Mockery\MockInterface|stdClass */
        $handler = $this->createPartialMock(\stdClass::class, ['handle']);
        $handler->expects($this->exactly(2))
            ->method('handle')
            ->withConsecutive([$this->callback(function ($arg) {
                if ($arg == [
                    'test' => 'array',
                ]) {
                    return true;
                }
                return false;
            })], ['test string']);

        $pubsub = new GcPubSub($client);
        $pubsub->setMaxMessages(10);
        $pubsub->setReturnImmediately(true);
        $subscriber = $pubsub->subscribe($subscriptionName, [$handler, 'handle'], '', false);
    }

    /**
     * Subscribe
     */
    public function testSubscribeWithMessagesAndOnePullAndUnexistingSubscriptionAndUnexistingTopicAndSuffixes()
    {
        $message1 = new Message(['data' => '{"test":"array"}'], ['ackId' => 1]);
        $message2 = new Message(['data' => '"test string"'], ['ackId' => 2]);
        $messageBatch = [
            $message1,
            $message2,
        ];

        $uniqid = uniqid();
        $subscriptionName = $uniqid;
        $topicFullName = 'mytopic_'.$uniqid;
        $subscriptionFullName = 'mysubscriber_' . $subscriptionName;

        /** @var \Mockery\MockInterface|Subscription */
        $subscription = $this->createMock(Subscription::class);
        $subscription->expects($this->once())
            ->method('exists')
            ->willReturn(false);
        $subscription->expects($this->once())
            ->method('create')
            ->willReturn($subscription);

        $subscription->expects($this->once())
            ->method('pull')
            ->with($this->callback(function ($arg) {
                if ($arg == [
                    'maxMessages' => 10,
                    'returnImmediately' => true,
                ]) {
                    return true;
                }
                return false;
            }))
            ->willReturn($messageBatch);

        $subscription->expects($this->exactly(2))
            ->method('acknowledge')
            ->withConsecutive([$this->callback(function ($arg) {
                if ($arg->data() == '{"test":"array"}' && $arg->ackId() == 1) {
                    return true;
                }
                return false;
            })], [$this->callback(function ($arg) {
                if ($arg->data() == '"test string"' && $arg->ackId() == 2) {
                    return true;
                }
                return false;
            })]);

        /** @var \Mockery\MockInterface|Topic */
        $topic = $this->createMock(Topic::class);
        $topic->expects($this->once())
            ->method('subscription')
            ->willReturn($subscription);
            
        /** @var \Mockery\MockInterface|PubSubClient */
        $client = $this->createMock(PubSubClient::class);
        $client->expects($this->once())
            ->method('subscription')
            ->with($this->equalTo($subscriptionFullName))
            ->willReturn($subscription);
        $client->expects($this->once())
            ->method('topic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);
        $client->expects($this->once())
            ->method('createTopic')
            ->with($this->equalTo($topicFullName))
            ->willReturn($topic);

        /** @var \Mockery\MockInterface|stdClass */
        $handler = $this->createPartialMock(\stdClass::class, ['handle']);
        $handler->expects($this->exactly(2))
            ->method('handle')
            ->withConsecutive([$this->callback(function ($arg) {
                if ($arg == [
                    'test' => 'array',
                ]) {
                    return true;
                }
                return false;
            })], ['test string']);

        $pubsub = new GcPubSub($client);
        $pubsub->setMaxMessages(10);
        $pubsub->setReturnImmediately(true);
        $pubsub->setTopicSuffix('mytopic_');
        $pubsub->setSubscriptionSuffix('mysubscriber_');
        $subscriber = $pubsub->subscribe($subscriptionName, [$handler, 'handle'], '', false);
    }

    /**
     * Set TopicSuffix
     * Get TopicSuffix
     */
    public function testSetGetTopicSuffix()
    {
        self::$randomExampleObject->setTopicSuffix('topicSuffix_');
        $this->assertEquals('topicSuffix_', self::$randomExampleObject->getTopicSuffix());
    }

    /**
     * Set SubscriptionSuffix
     * Get SubscriptionSuffix
     */
    public function testSetGetSubscriptionSuffix()
    {
        self::$randomExampleObject->setSubscriptionSuffix('subscriptionSuffix');
        $this->assertEquals('subscriptionSuffix', self::$randomExampleObject->getSubscriptionSuffix());
    }
}
