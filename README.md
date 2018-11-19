# T-HUB Service

![Atandra](http://www.atandra.com/images/logo.png)

[![Build Status](https://travis-ci.org/acobster/t-hub-service.svg?branch=master)](https://travis-ci.org/acobster/t-hub-service)

## Overview

An HTTP-service-style application for integrating a database-driven online store with the T-HUB desktop application. The included `index.php` file can serve as an independent web-facing script.

Currently the implementation is tied to a specific database structure. Maybe in the future it will become more configurable. Maybe.

**NOTE: This service implements only the first two levels of integration: `getOrders` and `updateOrdersShippingStatus` as decribed in the [T-HUB Service Spec](http://www.atandra.com/downloads/THUB_Service_Spec_43.pdf)**

## Setup

Clone the repo:

    $ git clone git@github.com:acobster/t-hub-service.git path/to/public/service/dir

Configure the service by adding a file called `thub_config.php` in the public service dir (it should be in the same directory as `index.php`):

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
    	'passwordFile'	=> '/path/to/thub.passwd',
    	'securityKey'	=> 'some-long-string',
    	'requireKey'	=> false // defaults to true
    ));
    
    ?>

To generate the password file, run:

```
php -r "echo password_hash('<your desired password>', PASSWORD_DEFAULT);" > /path/to/thub.passwd
```

Note that the `/path/to/thub.passwd` file must match the `passwordFile` path declared in the config.

## Test it out!

The code comes with a [Postman](https://www.getpostman.com/apps) collection for various operations against the service endpoint. Import `test/integration/THUB.postman_collection.json` into Postman to get started, and take one of the requests for a spin. **Careful! UpdateOrderShippingStatus has side-effects!**

## Development

To start development, you must have [Lando](https://docs.devwithlando.io) installed. After cloning, just run:

    lando start

inside the repo root.

### Running Tests

There are unit and integration tests, all of which run inside the Lando environment:

```
lando unit # runs unit tests
lando e2e  # runs integration tests
lando test # runs entire test suite
```

### The Lando Postman environment

The code comes with a Postman environment for testing with the collection mentioned above. Import it from `test/integration/lando.postman_environment.json`.