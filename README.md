# T-HUB Service

![Atandra](http://www.atandra.com/images/logo.png)

## Overview

An HTTP-service-style application for integrating a database-driven online store with the T-HUB desktop application. The included `index.php` file can serve as an independent web-facing script.

Currently the implementation is tied to a specific database structure. Maybe in the future it will become more configurable. Maybe.

**NOTE: This service implements only the first two levels of integration: `getOrders` and `updateOrdersShippingStatus` as decribed in the [T-HUB Service Spec](http://www.atandra.com/downloads/THUB_Service_Spec_43.pdf)**

## Setup

Clone the repo:

    $ git clone git@bitbucket.org:acobster/t-hub-service.git path/to/public/service/dir

Configure the service by adding a file called `tweb_config.php` in the public service dir (it should be in the same directory as `index.php`):

    <?php

    define( 'DB_NAME', 'your_db' );
    define( 'DB_USER', 'user' );
    define( 'DB_PASSWORD', 'password' );
    define( 'DB_HOST', 'localhost' );
    define( 'DB_SOCKET', '/tmp/mysql.sock' ); // optional
    
    // Using default values is discouraged in most cases.
    // See docblock for THubService::config()
    THub\THubService::config(array(
    	'viewDir'		=> '/some/dir/',
    	'user'			=> 'thub-user',
    	'password'		=> 'pw',
    	'securityKey'	=> 'some-long-string',
    	'requireKey'	=> false // defaults to true
    ));

    ?>

## Test it out!

Using a tool like Postman, POST a valid request per the service spec and check that the returned XML is as expected.

## Development

Grab the code, create a local config file, create the MySQL schema, then run:

    $ npm install
    $ grunt

Grunt is now watching for file changes.