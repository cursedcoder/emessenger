<?php

namespace EMessenger;

use EMessenger\Transport\TransportInterface;

class MessengerFactory
{
    public static function server(TransportInterface $transport)
    {
        return new Messenger($transport->createServer());
    }

    public static function client(TransportInterface $transport)
    {
        return new Messenger($transport->createClient());
    }
}
