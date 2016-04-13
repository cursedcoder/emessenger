<?php

namespace Tests\EMessenger;

use EMessenger\Messenger;
use EMessenger\MessengerFactory;
use EMessenger\Transport\TransportInterface;
use EMessenger\Transport\UnixTransport;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;

class MessengerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_works_with_unix_transport()
    {
        $loop = LoopFactory::create();
        $transport = new UnixTransport($loop, 'unix://' . $this->getUnixPath());

        $this->transport_works($loop, $transport);
    }

    private function transport_works(LoopInterface $loop, TransportInterface $transport)
    {
        $server = MessengerFactory::server($transport);
        $client = MessengerFactory::client($transport);

        $this->assertInstanceOf(Messenger::class, $server);
        $this->assertInstanceOf(Messenger::class, $client);

        $event = 'test';
        $eventArg = 'test arg';
        $isCalled = false;

        $client->on($event, function($arg) use ($eventArg, &$isCalled, $loop) {
            $this->assertEquals($arg, $eventArg);
            $isCalled = true;
            $loop->stop();
        });

        $loop->addTimer(0.001, function() use ($server, $event, $eventArg) {
            $server->send($event, $eventArg);
        });

        $loop->addTimer(3, function() use ($loop) {
            $loop->stop();

            throw new \RuntimeException('Timeout reached, server did not run.');
        });

        $loop->run();

        $this->assertTrue($isCalled);
    }

    public function tearDown()
    {
        if (file_exists($this->getUnixPath())) {
            unlink($this->getUnixPath());
        }
    }

    public function getUnixPath()
    {
        return realpath(__DIR__ . '/../../') . '/tmp/test.sock';
    }
}
