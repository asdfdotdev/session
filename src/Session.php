<?php

/**
 * Session - a class that endeavors to make using better sessions easier.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2.1 of the License only.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package   Asdfdotdev
 * @author    Chris Carlevato <hello@asdf.dev>
 * @copyright 2015-2020 Chris Carlevato
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html
 * @version   0.5.0
 * @link      https://github.com/asdfdotdev/session
 */

namespace Asdfdotdev;

/**
 * Class Session
 *
 * @package Asdfdotdev
 */
class Session
{
    /** @var string Key for session value array */
    protected $valuesKey = 'asdfdotdev.session';

    /** @var string Name of the session */
    protected $name;

    /** @var string Path the session cookie is available on  */
    protected $path;

    /** @var string Domain the cookie is available on */
    protected $domain;

    /** @var boolean Only transmit the session cookie over https */
    protected $secure;

    /** @var string Name of hashing algorithm to use for hashed values */
    protected $hash;

    /** @var int Length of Session ID string */
    protected $idLength;

    /** @var int Number of bits in encoded Session ID characters */
    protected $idBits;

    /** @var boolean Generate fake PHPSESSID cookie */
    protected $decoy;

    /** @var int Minimum time in seconds to regenerate session id */
    protected $timeMin;

    /** @var int Maximum time in seconds to regenerate session id */
    protected $timeMax;

    /** @var bool */
    protected $debug;

    /**
     * Config settings can include:
     * - name:    Name of the session                         (Default: asdfdotdev)
     * - path:    Server path the cookie is available on      (Default: /)
     * - domain:  Domain the session cookie is available on   (Default: localhost)
     * - secure:  Only transmit the cookie over https         (Default: false)
     * - hash:    Name of algorithm to use for hashed values  (Default: sha256)
     * - decoy:   Generate fake PHPSESSID cookie              (Default: true)
     * - min:     Min time in seconds to regenerate session   (Default: 60)
     * - max:     Max time in seconds to regenerate session   (Default: 600)
     *
     * @param array $config Session Configuration
     *
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        $settings = array_merge(
            [
                'name' => 'asdfdotdev',
                'path' => '/',
                'domain' => 'localhost',
                'secure' => false,
                'bits' => 4,
                'length' => 32,
                'hash' => 'sha256',
                'decoy' => true,
                'min' => 60,
                'max' => 600,
                'debug' => false,
            ],
            $config
        );

        $this->setName($settings['name']);
        $this->setPath($settings['path']);
        $this->setDomain($settings['domain']);
        $this->setSecure($settings['secure']);
        $this->setIdBits($settings['bits']);
        $this->setIdLength($settings['length']);
        $this->setHash($settings['hash']);
        $this->decoy = $settings['decoy'];
        $this->timeMin = $settings['min'];
        $this->timeMax = $settings['max'];
        $this->debug = $settings['debug'];

        $this->verifySettings();

        return $this;
    }

    /**
     * Set session name, this is also used as the name of the session cookie.
     *
     * @param string $name Session Name
     */
    protected function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get session name.
     *
     * @return string Session Name
     */
    protected function getName()
    {
        return $this->name;
    }

    /**
     * Set path on the domain where the cookies will work
     * Use a single slash (default) for all paths on the domain.
     *
     * @param  string $path Cookie Path
     * @return void
     */
    protected function setPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * Get cookie path.
     *
     * @return string Cookie Path
     */
    protected function getPath()
    {
        return $this->path;
    }

    /**
     * Set cookie domain. To make cookie visible on all subdomains prefixed with a dot
     *
     * @param string $domain Cookie Domain
     */
    protected function setDomain(string $domain = '')
    {
        $domain = ($domain == '') ? $_SERVER['SERVER_NAME'] : $domain;
        $this->domain = $domain;
    }

