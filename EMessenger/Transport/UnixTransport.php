<?php

namespace EMessenger\Transport;

use Concerto\Comms\Client;
use Concerto\Comms\Server;
use React\EventLoop\LoopInterface;

class UnixTransport implements TransportInterface
{
    private $loop;
    private $address;

    public function __construct(LoopInterface $loop, $address)
    {
        $this->loop = $loop;
        $this->address = $address;
    }

    public function createServer()
    {
        return new Server($this->loop, $this->address);
    }

    public function createClient()
    {
        return new Client($this->loop, $this->address);
    }
}
