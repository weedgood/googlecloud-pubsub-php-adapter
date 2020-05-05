<?php

namespace TwipiGroup\GoogleCloudPubSubPhpAdapter;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;

class GcPubSub
{
    /**
     * The Google PubSubClient instance.
     *
     * @var PubSubClient
     */
    protected $client;

    /**
     * Debug mode.
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * Manually delay in seconds between each pull.
     * returnImmediately must be set to true to be efficient
     *
     * @var int
     */
    protected $delay = 0;

    /**
     * Max messages for each pull.
     *
     * @var int
     */
    protected $maxMessages = 100;

    /**
     * Max messages for each batch.
     *
     * @var int
     */
    protected $batchSize = 100;

    /**
     * Max time in seconds between each batch publish.
     *
     * @var float
     */
    protected $callPeriod = 1.0;

    /**
     * If return immediately after pull.
     * https: //cloud.google.com/pubsub/docs/reference/rest/v1/projects.subscriptions/pull (deprecated)
     *
     * @var bool
     */
    protected $returnImmediately = false;

    /**
     * If auto create topic.
     *
     * @var bool
     */
    protected $autoCreateTopic = true;

    /**
     * If auto create subscription.
     *
     * @var bool
     */
    protected $autoCreateSubscription = true;

    /**
     * If auto create topic when create subscription and topic name empty only.
     *
     * @var bool
     */
    protected $autoCreateTopicFromSubscription = true;

    /**
     * Topic suffix.
     *
     * @var string
     */
    protected $topicSuffix = '';

    /**
     * Subscription suffix.
     *
     * @var string
     */
    protected $subscriptionSuffix = '';

