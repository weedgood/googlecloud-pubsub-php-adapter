# googlecloud-pubsub-php-adapter

A Php class adapter for the [Goolge Cloud PubSub](https://github.com/googleapis/google-cloud-php-pubsub) package.

![CI](https://github.com/twipi-group/googlecloud-pubsub-php-adapter/workflows/CI/badge.svg)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.1-8892BF.svg?style=flat)](https://www.php.net/manual/fr/migration71.new-features.php)
[![Software License](https://img.shields.io/badge/license-MIT-green.svg?style=flat)](LICENSE)

## Installation

```bash
composer require twipi-group/googlecloud-pubsub-php-adapter
```

## Samples
### Publish
```php
use Google\Cloud\PubSub\PubSubClient;
use TwipiGroup\GoogleCloudPubSubPhpAdapter\GcPubSub;

$client = new PubSubClient([
    'projectId' => 'your-googlecloud-project-id',
]);

$pubsub = new GcPubSub($client);

$pubsub->publish('mychannel', 'test first');
$pubsub->publish('mychannel', json_encode(['test' => 'another']));
$pubsub->publish('mytopic', [
    'test' => [0, 1, 2]
]);
```
### Consume
```php
use Google\Cloud\PubSub\PubSubClient;
use TwipiGroup\GoogleCloudPubSubPhpAdapter\GcPubSub;

$client = new PubSubClient([
    'projectId' => 'your-googlecloud-project-id',
]);

$pubsub = new GcPubSub($client);

// case blocking call
$pubsub->subscribe('mychannel', function ($message) {
    var_dump($message);
});
// OR
$pubsub->subscribe('mysubscriber', function ($message) {
    var_dump($message);
}, 'mytopic');

// case non blocking call
$messages = $pubsub->consume('mychannel');
// OR
$messages = $pubsub->consume('mysubscriber', 'mytopic');

foreach ($messages as $message) {
    var_dump($message);
}
```
## Functions
### Uses
`function createTopic(string $topicName): Topic`

This function creates a topic from **$topicName** and returns topic object.


`function getTopic(string $topicName): Topic`

This function returns an existing topic object from **$topicName** or create a new topic if **setAutoCreateTopic** is *true*.
If **setAutoCreateTopic** is *false*, an exception will be thrown.


`function deleteTopic(string $topicName): Topic`

This function deletes an existing topic from **$topicName** and returns topic object.


`function updateTopic($topicName, $properties): Topic`

This function updates an existing topic from **$topicName** and returns topic object.
Note that the topic's name and kms key name are immutable properties and may not be modified.
https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.131.0/pubsub/topic


`function getTopics(): array`

This function returns an array of existing topics objects.


`function deleteTopics(): array`

This function deletes all existing topics and returns array of topics name deleted.


`function createSubscription(string $subscriptionName, string $topicName = ''): Subscription`

This function creates a topic from **$subscriptionName** and returns subscription object.
If **setAutoCreateTopicFromSubscription** and **setAutoCreateTopic** are *true* and **$topicName** is *empty*, so a topic will be created from **$subscriptionName**.


`function getSubscription(string $subscriptionName, string $topicName = ''): Subscription`

This function returns an existing subscription object from **$subscriptionName** or create a new subscription if **setAutoCreateSubscription** is *true*.
If **setAutoCreateSubscription** is *false*, an exception will be thrown.
If **setAutoCreateTopicFromSubscription** and **setAutoCreateTopic** are *true* and **$topicName** is *empty*, so a topic will be created from **$subscriptionName**.
If **setAutoCreateTopicFromSubscription** or **setAutoCreateTopic** is *false* and **$topicName** is *empty*, an exception will be thrown.


`function deleteSubscription($subscriptionName): Subscription`

This function deletes an existing subscription from **$subscriptionName** and returns subscription object.


`function updateSubscription($subscriptionName, $properties): Subscription`

This function updates an existing subscription from **$subscriptionName** and returns subscription object.
Note that subscription name and topic are immutable properties and may not be modified.
https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.131.0/pubsub/subscription


`function getSubscriptions(): array`

This function returns an array of existing subscriptions objects.


`function consume(string $subscriptionName, string $topicName = ''): array`

This function always return immediately after one pull. 
You just can change setMaxMessages for receiving x messages from this pull.
If **setAutoCreateTopic** and **setAutoCreateTopicFromSubscription** are *true*, and if **$topicName** is *empty*, it will auto create topic from subscription name if topic not already exists.
This function returns an array of messages.


`function subscribe(string $subscriptionName, callable $handler, string $topicName = '', $infiniteLoop = true): void`

This function is an infinite loop by default thanks to *$infiniteLoop = true*.
* if **$infiniteLoop** is *true* :
    * if **setReturnImmediately** is *true* and **setDelay** > 1 second, the loop will sleep every one second between each pull
    * if **setReturnImmediately** is *true* and **setDelay** = 0 second, the loop will work everytime, this __**IS NOT  RECOMMENDED**__ because it adversely impacts the performance of the system.
    * if **setReturnImmediately** is *false*, (**RECOMMENDED**) the system waits (~90 seconds) until at least one message is available, and **setDelay** has no effect.
    

* if **$infiniteLoop** is *false* :
    * **setReturnImmediately** and **setDelay** has no impact because just one loop will be executed with only one pull.


#### Settings
```php
// set max messages number consuming by pull
$pubsub->setMaxMessages(2); // 100 by default

// set if return immediately after pull
$pubsub->setReturnImmediately(true); // false by default

// set delay in seconds to wait between each pull
// note: if ReturnImmediately is false, this option has no effect.
$pubsub->setDelay(2); // 10 by default

// set a suffix for subscriptions
$pubsub->setTopicSuffix('mysubsriber_'); // empty by default

// set debug mode to display pull messages directly to output
$pubsub->setDebug(true); // false by default

// set if auto create topic when not existing
$pubsub->setAutoCreateTopic(false); // true by default

// set if auto create subscription when not existing
$pubsub->setAutoCreateSubscription(false); // true by default

// set if auto create topic from subscription name when consume messages with empty topic name
$pubsub->setAutoCreateTopicFromSubscription(false); // true by default
```

## Tests
```
vendor/bin/phpunit Tests
```
## Contribution
You can contribute to this package by discovering bugs, opening issues or purpose new features.

## Licence
This project is licensed under the terms of the MIT license. See License file for more information.