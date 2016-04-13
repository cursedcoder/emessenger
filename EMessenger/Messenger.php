<?php

namespace EMessenger;

use UniversalSerializer\UniversalSerializerTrait;
use Evenement\EventEmitterTrait;

class Messenger
{
    use EventEmitterTrait;
    use UniversalSerializerTrait;

    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;

        $this->connection->on('message', function($message) {
            $data = json_decode($message, true);
            $message = new Message($data['event'], $this->unserialize(base64_decode($data['content'])));

            $this->emit('message', [$message]);
            $this->emit($message->getEvent(), [$message->getContent()]);
        });

        $this->connection->on('close', [$this, 'close']);

        if (method_exists($this->connection, 'listen')) {
            $this->connection->listen();
        } elseif (method_exists($this->connection, 'connect')) {
            $this->connection->connect();
        } else {
            throw new \InvalidArgumentException('Connection does not implement "listen" or "connect" methods');
        }
    }

    public function send($event, $data = [])
    {
        $this->connection->send((string) new Message($event, $this->serialize($data)));
    }

    public function close()
    {
        $this->emit('close');
    }
}
