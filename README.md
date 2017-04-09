# Php-mongodb-session - a tiny, lockless mongodb session backend

## Usage

Use composer to include 'netom/php-mongodb-session'.

Use the code below to register a mongodb session handler instance, and
get the actual handler instance in a signle line:

        $h = \Netom\Session\MongoDB::register();

See the code for parameters.

The default is to connect to the localhost, and to the 'session'
database, using the 'session' collection.

Put an index to the 't' field to make garbage collection faster.