    /**
     * Create a new instance of GC PubSub.
     *
     * @param PubSubClient $client
     */
    public function __construct(PubSubClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a Pub/Sub topic.
     *
     * @param string $topicName  The Pub/Sub topic name.
     * @return Topic
     */
    public function createTopic(string $topicName): Topic
    {
        return $this->client->createTopic($this->getTopicName($topicName));
    }

    /**
     * Get a Pub/Sub topic.
     *
     * @param string $topicName  The Pub/Sub topic name.
     * @throws \Exception
     * @return Topic
     */
    public function getTopic(string $topicName): Topic
    {
        $topic = $this->client->topic($this->getTopicName($topicName));

        if (!$topic->exists()) {

            if ($this->getAutoCreateTopic()) {

                $topic = $this->createTopic($topicName);
            } else {
                throw new \Exception('Unexisting topic');
            }
        }

        return $topic;
    }

    /**
     * Delete a Pub/Sub topic.
     *
     * @param string $topicName  The Pub/Sub topic name.
     * @return Topic
     */
    public function deleteTopic(string $topicName): Topic
    {
        $topic = $this->getTopic($topicName);
        $topic->delete();

        return $topic;
    }

    /**
     * Update a topic.
     * Note that the topic's name and kms key name are immutable properties
     * and may not be modified
     *
     * @param string $topicName  The Pub/Sub topic name.
     * @param array $properties  The Pub/Sub topic properties.
     * @return Topic
     */
    public function updateTopic(string $topicName, array $properties): Topic
    {
        $topic = $this->getTopic($topicName);
        $topic->update($properties);

        return $topic;
    }

    /**
     * Get all Pub/Sub topics.
     *
     * @return array
     */
    public function getTopics(): array
    {
        $topics = [];

        foreach ($this->client->topics() as $topic) {
            $topics[] = $topic;
        }

        return $topics;
    }

    /**
     * Delete all Pub/Sub topics.
     *
     * @return array
     */
    public function deleteTopics(): array
    {
        $topicRemoved = [];
        $topics = $this->getTopics();

        foreach ($topics as $topic) {
            $topic->delete();
            $topicRemoved[] = $topic->name();
        }

        return $topicRemoved;
    }

    /**
     * Create a Pub/Sub subscription.
     *
     * @param string $subscriptionName  The Pub/Sub subscription name.
     * @param string $topicName  The Pub/Sub topic name.
     * @return Subscription
     */
    public function createSubscription(string $subscriptionName, string $topicName = ''): Subscription
    {
        if (!$topicName) {

            if ($this->autoCreateTopicFromSubscription) {
                $topicName = $subscriptionName;
            } else {
                throw new \Exception('Empty topic name');
            }
        }

        $subscription = $this->getTopic($topicName)->subscription($this->getSubscriptionName($subscriptionName));
        $subscription->create();

        return $subscription;
    }

    /**
     * Create a Pub/Sub subscription.
     *
     * @param string $subscriptionName  The Pub/Sub subscription name.
     * @param string|null $topicName  The Pub/Sub subscription name.
     * @throws \Exception
     * @return Subscription
     */
    public function getSubscription(string $subscriptionName, string $topicName = ''): Subscription
    {
        $subscription = $this->client->subscription($this->getSubscriptionName($subscriptionName));

        if (!$subscription->exists()) {

            if ($this->getAutoCreateSubscription()) {

                $subscription = $this->createSubscription($subscriptionName, $topicName);
            } else {
                throw new \Exception('Unexisting subscription');
            }
        }

        return $subscription;
    }

    /**
     * Delete a Pub/Sub subscription.
     *
     * @param string $subscriptionName  The Pub/Sub subscription name.
     * @return Subscription
     */
    public function deleteSubscription(string $subscriptionName): Subscription
    {
        $subscription = $this->getSubscription($subscriptionName);
        $subscription->delete();

        return $subscription;
    }

    /**
     * Update a subscription.
     * Note that subscription name and topic are immutable properties
     * and may not be modified.
     *
     * @param string $subscriptionName  The Pub/Sub subscription name.
     * @param array $properties  The Pub/Sub subscription properties.
     * @return Subscription
     */
    public function updateSubscription(string $subscriptionName, array $properties): Subscription
    {
        $subscription = $this->getsubscription($subscriptionName);
        $subscription->update($properties);

        return $subscription;
    }

    /**
     * Get all Pub/Sub subscriptions.
     *
     * @return array
     */
    public function getSubscriptions(): array
    {
        $subscriptions = [];

        foreach ($this->client->subscriptions() as $subscription) {
            $subscriptions[] = $subscription;
            //$subscriptions[] = $subscription->__debugInfo();
        }

        return $subscriptions;
    }

    /**
     * Publish a message for a Pub/Sub topic.
     *
     * @param string $topicName  The Pub/Sub topic name.
     * @param mixed $datas  The data to publish.
     * @param array $attributes  The message attributes.
     * @return array
     */
    public function publish(string $topicName, $datas, array $attributes = []): array
    {
        if (is_object($datas) || is_array($datas)) {
            $datas = json_encode($datas);
        }

        $message = [
            'data' => $datas,
        ];

        if ($attributes) {
            $message['attributes'] = $this->validateMessageAttributes($attributes);
        }

        $response = $this->getTopic($topicName)->publish($message);

        return $response;
    }

    /**
     * Publish a message for a Pub/Sub topic.
     *
     * The publisher should be used in conjunction with the `google-cloud-batch`
     * daemon, which should be running in the background.
     *
     * To start the daemon, from your project root call `vendor/bin/google-cloud-batch daemon`.
     *
     * @param string $topicName  The Pub/Sub topic name.
     * @param array $messages    The messages to publish.
     */
    public function publishBatch(string $topicName, array $messages)
    {
        // Check if the batch daemon is running.
        if (getenv('IS_BATCH_DAEMON_RUNNING') !== 'true') {
            trigger_error(
                'The batch daemon is not running. Call ' .
                '`vendor/bin/google-cloud-batch daemon` from ' .
                'your project root to start the daemon.',
                E_USER_NOTICE
            );
        }

        $batchOptions = [
            'batchSize' => $this->getBatchSize(), // Max messages for each batch.
            'callPeriod' => $this->getCallPeriod(), // Max time in seconds between each batch publish.
        ];

        $messages = array_map(function ($message) {
            return ['data' => json_encode($message)];
        }, $messages);

        $publisher = $this->getTopic($topicName)->batchPublisher([
            'batchOptions' => $batchOptions,
        ]);

        $publisher->publish($messages);
    }

    /**
     * Consume topic message(s) once.
     *
     * @param string $subscriptionName  The Pub/Sub subscription name to create if not exists.
     * @param string $topicName  The Pub/Sub topic name.
     * @return Message[]
     */
    public function consume(string $subscriptionName, string $topicName = ''): array
    {
        $messages = [];
        $subscription = $this->getSubscription($subscriptionName, $topicName);

        $options = [
            'maxMessages' => $this->maxMessages,
            'returnImmediately' => true,
        ];

        if ($this->debug) {
            echo $subscriptionName . ' : ' . date('Y-m-d H:i:s u') . "\n";
        }

        $pullMessages = $subscription->pull($options);

        foreach ($pullMessages as $pullMessage) {

            $this->debugInfo($topicName, $subscriptionName, $pullMessage);

            $availableAt = $pullMessage->attribute('availableAt');

            if ($availableAt && $availableAt > time()) {
                continue;
            }

            $messages[] = $pullMessage;

            // Acknowledge the Pub/Sub message has been received, so it will not be pulled multiple times.
            $subscription->acknowledge($pullMessage);
        }

        return $messages;
    }

    /**
     * Display message info.
     *
     * @param string $topicName  The Pub/Sub topic name.
     * @param string $subscriptionName  The Pub/Sub subscription name.
     * @param Message $message  The Pub/Sub message.
     * @return void
     */
    public function debugInfo(string $topicName, string $subscriptionName, Message $message): void
    {
        if ($this->debug) {

            $publishTime = $message->publishTime();
            $publishTime = $publishTime->format('Y-m-d H:i:s');

            echo "\n";
            echo '========================== RECEIVING ==========================' . "\n";
            echo '   ' . 'TOPIC ' . "\n";
            echo '   ' . '   ' . 'NAME : ' . $this->getTopicName($topicName) . "\n\n";
            echo '   ' . 'SUBSCRIBER ' . "\n";
            echo '   ' . '   ' . 'NAME : ' . $this->getSubscriptionName($subscriptionName) . "\n\n";
            echo '   ' . 'MESSAGE ' . "\n";
            echo '   ' . '   ' . 'ID : ' . $message->id() . "\n";
            echo '   ' . '   ' . 'DATAS : ' . $message->data() . "\n";
            echo '   ' . '   ' . 'ATTRIBUTES : ' . json_encode($message->attributes()) . "\n";
            echo '   ' . '   ' . 'PUBLISH TIME : ' . $publishTime . "\n";
            echo '   ' . '   ' . 'DELIVERY ATTEMPT : ' . $message->deliveryAttempt() . "\n";
            echo '   ' . '   ' . 'ORDERING KEY : ' . $message->orderingKey() . "\n";
            echo '   ' . '   ' . 'ACK Id : ' . $message->ackId() . "\n";
            echo '===============================================================';
            echo "\n";
        }
    }

    /**
     * Subscribe a handler to a topic.
     *
     * @param string $subscriptionName
     * @param callable $handler
     * @param string $topicName
     * @param bool $infiniteLoop
     */
    public function subscribe(string $subscriptionName, callable $handler, string $topicName = '', $infiniteLoop = true)
    {
        $subscription = $this->getSubscription($subscriptionName, $topicName);
        $isDelayed = $this->delay;

        $options = [
            'maxMessages' => $this->maxMessages,
            'returnImmediately' => $this->returnImmediately,
        ];

        do {
            if ($this->debug) {
                echo $subscriptionName . ' : ' . date('Y-m-d H:i:s u') . "\n";
            }

            $pullMessages = $subscription->pull($options);

            foreach ($pullMessages as $pullMessage) {

                $this->debugInfo($topicName, $subscriptionName, $pullMessage);

                $availableAt = $pullMessage->attribute('availableAt');

                if ($availableAt && $availableAt > time()) {
                    continue;
                }

                // Acknowledge the Pub/Sub message has been received, so it will not be pulled multiple times.
                $subscription->acknowledge($pullMessage);

                $payload = json_decode($pullMessage->data(), true);

                call_user_func($handler, $payload);
            }

            if ($infiniteLoop && $this->returnImmediately && $isDelayed) {
                sleep($isDelayed);
            }

        } while ($infiniteLoop);
    }

    /**
     * Check if the message attributes array only contains strings key-values
     *
     * @param  array $attributes
     *
     * @throws \UnexpectedValueException
     * @return array
     */
    public function validateMessageAttributes(array $attributes): array
    {
        $attributes_values = array_filter($attributes, 'is_string');

        if (count($attributes_values) !== count($attributes)) {
            throw new \UnexpectedValueException('PubSubMessage attributes only accept key-value string pairs');
        }

        $attributes_keys = array_filter(array_keys($attributes), 'is_string');

        if (count($attributes_keys) !== count(array_keys($attributes))) {
            throw new \UnexpectedValueException('PubSubMessage attributes only accept key-value string pairs');
        }

        return $attributes;
    }

    /**
     * Return the Google PubSubClient instance.
     *
     * @return PubSubClient
     */
    public function getClient(): PubSubClient
    {
        return $this->client;
    }

    /**
     * Return the delay number.
     *
     * @return int
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * Set the delay number in seconds.
     *
     * @param int  $delay
     * @return GcPubSub
     */
    public function setDelay(int $delay): GcPubSub
    {
        $this->delay = $delay;
        return $this;
    }

    /**
     * Return the batch size.
     *
     * @return int
     */
    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    /**
     * Set the batch size.
     *
     * @param int  $batchSize
     * @return GcPubSub
     */
    public function setBatchSize(int $batchSize): GcPubSub
    {
        $this->batchSize = $batchSize;
        return $this;
    }

    /**
     * Return the call period.
     *
     * @return int
     */
    public function getCallPeriod(): int
    {
        return $this->callPeriod;
    }

    /**
     * Set the call period.
     *
     * @param float  $callPeriod
     * @return GcPubSub
     */
    public function setCallPeriod(float $callPeriod): GcPubSub
    {
        $this->callPeriod = $callPeriod;
        return $this;
    }

    /**
     * Return the return immediately pull option.
     *
     * @return bool
     */
    public function getReturnImmediately(): bool
    {
        return $this->returnImmediately;
    }

    /**
     * Set the return immediately pull option.
     *
     * @param bool  $returnImmediately
     * @return GcPubSub
     */
    public function setReturnImmediately(bool $returnImmediately): GcPubSub
    {
        $this->returnImmediately = $returnImmediately;
        return $this;
    }

    /**
     * Return the debug mode.
     *
     * @return bool
     */
    public function getDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Set the debug mode.
     *
     * @param bool  $debug
     * @return GcPubSub
     */
    public function setDebug(bool $debug): GcPubSub
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Return the max messages pull option.
     *
     * @return int
     */
    public function getMaxMessages(): int
    {
        return $this->maxMessages;
    }

    /**
     * Set the max messages pull option.
     *
     * @param int  $maxMessages
     * @return GcPubSub
     */
    public function setMaxMessages(int $maxMessages): GcPubSub
    {
        $this->maxMessages = $maxMessages;
        return $this;
    }

    /**
     * Return the auto create topic.
     *
     * @return bool
     */
    public function getAutoCreateTopic(): bool
    {
        return $this->autoCreateTopic;
    }

    /**
     * Set the auto create topic pull.
     *
     * @param bool  $autoCreateTopic
     * @return GcPubSub
     */
    public function setAutoCreateTopic(bool $autoCreateTopic): GcPubSub
    {
        $this->autoCreateTopic = $autoCreateTopic;
        return $this;
    }

    /**
     * Return the auto create subscription.
     *
     * @return bool
     */
    public function getAutoCreateSubscription(): bool
    {
        return $this->autoCreateSubscription;
    }

    /**
     * Set the auto create subscription.
     *
     * @param bool  $autoCreateSubscription
     * @return GcPubSub
     */
    public function setAutoCreateSubscription(bool $autoCreateSubscription): GcPubSub
    {
        $this->autoCreateSubscription = $autoCreateSubscription;
        return $this;
    }

    /**
     * Return the auto create subscription.
     *
     * @return bool
     */
    public function getAutoCreateTopicFromSubscription(): bool
    {
        return $this->autoCreateTopicFromSubscription;
    }

    /**
     * Set the auto create topic from subscription.
     *
     * @param bool  $autoCreateSubscription
     * @return GcPubSub
     */
    public function setAutoCreateTopicFromSubscription(bool $autoCreateTopicFromSubscription): GcPubSub
    {
        $this->autoCreateTopicFromSubscription = $autoCreateTopicFromSubscription;
        return $this;
    }

    /**
     * Return the topic suffix.
     *
     * @param string  $topicSuffix
     * @return string
     */
    public function getTopicSuffix(): string
    {
        return $this->topicSuffix;
    }

    /**
     * Set the topic suffix.
     *
     * @return GcPubSub
     */
    public function setTopicSuffix(string $topicSuffix): GcPubSub
    {
        $this->topicSuffix = $topicSuffix;
        return $this;
    }

    /**
     * Return the subscription suffix.
     *
     * @return string
     */
    public function getSubscriptionSuffix(): string
    {
        return $this->subscriptionSuffix;
    }

    /**
     * Set the subscription suffix.
     *
     * @param string  $subscriptionSuffix
     * @return GcPubSub
     */
    public function setSubscriptionSuffix(string $subscriptionSuffix): GcPubSub
    {
        $this->subscriptionSuffix = $subscriptionSuffix;
        return $this;
    }

    /**
     * Return the full topic name.
     *
     * @param string  $topicName
     * @return string
     */
    public function getTopicName(string $topicName): string
    {
        return $this->topicSuffix . $topicName;
    }

    /**
     * Return the full subscription name.
     *
     * @param string  $subscriptionName
     * @return string
     */
    public function getSubscriptionName(string $subscriptionName): string
    {
        return $this->subscriptionSuffix . $subscriptionName;
    }
}
