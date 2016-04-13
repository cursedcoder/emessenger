EMessenger
==========

Send any messages across remote processes.

Available transports
====================

* Unix socket files (unix://tmp/log.sock)
* More later: tcp, udp etc.

Examples
========

```php
<?php

// logserver.php

use React\EventLoop\Factory as EventLoopFactory;
use EMessenger\Transport\UnixTransport;
use EMessenger\MessengerFactory;

$loop = EventLoopFactory::create();

$transport = new UnixTransport($loop, 'unix://tmp/log.sock');
$messenger = MessengerFactory::server($transport);

$messenger->send('debug', 'This is a test message.');

```

```php
<?php

// logwriter.php

use React\EventLoop\Factory as EventLoopFactory;
use EMessenger\Transport\UnixTransport;
use EMessenger\MessengerFactory;

$loop = EventLoopFactory::create();

$transport = new UnixTransport($loop, 'unix://tmp/log.sock');
$messenger = MessengerFactory::client($transport);

$messenger->subscribe('debug', function($debug) {
    echo 'Received debug message: ' . $debug;
});

```
