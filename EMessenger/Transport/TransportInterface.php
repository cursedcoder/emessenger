<?php

namespace EMessenger\Transport;

interface TransportInterface
{
    public function createServer();
    public function createClient();
}
