<?php

namespace Netom\Session;

class Mongodb implements \SessionHandlerInterface {

    public
        $manager,
        $dbName,
        $collectionName;

    public static function register($uri, $collectionName, array $uriOptions = [], array $driverOptions = [])
    {
        $h = new self($uri, $collectionName, $uriOptions, $driverOptions);
        session_set_save_handler($h, true);
    }

    public function __construct($uri, $collectionName, array $uriOptions = [], array $driverOptions = [])
    {
        $this->manager = new \MongoDB\Driver\Manager($uri, $uriOptions, $driverOptions);
        $this->collectionName = $collectionName;
    }

    public function getManager()
    {
        return $this->manager;
    }

    public function close()
    {
        return true;
    }

    public function destroy($session_id)
    {
        return true;
    }

    public function gc($maxlifetime) //int
    {
        return true;
    }

    public function open($save_path, $session_name)
    {
        return true;
    }

    public function read($session_id)
    {
        return "";
    }

    public function write($session_id, $session_data)
    {
        return true;
    }
}

// $m = Mongodb::register('mongodb://127.0.0.1/sessiontest', 'session');
