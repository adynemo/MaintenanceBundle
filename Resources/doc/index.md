Installation
============

## Install it via composer

```shell
composer require ady/maintenance-bundle
```

Use
===

you have several options for each driver.

Here the complete configuration with the `example` of each pair of class / options.

The ttl (time to life) option is optional everywhere, it is used to indicate the duration in `second` of the maintenance.

    #app/config.yml
    ady_maintenance:
        authorized:
            path: /path                                                         # Optional. Authorized path, accepts regexs
            host: your-domain.com                                               # Optional. Authorized domain, accepts regexs
            ips: ['127.0.0.1', '172.123.10.14']                                 # Optional. Authorized ip addresses
            query: { foo: bar }                                                 # Optional. Authorized request query parameter (GET/POST)
            cookie: { bar: baz }                                                # Optional. Authorized cookie
            route:                                                              # Optional. Authorized route name
            attributes:                                                         # Optional. Authorized route attributes
        driver:
            ttl: 3600                                                                  # Optional ttl option, can be not set

            # File driver
            class: '\Ady\Bundle\MaintenanceBundle\Drivers\FileDriver'                  # class for file driver
            options: {file_path: '%kernel.project_dir%/../var/cache/lock'}             # file_path is the complete path for create the file

            # Shared memory driver
            class: '\Ady\Bundle\MaintenanceBundle\Drivers\ShmDriver'                   # class for shared memory driver

            # MemCache driver
            class: Ady\Bundle\MaintenanceBundle\Drivers\MemCacheDriver                 # class for MemCache driver
            options: {key_name: 'maintenance', host: 127.0.0.1, port: 11211}           # need to define a key_name, the host and port

            # Database driver:
            class: 'Ady\Bundle\MaintenanceBundle\Drivers\DatabaseDriver'               # class for database driver

            # Option 1 : for doctrine
            options: {connection: custom, table: maintenance}                          # Optional. You can choose an other connection and/or a custom table name.
                                                                                       # Default connection is Doctrine.
                                                                                       # Default table name is "ady_maintenance"

            # Option 2 : for dsn, you must have a column ttl type datetime in your table.
            options: {dsn: "mysql:dbname=maintenance;host:localhost", table: maintenance, user: root, password: root}  # the dsn configuration, name of table, user/password

            # Recommended : after bundle installation (works with options 1 and 2)
            options: {table_created: true}                                              # Optional. After installation and after table creation, set this option to true to avoid
                                                                                        # the unnecessary query (create table if exists) at every request.
        
        #Optional. response code and status of the maintenance page
        response:
            code: 503                                                                  # Http response code of Exception page
            status: "Service Temporarily Unavailable"                                  # Exception page title
            exception_message: "Service Temporarily Unavailable"                       # Message when Exception is thrown 


### Commands

There are two commands:

    ady:maintenance:lock

This command will enable the maintenance according to your configuration. You can pass the time to life (in seconds) of the maintenance in parameter, ``this doesn't works with file driver``.

    ady:maintenance:unlock

This command will disable the maintenance

You can execute the lock without a warning message which you need to interact with:

    ady:maintenance:lock --no-interaction

Or (with the optional ttl overwriting)

    ady:maintenance:lock 3600 -n


---------------------

Custom error page 503
---------------------

In the listener, an exception is thrown when website is under maintenance. This exception is a 'This exception is a 'HttpException' (status 503), to custom your error page
 you need to create an error503.html.twig (if you use twig) in:
    app/Resources/TwigBundle/views/Exception

#### Important


    You must remember that this only works if Symfony works.

----------------------

Using with a Load Balancer
---------------------
Some load balancers will monitor the status code
of the http response to stop forwarding traffic
to your nodes.  If you are using a load balancer
you may want to change the status code of the
maintenance page to 200, so your users will still see
something. You may change the response code of the status page from 503 by changing the **response.code** configuration.


Service
--------

You can use the ``ady_maintenance.driver.factory`` service anyway in your app and call ``lock`` and ``unlock`` methods.
For example, you can build a backend module to activate maintenance mode.
In your controller:

    $driver = $this->get('ady_maintenance.driver.factory')->getDriver();
    $message = "";
    if ($action === 'lock') {
        $message = $driver->getMessageLock($driver->lock());
    } else {
        $message = $driver->getMessageUnlock($driver->unlock());
    }

    $this->get('session')->setFlash('maintenance', $message);

    return new RedirectResponse($this->generateUrl('_demo'));


**Warning**: Make sure you have allowed IP addresses if you run maintenance from the backend, otherwise you will find yourself blocked on page 503.