    /**
     * Get session cookie domain.
     *
     * @return string Cookie Domain
     */
    protected function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set cookie secure status. If TRUE cookie will only be sent over secure connections.
     *
     * @param bool $secure Cookie Secure Status
     *
     * @return void
     */
    protected function setSecure(bool $secure = false)
    {
        $this->secure = $secure;
    }

    /**
     * Get cookie secure status.
     *
     * @return bool Cookie Secure Status
     */
    protected function getSecure()
    {
        return $this->secure;
    }

    /**
     * Set cookie id hash method.
     *
     * @param int/string $hash 0 = MD5, 1 = SHA1, or supported hash name (Default: 1)
     *
     * @throws \Exception
     */
    protected function setHash(string $hash = '')
    {
        if (in_array($hash, hash_algos())) {
            $this->hash = $hash;
        } else {
            $this->error(
                'Server does not support selected hash algorithm selected.'
            );
        }
    }

    /**
     * Get session hash setting.
     *
     * @return int Cookie Hash Setting
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set length of session id string.
     *
     * @param int $length
     * @throws \Exception
     *
     * @return void
     */
    protected function setIdLength(int $length)
    {
        if (in_array($length, range(22, 256))) {
            $this->idLength = $length;
        } else {
            $this->error(
                'Session ID length invalid. Length must be between 22 to 256.'
            );
        }
    }

    /**
     * Get session id length.
     *
     * @return int
     */
    protected function getIdLength()
    {
        return $this->idLength;
    }

    /**
     * Set session id bits.
     *
     * @param int $bits
     *
     * @throws \Exception
     *
     * @return void
     */
    protected function setIdBits(int $bits)
    {
        if (in_array($bits, range(4, 6))) {
            $this->idBits = $bits;
        } else {
            $this->error(
                'Session ID bits per character invalid. Options are 4, 5, or 6.'
            );
        }
    }

    /**
     * Get session id bits.
     *
     * @return int
     */
    protected function getIdBits()
    {
        return $this->idBits;
    }

    /**
     * Create decoy cookie if it hasn't been set.
     *
     * This cookie intentionally exhibits signs of a week session cookie so that it
     * looks attractive to would be scoundrels. These vulnerabilities include:
     * - PHPSESSID name
     * - MD5 hash value
     * - not HTTPOnly
     *
     * @throws \Exception
     *
     * @return void
     */
    protected function generateDecoyCookie()
    {
        $has_decoy = isset($_COOKIE['PHPSESSID']);

        if ($this->decoy && !$has_decoy) {
            $this->setValue('decoy_value', md5(mt_rand()));
            setcookie(
                'PHPSESSID',
                $this->getValue('decoy_value'),
                0,
                $this->getPath(),
                $this->getDomain(),
                $this->getSecure(),
                0
            );
        }
    }

    /**
     * Generate sha256 fingerprint hash from current settings.
     *
     * @return string
     */
    protected function generateFingerprint()
    {
        return hash(
            'sha256',
            $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . session_id()
        );
    }

    /**
     * Create session fingerprint from user agent, ip and session id in an attempt to discourage session hijacking.
     *
     * @return void
     */
    protected function setFingerprint()
    {
        $this->setValue(
            'fingerprint',
            $this->generateFingerprint()
        );
    }

    /**
     * Compare current user agent, ip and session id against stored session fingerprint
     * If compared value doesn't match stored value session end the session.
     *
     * @throws \Exception
     *
     * @return bool Valid fingerprint
     */
    protected function validateFingerprint()
    {
        $print = $this->getValue('fingerprint');
        $valid = $this->generateFingerprint();

        if (!isset($print)) {
            $this->setFingerprint();
        } elseif ($print != $valid) {
            $this->end();
            return false;
        }

        return true;
    }

    /**
     * Reset session lifespan time using random value between timeMin and timeMax.
     *
     * @return void
     */
    protected function resetLifespan()
    {
        $this->setValue(
            'lifespan',
            date('U') + mt_rand($this->timeMin, $this->timeMax)
        );
    }

