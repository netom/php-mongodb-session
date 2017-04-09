<?php

namespace Netom\Session;

class MongoDB implements \SessionHandlerInterface {

    public
        $manager,
        $dbName,
        $collectionName;

    public static function register(
        $uri = 'mongodb://127.0.0.1:27017/session',
        $dbName = 'session',
        $collectionName = 'session',
        array $uriOptions = [
            'journal' => false,
            'readConcernLevel' => 'local',
            'readPreference' => 'secondaryPreferred',
            'w' => 1
        ],
        array $driverOptions = []
    )
    {
        $h = new self($uri, $dbName, $collectionName, $uriOptions, $driverOptions);
        session_set_save_handler($h, true);
        return $h;
    }

    public function __construct($uri, $dbName, $collectionName, array $uriOptions = [], array $driverOptions = [])
    {
        $this->manager = new \MongoDB\Driver\Manager($uri, $uriOptions, $driverOptions);
        $this->dbName = $dbName;
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
        $this->manager->executeCommand(
            $this->dbName,
            new \MongoDB\Driver\Command([
                'delete' => $this->collectionName,
                'deletes' => [
                    [
                        'q' => ['_id' => $session_id],
                        'limit' => 0
                    ]
                ]
            ])
        );
        return true;
    }

    public function gc($maxlifetime) //int
    {
        $this->manager->executeCommand(
            $this->dbName,
            new \MongoDB\Driver\Command([
                'delete' => $this->collectionName,
                'deletes' => [
                    [
                        'q' => ['t' => ['$lt' => time() - $maxlifetime]],
                        'limit' => 0
                    ]
                ]
            ])
        );
        return true;
    }

    public function open($save_path, $session_name)
    {
        return true;
    }

    public function read($session_id)
    {
        $rows = $this->manager->executeQuery(
            $this->dbName . '.' . $this->collectionName,
            new \MongoDB\Driver\Query(
                [
                    '_id' => $session_id
                ],
                [
                    'projection' => [
                        '_id' => 0,
                        'd' => 1
                    ],
                    'limit' => 1
                ]
            )
        );
        $rows->setTypeMap(['root' => 'array']);

        $a = $rows->toArray();

        if (($c = count($a)) > 1) {
            throw new \Exception('MongoDB returned multiple row for session id ' . $session_id);
        }
        if ($c == 0) {
            return '';
        }

        return $a[0]['d'];
    }

    public function write($session_id, $session_data)
    {
        $bw = new \MongoDB\Driver\BulkWrite();
        $bw->update(
            ['_id' => $session_id],
            ['$set' => ['d' => $session_data, 't' => time()]],
            ['upsert' => true]
        );

        // This can throw exceptions.
        $this->manager->executeBulkWrite($this->dbName . '.' . $this->collectionName, $bw);

        return true;
    }
}

/*

// Quick & dirty test

$h = Mongodb::register();

session_id('asdf123');
session_start();

print session_id() . "\n";

var_dump($_SESSION);

$_SESSION['key'] = "value";

var_dump($_SESSION);

//session_destroy();

session_write_close();

//$h->gc(-1);

*/
