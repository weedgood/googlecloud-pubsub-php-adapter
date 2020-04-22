# googlecloud-pubsub-php-adapter

A Php class adapter for the [Goolge Cloud PubSub](https://github.com/googleapis/google-cloud-php-pubsub) package.

[![Author](http://img.shields.io/badge/author-@missill-blue.svg?style=flat-square)](https://github.com/missill)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.0-8892BF.svg?style=flat-square)](https://www.php.net/manual/fr/migration71.new-features.php)


## Installation

```bash
composer require twipi-group/googlecloud-pubsub-php-adapter
```

## Samples
#### Publish
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
#### Consume
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
`function consume(string $subscriptionName, string $topicName = '')`
This function always return immediately after one pull. 
You just can change setMaxMessages for consuming x messages from this pull.
If **setAutoCreateTopic** and **setAutoCreateTopicFromSubscription** are *true*, and if **$topicName** is *empty*, it will auto create topic from subscription name if topic not already exists.

`function subscribe(string $subscriptionName, callable $handler, string $topicName = '', $infiniteLoop = true)`
This function is an infinite loop by default thanks to *$infiniteLoop = true*.
* if **$infiniteLoop** is *true* :
    * if **setReturnImmediately** is *true* and **setDelay** > 1 second, the loop will sleep every one second between each pull - (**RECOMMENDED**)
    * if **setReturnImmediately** is *true* and **setDelay** = 0 second, the loop will work everytime, this __**IS NOT  RECOMMENDED**__ because it adversely impacts the performance of the system.
    * if **setReturnImmediately** is *false*, the system wait (~90 seconds) until at least one message is available, and **setDelay** will just add seconds to this existing delay.
    

* if **$infiniteLoop** is *false* :
    * **setReturnImmediately** and **setDelay** has no impact because just one loop will be executed with only one pull.

#### Settings
```php
// set max messages number consuming by pull
$pubsub->setMaxMessages(2); // 100 by default

// set if return immediately after pull
$pubsub->setReturnImmediately(false); // true by default

// set delay in seconds to wait between each pull
// note: if ReturnImmediately is false, this option has no interest because serverside pubsub timeout occurs every ~90sec
$pubsub->setDelay(2); // 10 by default

// set a suffix for subscriptions
$pubsub->setTopicSuffix('mysubsriber_'); // empty by default

// set debug mode to display subscriber and pull messages directly to output
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
vendor/bin/phpunit
```
## Contribution
You can contribute to this package by discovering bugs and opening issues.

## Licence
This project is licensed under the terms of the MIT license. See License file for more information.