    /**
     * Compare session lifespan time to current time
     * If current time is beyond session lifespan regenerate session id.
     *
     * @throws \Exception
     *
     * @return void
     */
    protected function checkLifespan()
    {
        if ($this->getValue('lifespan') == '') {
            $this->resetLifespan();
        } elseif ($this->getValue('lifespan') < date('U')) {
            $this->regenerate();
        }
    }

    /**
     * Start Session.
     *
     * @param bool $restart Force session id regeneration
     *
     * @throws \Exception
     *
     * @return void
     */
    public function start($restart = false)
    {
        if ($restart) {
            $this->regenerateId();
        } else {
            $this->configureSystemSessionSettings();
        }

        $this->prepareSession();

        if ($restart) {
            $this->setFingerprint();
            $this->resetLifespan();
        }

        if ($this->validateFingerprint()) {
            $this->generateDecoyCookie();
            $this->checkLifespan();
            $this->setValue('session_loaded', date('U'));
            $this->setValue(
                'ttl',
                ($this->getValue('lifespan') - $this->getValue('session_loaded'))
            );
        }
    }

    /**
     * Generate generate system session, maybe scaffold value array
     *
     * @return void
     */
    private function prepareSession()
    {
        session_set_cookie_params(
            0,
            $this->getPath(),
            $this->getDomain(),
            $this->getSecure(),
            true
        );

        session_name($this->getName());
        session_start();

        if (!isset($_SESSION[$this->valuesKey])) {
            $_SESSION[$this->valuesKey] = [];
        }
    }

    /**
     * Get session variable value.
     *
     * @param string $key Name of the session variable value to retrieve
     *
     * @return mixed Value of the variable requested
     */
    public function getValue($key)
    {
        if (!isset($_SESSION[$this->valuesKey][$key])) {
            return null;
        }

        return $_SESSION[$this->valuesKey][$key];
    }

    /**
     * Create session value if not present, otherwise the value is updated.
     *
     * @param string $key   Name of the session variable to create/update
     * @param mixed  $value Value of the session variable to create/update
     * @param bool   $hash  false = store $value in session array as plain text,
     *                      true = store hash of $value in session array
     *
     * @return void
     */
    public function setValue($key, $value, $hash = false)
    {
        if ($hash) {
            $value = hash($this->getHash(), $value);
        }

        $_SESSION[$this->valuesKey][$key] = $value;
    }

    /**
     * Append to session value.
     * Note: Append behavior varies by current value type:
     *       - Array: passed value added to array (array_merge)
     *       - String: passed value added to the end of the string (concatenation)
     *       - Other: passed value replaces the saved value (replace)
     *
     * @param string $key Name of the session variable to create/update
     * @param string $value String to append to the end of the current value
     *
     * @throws \Exception
     *
     * @return void
     */
    public function appValue($key, $value)
    {
        $currentValue = $this->getValue($key);

        if (isset($currentValue)) {
            if (is_array($currentValue)) {
                $updatedValue = array_merge($currentValue, $value);
            } elseif (is_string($currentValue)) {
                $updatedValue = $currentValue . $value;
            } else {
                $updatedValue = $value;
            }
        } else {
            $updatedValue = $value;
        }

        $this->setValue($key, $updatedValue);
    }

    /**
     * Increment session value.
     *
     * @param string $key Name of the session variable to create/increment
     * @param int $value Amount to add to the current value
     *
     * @throws \Exception
     *
     * @return void
     */
    public function incValue($key, $value)
    {
        if (!is_numeric($value)) {
            $this->error(
                sprintf(
                    'Only numeric values can be passed to %s',
                    __METHOD__
                )
            );
        }

        $currentValue = $this->getValue($key);

        if (isset($currentValue)) {
            $updatedValue = $currentValue + $value;
        } else {
            $updatedValue = $value;
        }

        $this->setValue($key, $updatedValue);
    }

