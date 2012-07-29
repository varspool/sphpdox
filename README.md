<!-- vim: set ts=4 sw=4 tw=78 : -->

# Sphpdox
## PHPDoc to Sphinx phpdomain

Writing ReStructuredText documentation for your PHP project? Already have well
documented PHP code using docblocks? Use this to generate API documentation
compatible with Sphinx's [sphinxcontrib-phpdomain](http://packages.python.org/sphinxcontrib-phpdomain/).

The name is a mixture of 'sphinx' and 'phpdoc'.

### Installing

Compatible with PSR-0. The vendor namespace is **Sphpdox**.

#### composer

Sphpdox is available on Packagist as `sphpdox/sphpdox`.

```json
    {
        ...
        "require": {
            "sphpdox/sphpdox": "dev-master"
        }
    }
```

### What It Does

Sphpdox reads a directory of your namespaced source code. It finds documented
classes, methods, properties, etc. and produces documentation in ReStructuredText
format, using the `phpdomain` available as part of `sphinxcontrib`. It takes a 
library like this:

```
lib
├── SplClassLoader.php
└── Wrench
    ├── Application
    │   ├── Application.php
    │   └── EchoApplication.php
    ├── BasicServer.php
    ├── Client.php
    ├── ConnectionManager.php
    ├── Connection.php
    ├── Frame
    │   ├── Frame.php
    │   └── HybiFrame.php
    ├── Listener
    │   ├── HandshakeRequestListener.php
    │   ├── Listener.php
    │   ├── OriginPolicy.php
    │   └── RateLimiter.php
    ├── Payload
    │   ├── HybiPayload.php
    │   └── Payload.php
    ├── Protocol
    │   ├── Hybi10Protocol.php
    │   ├── HybiProtocol.php
    │   ├── Protocol.php
    │   └── Rfc6455Protocol.php
    ├── Resource.php
    ├── Server.php
    ├── Socket
    │   ├── ClientSocket.php
    │   ├── ServerClientSocket.php
    │   ├── ServerSocket.php
    │   ├── Socket.php
    │   └── UriSocket.php
    └── Util
        ├── Configurable.php
        └── Ssl.php
```

And turns it into a documentation tree, like this:

```
build/Wrench/
├── Application
│   ├── Application.rst
│   ├── EchoApplication.rst
│   └── index.rst
├── BasicServer.rst
├── Client.rst
├── ConnectionManager.rst
├── Connection.rst
├── Frame
│   ├── Frame.rst
│   ├── HybiFrame.rst
│   └── index.rst
├── index.rst
├── Listener
│   ├── HandshakeRequestListener.rst
│   ├── index.rst
│   ├── Listener.rst
│   ├── OriginPolicy.rst
│   └── RateLimiter.rst
├── Payload
│   ├── HybiPayload.rst
│   ├── index.rst
│   └── Payload.rst
├── Protocol
│   ├── Hybi10Protocol.rst
│   ├── HybiProtocol.rst
│   ├── index.rst
│   ├── Protocol.rst
│   └── Rfc6455Protocol.rst
├── Resource.rst
├── Server.rst
├── Socket
│   ├── ClientSocket.rst
│   ├── index.rst
│   ├── ServerClientSocket.rst
│   ├── ServerSocket.rst
│   ├── Socket.rst
│   └── UriSocket.rst
└── Util
    ├── Configurable.rst
    ├── index.rst
    └── Ssl.rst
```

Where each file contains documentation for a class, like this:

```rst
.. php:class:: DateTime

  Datetime class

  .. php:method:: setDate($year, $month, $day)

      Set the date.

      :param int $year: The year.
      :param int $month: The month.
      :param int $day: The day.
      :returns: Either false on failure, or the datetime object for method chaining.


  .. php:method:: setTime($hour, $minute[, $second])

      Set the time.

      :param int $hour: The hour
      :param int $minute: The minute
      :param int $second: The second
      :returns: Either false on failure, or the datetime object for method chaining.

  .. php:const:: ATOM

      Y-m-d\TH:i:sP
```

### Usage

`./sphpdox.php help` for help. The main command is `process`. If you don't
supply any arguments, you'll be asked interactively.

Here's the built-in help for the `process` command:

```
$./sphpdox.php help process
Usage:
 process [-o|--output="..."] namespace path

Arguments:
 namespace     The namespace to process
 path          The path the namespace can be found in

Options:
 --output (-o) The path to output the ReST files (default: build)

Help:
 The process command works recursively on a directory of PHP code.
```

### Libraries

Sphpdox uses the Symfony Console component, and PHP-Token-Reflection.