# Session

[![Build Status](https://travis-ci.org/asdfdotdev/session.svg?branch=master)](https://travis-ci.org/asdfdotdev/session) [![codecov](https://codecov.io/gh/asdfdotdev/session/branch/master/graph/badge.svg)](https://codecov.io/gh/asdfdotdev/session) [![Packagist](https://img.shields.io/packagist/dm/asdfdotdev/session)](https://packagist.org/packages/asdfdotdev/session)

This class endeavors to make it easy to use better sessions in PHP scripts.

* Easily set, increment, append, hash, and drop session values
* Custom session naming
* Simple session fingerprint check
* Regenerate session id at random intervals
* Change session id length & bits per character*
* HTTPOnly session cookie
* Decoy PHPSESSID cookie
* Force session strict mode*
* Force session use only cookies*
* Force HTTPS only session cookies*
* Supports PHP 7.2+

<sup>\* Requires `ini_set()` method be enabled.</sup>

### Compatibility

[![PHP Compatibility](https://img.shields.io/badge/PHP-7.2_to_7.4-%238892BF.svg?logo=php)](https://php.net/)

Session class is developed for and tested with recent PHP Version:

- PHP 7.2, 7.3, and 7.4


## Installation

```
composer require asdfdotdev/session
```

## Use

A number of usage examples are included in `_examples/`. Check out the examples [README](./_examples/README.md) for further details.

## Tests

Information regarding the included tests is available in the tests the [README](./_test#readme).

[Build history can be browsed at Travis-CI.](https://travis-ci.org/asdfdotdev/session)

## Standards

This class follows [PSR-12](https://www.php-fig.org/psr/psr-12/) code standards.

## Debugging

By default basic checks are performed when creating a session:

- **Session Lifespan:** Prevents min lifespan from being greater than max lifespan.
- **System Timezone:** Confirms default timezone is configured for PHP, if not UTC is set.

Additional optional debugging can be enabled in session settings:

- **PHP Version:** Confirms the version available is 7.2.0 or newer
- **Session Directory:** Confirms write access to PHP session directory
- **Session Domain:** Confirms session domain setting matches the request domain

## Contributing

Feedback, bug reports, feature requests, and pull requests are welcome!

If you'd like to contribute please reference our [code of conduct](./.github/CODE_OF_CONDUCT.md) and [contributing](./.github/CONTRIBUTING.md) guides.
