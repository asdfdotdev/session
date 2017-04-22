<?php
/**
 * @copyright 2015-2017 Chris Carlevato (https://github.com/chrislarrycarl)
 * @license http://www.gnu.org/licenses/lgpl-2.1.html
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public 
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 */

namespace ChristopherL;

class Session
{
    const VERSION = '0.3';

    protected $name, $domain, $hash, $key, $path, $secure, $decoy, $min_time, $max_time;
    protected $failmsg = 'Session generation failed.';

    /**
     * Config settings can include:
     * - name:      Name of the session                             (Default: clsession)
     * - path:      Server path the cookie is available on          (Default: /)
     * - domain:    Domain the cookie is available to               (Default: localhost)
     * - secure:    Only transmit the cookie over https             (Default: false)
     * - hash:      0 = MD5, 1 = SHA1, or supported hash name       (Default: 1)
     * - decoy:     True/False to generate fake PHPSESSID cookie    (Default: true)
     * - min:       Min time, in seconds, to regenerate session     (Default: 60)
     * - max:       Max time, in seconds, to regenerate session     (Default: 600).
     * 
     * @param array $config Session Configuration
     */
    public function __construct($config = array())
    {
        // Create session settings based on provided config
        $settings = array(
            'name'      => isset($config['name'])   ? $config['name']   : 'clsession',
            'path'      => isset($config['path'])   ? $config['path']   : '/',
            'domain'    => isset($config['domain']) ? $config['domain'] : 'localhost',
            'secure'    => isset($config['secure']) ? $config['secure'] : false,
            'hash'      => isset($config['hash'])   ? $config['hash']   : 1,
            'decoy'     => isset($config['decoy'])  ? $config['decoy']  : true,
            'min'       => isset($config['min'])    ? $config['min']    : 60,
            'max'       => isset($config['max'])    ? $config['max']    : 600,
        );

        // If min is greater than max swap values so we can construct valid lifespan
        if ($settings['min'] > $settings['max']) {
            $settings['min'] = $settings['min'] + $settings['max'];
            $settings['max'] = $settings['min'] - $settings['max'];
            $settings['min'] -= $settings['max'];
        }

        // Apply session settings
        $this->setName($settings['name']);
        $this->setPath($settings['path']);
        $this->setDomain($settings['domain']);
        $this->setSecure($settings['secure']);
        $this->setHash($settings['hash']);
        $this->min_time = $settings['min'];
        $this->decoy = $settings['decoy'];
        $this->max_time = $settings['max'];

        // Sometimes a timezone isn't set. This will avoid an error in those instances.
        if (function_exists('ini_get') && ini_get('date.timezone') == '') {
            date_default_timezone_set('UTC');
        }
    }

