<?php
/**
 * Class SessionTest.
 *
 * Tests include:
 *  testSessionNoSettings           Creates and tests a new session with only default settings
 *  testSessionCustomSettings       Creates and tests a new session with custom settings
 *  testSessionValue                Creates new session value then changes the value
 *  testSessionAppendValue          Creates a new session value then appends to the value
 *  testSessionIncrementValue       Creates a new session value then increments the value
 *  testSessionDeleteValue          Creates a new session value then deletes it
 *  testSessionHashValue            Creates a new hashed session value
 *  testSessionRegenerate           Regenerates session id
 *  testSessionFingerprint          Validates session fingerprint in different situations
 *  testExceptionThrow              Validates exception handling by class
 *  testSessionDir                  Validates session directory without write access
 *  testSessionDomain               Validates session domain mismatch
 */

namespace Asdfdotdev;

class TestSession extends \PHPUnit\Framework\TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testSessionNoSettings()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require '../src/Session.php';
        $session = new Session();
        $session->start();

        // Verify session lifespan falls between default times range of 60 & 600
        $current_time = date('U');
        $session_min = $current_time + 60;
        $session_max = $current_time + 600;
        $session_lifespan = $session->getValue('lifespan');
        $this->assertGreaterThanOrEqual($session_min, $session_lifespan);
        $this->assertLessThanOrEqual($session_max, $session_lifespan);

        // Verify default session name is in use
        $this->assertEquals('asdfdotdev', session_name());

        // Verify default session cookie settings
        $array_details = session_get_cookie_params();
        $this->assertEquals('/', $array_details['path']);
        $this->assertEquals('localhost', $array_details['domain']);
        $this->assertEquals(false, $array_details['secure']);
        $this->assertEquals(true, $array_details['httponly']);

        // Verify decoy value is in session array (decoy cookie in use)
        $session_dump = $session->dump(2);
        $this->assertTrue(array_key_exists('decoy_value', $session_dump['asdfdotdev.session']));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionCustomSettings()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require '../src/Session.php';
        $session = new Session([
            'name' => 'TestSession',
            'path' => '/subdirectory',
            'domain' => '.testing.edu',
            'secure' => true,
            'decoy' => false,
            'hash' => 'sha512',
            'min' => 150,
            'max' => 200,
        ]);
        $session->start();

        // Verify session lifespan falls between default times range of 60 & 600
        $current_time = date('U');
        $session_min = $current_time + 150;
        $session_max = $current_time + 200;
        $session_lifespan = $session->getValue('lifespan');
        $this->assertGreaterThanOrEqual($session_min, $session_lifespan);
        $this->assertLessThanOrEqual($session_max, $session_lifespan);

        // Verify default session name is in use
        $this->assertEquals('TestSession', session_name());

        // Verify default session cookie settings
        $array_details = session_get_cookie_params();
        $this->assertEquals('/subdirectory', $array_details['path']);
        $this->assertEquals('.testing.edu', $array_details['domain']);
        $this->assertEquals(true, $array_details['secure']);
        $this->assertEquals(true, $array_details['httponly']);

        // Verify hash has changed
        $this->assertEquals('sha512', $session->getHash());

        // Verify decoy value isn't in session array (no decoy cookie in use)
        $session_dump = $session->dump(2);
        $this->assertFalse(array_key_exists('decoy_value', $session_dump['asdfdotdev.session']));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionValue()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require '../src/Session.php';
        $session = new Session();
        $session->start();

        // Create a new session string values
        $session->setValue('my_variable', 'this is the value');
        $session->setValue('my_hashed_variable', 'this is the other value', true);

        // Verifiy creation of session values
        $this->assertEquals('this is the value', $session->getValue('my_variable'));
        $this->assertEquals(
            hash(
                'sha256',
                'this is the other value'
            ),
            $session->getValue('my_hashed_variable')
        );

        // Change session value
        $session->setValue('my_variable', 50);

        // Verifiy changed session value
        $this->assertEquals(50, $session->getValue('my_variable'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionAppendValue()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require '../src/Session.php';
        $session = new Session();
        $session->start();

        // Create a new session value
        $session->appValue('my_variable', 'this is the base value');

        // Verifiy creation of session value
        $this->assertEquals('this is the base value', $session->getValue('my_variable'));

        // Append something to the value
        $session->appValue('my_variable', ' updated');

        // Verifiy changed session value
        $this->assertEquals('this is the base value updated', $session->getValue('my_variable'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionIncrementValue()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require '../src/Session.php';
        $session = new Session();
        $session->start();

        // Create a new session value
        $session->incValue('my_variable', 10);

        // Verifiy creation of session value
        $this->assertEquals(10, $session->getValue('my_variable'));

        // Append something to the value
        $session->incValue('my_variable', 5.5);

        // Verifiy changed session value
        $this->assertEquals(15.5, $session->getValue('my_variable'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionDeleteValue()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require '../src/Session.php';
        $session = new Session();
        $session->start();

        // Create a new session string value
        $session->setValue('my_variable', 'i exist');

        // Verifiy creation of session value
        $this->assertEquals('i exist', $session->getValue('my_variable'));

        // Delete the value
        $session->dropValue('my_variable');

        // Verify decoy value isn't in session array (no decoy cookie in use)
        $session_dump = $session->dump(2);
        $this->assertFalse(array_key_exists('my_variable', $session_dump['asdfdotdev.session']));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionRegenerate()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require '../src/Session.php';
        $session = new Session();
        $session->start();

        // Save the initial session id
        $initial_session_id = session_id();

        // Regenerate the session
        $session->regenerate();

        // Verify the two ids are different
        $this->assertNotEquals($initial_session_id, session_id());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionFingerprint()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require '../src/Session.php';
        $session = new Session();
        $session->start();
        $session->setValue('my_variable', 'this is the value');

        $this->assertEquals(
            hash(
                'sha256',
                $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . session_id()
            ),
            $session->getValue('fingerprint')
        );

        if (rand(0, 1) == 1) {
            $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows; U; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)';
        } else {
            $_SERVER['REMOTE_ADDR'] = '10.0.10.1';
        }

        $this->assertNOTEquals(
            hash(
                'sha256',
                $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . session_id()
            ),
            $session->getValue('fingerprint')
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testExceptionThrow()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require '../src/Session.php';
        $session = new Session();
        $session->start();

        $missing_value = $session->getValue('there_is_no_spoon');
        $this->assertEquals(null, $missing_value);
    }

    /**
     * @runInSeparateProcess
     */
    public function testInvalidHash()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        try {
            require '../src/Session.php';
            $session = new Session([
                'hash' => 'doesnotexist'
            ]);
            $session->start();
        } catch (\Exception $e) {
            $this->assertEquals('Server does not support selected hash algorithm selected.', $e->getMessage());
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testInvalidIdLength()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        try {
            require '../src/Session.php';
            $session = new Session([
                'length' => '21'
            ]);
            $session->start();
        } catch (\Exception $e) {
            $this->assertEquals('Session ID length invalid. Length must be between 22 to 256.', $e->getMessage());
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testInvalidIdBits()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        try {
            require '../src/Session.php';
            $session = new Session([
                'bits' => '3'
            ]);
            $session->start();
        } catch (\Exception $e) {
            $this->assertEquals('Session ID bits per character invalid. Options are 4, 5, or 6.', $e->getMessage());
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testInvalidIncrement()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        try {
            require '../src/Session.php';
            $session = new Session();
            $session->start();

            $session->incValue('should-not-work', 'this is not a numeric value');
        } catch (\Exception $e) {
            $this->assertEquals('Only numeric values can be passed to Asdfdotdev\Session::incValue', $e->getMessage());
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testInvalidMinMax()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require '../src/Session.php';
        $session = new Session([
            'min' => 600,
            'max' => 60,
        ]);
        $session->start();

        $ttl = $session->getValue('ttl');

        $this->assertGreaterThan(0, $ttl);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionDir()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $testDirectory = sprintf('%s/asdf_session_test', sys_get_temp_dir());

        try {
            if(!file_exists($testDirectory)) {
                mkdir($testDirectory, 0555, false);
            }
            ini_set('session.save_path', $testDirectory);
            require '../src/Session.php';
            $session = new Session(['debug' => true]);
            $session->start();
        } catch (\Exception $e) {
            $this->assertEquals('Session directory is not writable.', $e->getMessage());
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionDomain()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_HOST'] = 'somethingelse.tld';

        try {
            ini_set('session.save_path', sys_get_temp_dir());
            require '../src/Session.php';
            $session = new Session(['debug' => true, 'domain' => 'nope.invalid']);
            $session->start();
        } catch (\Exception $e) {
            $this->assertEquals(
                sprintf(
                    'Session cookie domain (%s) and request domain (%s) mismatch.',
                    $_SERVER['HTTP_HOST'],
                    'nope.invalid'
                ),
                $e->getMessage()
            );
        }
    }

}
