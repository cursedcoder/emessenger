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

        $this->transport_works_simulatenously($loop, $transport);
    }

    private function transport_works_simulatenously(LoopInterface $loop, TransportInterface $transport)
    {
        $server = MessengerFactory::server($transport);
        $client = MessengerFactory::client($transport);

        $this->assertInstanceOf(Messenger::class, $server);
        $this->assertInstanceOf(Messenger::class, $client);

        $tester = new Tester();
        $tester->messages[] = new TestMessage('event1', 'args1');
        $tester->messages[] = new TestMessage('event2', 'args123');
        $tester->initialize($server, $client, $loop);
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

class Tester extends \PHPUnit_Framework_TestCase
{
    public $messages = [];
    public $totalCalled = 0;

    public function initialize(Messenger $server, Messenger $client, LoopInterface $loop)
    {
        foreach ($this->messages as $message) {
            $client->on($message->event, function($arg) use ($message, $loop) {
                $this->assertEquals($arg, $message->arguments);
                $message->called = true;
                $this->totalCalled++;

                if ($this->totalCalled === count($this->messages)) {
                    $loop->stop();
                }
            });

            $loop->addTimer(0.001, function() use ($server, $message) {
                $server->send($message->event, $message->arguments);
            });
        }

        $loop->addTimer(3, function() use ($loop) {
            $loop->stop();

            throw new \RuntimeException('Timeout reached, server has not accomplished all messages.');
        });

        $loop->run();

        foreach ($this->messages as $message) {
            $this->assertTrue($message->called);
        }
    }
}

class TestMessage
{
    public $event;
    public $arguments;
    public $called = false;

    public function __construct($event, $arguments)
    {
        $this->event = $event;
        $this->arguments = $arguments;
    }
}