    /**
     * Set session name, this is also used as the name of the session cookie.
     *
     * @param string $name Session Name
     */
    protected function setName($name)
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
     * @param string $path Cookie Path
     */
    protected function setPath($path)
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
     * Ex) .christopherl.com.
     *
     * @param string $domain Cookie Domain
     */
    protected function setDomain($domain = '')
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
     */
    protected function setSecure($secure = false)
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
     */
    protected function setHash($hash = 1)
    {
        if ($hash === 0) {
            $hash = 'md5';
        }
        else if ($hash === 1) {
            $hash = 'sha256';
        }
        else if (in_array($hash, hash_algos())) {
            $hash = $hash;
        }
        else {
            $this->Error('Invalid hash algorithm selected.');
        }

        $this->hash = $hash;
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
     * Create decoy cookie if it hasn't been set.
     *
     * This cookie intentionally exhibits signs of a week session cookie so that it looks attractive
     * to would be scoundrels. These vulnerabilities include: PHPSESSID name, MD5 hash value, and not HTTPOnly.
     */
    protected function generateDecoyCookie()
    {
        if (!isset($_COOKIE['PHPSESSID'])) {
            $this->setValue('decoy_value', md5(mt_rand()));
            setcookie('PHPSESSID', $this->getValue('decoy_value'), 0, $this->getPath(), $this->getDomain(), $this->getSecure(), 0);
        }
    }

    /**
     * Destroy PHPSESSID decoy cookie.
     */
    protected function killDecoyCookie()
    {
        if (isset($_COOKIE['PHPSESSID'])) {
            unset($_COOKIE['PHPSESSID']);
        }
    }

    /**
     * Create session fingerprint from user agent, ip and session id in an attempt to discourage session hijacking.
     */
    protected function generateFingerprint()
    {
        $this->setValue('fingerprint', sha1($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].session_id()));
    }

    /**
     * Compare current user agent, ip and session id against stored session fingerprint
     * If compared value doesn't match stored value session end the session.
     */
    protected function validateFingerprint()
    {
        if ($this->getValue('fingerprint') == '') {
            $this->generateFingerprint();
        } elseif ($this->getValue('fingerprint') != sha1($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].session_id())) {
            $this->end();
        }
    }

    /**
     * Reset session lifespan time using random value between min_time and max_time.
     */
    protected function resetLifespan()
    {
        $this->setValue('lifespan', date('U') + mt_rand($this->min_time, $this->max_time));
    }

    /**
     * Compare session lifespan time to current time
     * If current time is beyond session lifespan regenerate session id.
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
     */
    public function start($restart = false)
    {

        // when restarting regenerate session id
        if ($restart) {
            session_regenerate_id(true);
            $new_id = session_id();
            session_write_close();
            session_id($new_id);
        }

        if (function_exists('ini_set') && !$restart) {
            ini_set('session.hash_function', $this->getHash());
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.use_only_cookies', 1);
        }

        session_set_cookie_params(0, $this->getPath(), $this->getDomain(), $this->getSecure(), true);
        session_name($this->getName());
        session_start();
        $_SESSION['clValues'] = (isset($_SESSION['clValues'])) ? $_SESSION['clValues'] : array();

        // on restart or initial creation (empty clValues) generate fingerprint & lifespan
        if ($restart || count($_SESSION['clValues']) == 0) {
            $this->generateFingerprint();
            $this->resetLifespan();
        }

        if ($this->decoy) {
            $this->generateDecoyCookie();
        } else {
            $this->dropValue('decoy_value');
        }

        $this->validateFingerprint();
        $this->checkLifespan();
        $this->setValue('session_load', date('U'));
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
        if (!isset($_SESSION['clValues'][$key])) {
            $this->Error('Invalid Session Value Name');
        }

        return $_SESSION['clValues'][$key];
    }

    /**
     * Create session value if not present, otherwise the value is updated.
     *
     * @param string $key   Name of the session variable to create/update
     * @param string $value Value of the session variable to create/update
     * @param int    $hash  0 = store $value in session array as plain text,
     *                      1 = store SHA1 hash of $value in session array
     */
    public function setValue($key, $value, $hash = false)
    {
        // if requested, hash the value before saving it
        if ($hash) {
            $value = hash($this->getHash(), $value);
        }

        $_SESSION['clValues'][$key] = $value;
    }

    /**
     * Append session value.
     *
     * @param string $key   Name of the session variable to create/update
     * @param string $value String to append to the end of the current value
     */
    public function appValue($key, $value)
    {
        if (isset($_SESSION['clValues'][$key])) {
            $_SESSION['clValues'][$key] = ($this->getValue($key).$value);
        } else {
            $this->setValue($key, $value);
        }
    }

    /**
     * Increment session value.
     *
     * @param string $key    Name of the session variable to create/increment
     * @param int    $amount Amount to add to the current value
     */
    public function incValue($key, $amount)
    {
        if (isset($_SESSION['clValues'][$key])) {
            $_SESSION['clValues'][$key] += $amount;
        } else {
            $this->setValue($key, $amount);
        }
    }

    /**
     * Drop session value.
     *
     * @param string $key Name of the session variable to drop
     */
    public function dropValue($key)
    {
        unset($_SESSION['clValues'][$key]);
    }

    /**
     * Regenerate session id.
     */
    public function regenerate()
    {
        $this->start(true);
    }

    /**
     * End session.
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
     * Throw exception on error.
     *
     * @param string $response Explain to them what they screwed up
     *
     * @throws \Exception
     */
    protected function Error($response)
    {
        throw new \Exception($response, null, null);
    }
}
