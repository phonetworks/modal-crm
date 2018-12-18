<?php

namespace Pho\Crm\Session;

use Predis\Client;
use Predis\Session\Handler;

class SessionManager
{
    public function run()
    {
        $sessionStorage = config('app.session_storage');
        switch ($sessionStorage) {

            case 'redis':
                $this->getRedisSessionHandler()->register();
                break;

            case 'file':
                break;

            default:
                throw new \Exception('Invalid session storage');
        }
        session_start();
    }

    /**
     * @return \Predis\Session\Handler
     */
    public function getRedisSessionHandler()
    {
        $single_server = config('redis.url') ?: array(
            'host' => config('redis.host'),
            'port' => config('redis.port'),
        );
        $client = new Client($single_server, array('prefix' => 'sessions:'));
        $handler = new Handler($client, array('gc_maxlifetime' => 5000));

        return $handler;
    }
}