    /**
     * Drop session value.
     *
     * @param string $key Name of the session variable to drop
     *
     * @return void
     */
    public function dropValue($key)
    {
        unset($_SESSION[$this->valuesKey][$key]);
    }

    /**
     * Restart session with reset flag true.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function regenerate()
    {
        $this->start(true);
    }

    /**
     * Regenerate session id.
     */
    private function regenerateId()
    {
        session_regenerate_id(true);
        $new_id = session_id();
        session_write_close();
        session_id($new_id);
    }

    /**
     * Update system session values.
     *
     * @see https://www.php.net/manual/en/session.configuration.php
     */
    private function configureSystemSessionSettings()
    {
        if (function_exists('ini_set')) {
            ini_set('session.sid_length', $this->getIdLength());
            ini_set('session.sid_bits_per_character', $this->getIdBits());
            ini_set('session.cookie_secure', $this->getSecure());
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
        }
    }

    /**
     * End session.
     *
     * @return void
     */
    public function end()
    {
        session_unset();
        session_destroy();
    }

    /**
     * Dump session contents for debugging or testing.
     *
     * @param int $format 0 = string
     *                    1 = array
     *                    2 = json encoded string
     *
     * @return mixed
     */
    public function dump($format = 1)
    {
        switch ($format) {
            // string
            case 1:
                return print_r($_SESSION, true);
                break;
            // array
            case 2:
                return $_SESSION;
                break;
            // json string
            case 3:
            default:
                return json_encode($_SESSION);
                break;
        }
    }

    /**
     * Prevents incorrect configuration of timeMin/time/Max lifespan values.
     *
     * @throws \Exception
     *
     * @return void
     */
    private function validateSessionLifespan()
    {
        if ($this->timeMin > $this->timeMax) {
            $this->timeMin = $this->timeMin + $this->timeMax;
            $this->timeMax = $this->timeMin - $this->timeMax;
            $this->timeMin -= $this->timeMax;
        }
    }

    /**
     * In the event timezone is unset, set it if possible.
     *
     * @throws \Exception
     *
     * @return void
     */
    private function validateSystemTimezone()
    {
        if (function_exists('ini_get') && ini_get('date.timezone') == '') {
            date_default_timezone_set('UTC');
        }
    }

    /**
     * Confirm session path is writable.
     *
     * @throws \Exception
     *
     * @return void
     */
    private function validateSessionDir()
    {
        if (!is_writable(session_save_path())) {
            $this->error(
                'Session directory is not writable.'
            );
        }
    }

    /**
     * Confirm that request domain matches cookie domain.
     *
     * @throws \Exception
     *
     * @return void
     */
    private function validateSessionDomain()
    {
        if ($_SERVER['HTTP_HOST'] != $this->getDomain()) {
            $this->error(
                sprintf(
                    'Session cookie domain (%s) and request domain (%s) mismatch.',
                    $_SERVER['HTTP_HOST'],
                    $this->getDomain()
                )
            );
        }
    }

    /**
     * Confirm PHP version is at least 7.2.0
     *
     * @throws \Exception
     *
     * @return void
     */
    private function validatePHPVersion()
    {
        if (version_compare(phpversion(), '7.2.0', '<')) {
            $this->error(
                'PHP v7.2.0 or newer is required.'
            );
        }
    }

    /**
     * Throw exception on error.
     *
     * @param string $response Explain to them what they screwed up
     *
     * @throws \Exception
     *
     * @return void
     */
    protected function error($response)
    {
        throw new \Exception($response, null, null);
    }

    /**
     * Validate various requirements
     * @throws \Exception
     */
    protected function verifySettings()
    {
        $this->validateSystemTimezone();
        $this->validateSessionLifespan();

        if ($this->debug) {
            $this->validatePHPVersion();
            $this->validateSessionDir();
            $this->validateSessionDomain();
        }
    }
}
