<?php
/**
 * Class SessionTest
 *
 * Tests include:
 *  testSessionNoSettings           Creates and tests a new session with only default settings
 *  testSessionCustomSettings       Creates and tests a new session with custom settings
 *  testSessionValue                Creates new session value then changes the value
 *  testSessionAppendValue          Creates a new session value then appends to the value
 *  testSessionIncrementValue       Creates a new session value then increments the value
 *  testSessionDeleteValue          Creates a new session value then deletes it
 *  testSessionRegenerate           Regenerates session id
 *  testSessionFingerprint          Validates session fingerprint in different situations
 *  testExceptionThrow              Validates exception handling by class
 */

namespace ChristopherL;


class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testSessionNoSettings()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require('cl_session.php');
        $session = new Session();
        $session->start();

        // Verify session lifespan falls between default times range of 60 & 600
        $current_time = date("U");
        $session_min = $current_time + 60;
        $session_max = $current_time + 600;
        $session_lifespan = $session->getValue('lifespan');
        $this->assertGreaterThanOrEqual($session_min, $session_lifespan);
        $this->assertLessThanOrEqual($session_max, $session_lifespan);

        // Verify default session name is in use
        $this->assertEquals(session_name(), 'clsession');

        // Verify default session cookie settings
        $array_details = session_get_cookie_params();
        $this->assertEquals($array_details['path'], '/');
        $this->assertEquals($array_details['domain'], 'localhost');
        $this->assertEquals($array_details['secure'], false);
        $this->assertEquals($array_details['httponly'], true);

        // Verify decoy value is in session array (decoy cookie in use)
        $session_dump = $session->dump(2);
        $this->assertTrue(array_key_exists('decoy_value', $session_dump['clValues']));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionCustomSettings()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $session_settings = array(
            'name'      => 'TestSession',
            'path'      => '/subdirectory',
            'domain'    => '.christopherl.com',
            'secure'    => true,
            'hash'      => 1,
            'decoy'     => false,
            'min'       => 150,
            'max'       => 200,
        );

        require('cl_session.php');
        $session = new Session($session_settings);
        $session->start();

        // Verify session lifespan falls between default times range of 60 & 600
        $current_time = date("U");
        $session_min = $current_time + 150;
        $session_max = $current_time + 200;
        $session_lifespan = $session->getValue('lifespan');
        $this->assertGreaterThanOrEqual($session_min, $session_lifespan);
        $this->assertLessThanOrEqual($session_max, $session_lifespan);

        // Verify default session name is in use
        $this->assertEquals(session_name(), 'TestSession');

        // Verify default session cookie settings
        $array_details = session_get_cookie_params();
        $this->assertEquals($array_details['path'], '/subdirectory');
        $this->assertEquals($array_details['domain'], '.christopherl.com');
        $this->assertEquals($array_details['secure'], true);
        $this->assertEquals($array_details['httponly'], true);

        // Verify decoy value isn't in session array (no decoy cookie in use)
        $session_dump = $session->dump(2);
        $this->assertFalse(array_key_exists('decoy_value', $session_dump['clValues']));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionValue()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require('cl_session.php');
        $session = new Session();
        $session->start();

        // Create a new session string value
        $session->setValue('my_variable', 'this is the value');

        // Verifiy creation of session value
        $this->assertEquals($session->getValue('my_variable'), 'this is the value');

        // Change session value
        $session->setValue('my_variable', 50);

        // Verifiy changed session value
        $this->assertEquals($session->getValue('my_variable'), 50);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionAppendValue()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require('cl_session.php');
        $session = new Session();
        $session->start();

        // Create a new session value
        $session->appValue('my_variable', 'this is the base value');

        // Verifiy creation of session value
        $this->assertEquals($session->getValue('my_variable'), 'this is the base value');

        // Append something to the value
        $session->appValue('my_variable', ' updated');

        // Verifiy changed session value
        $this->assertEquals($session->getValue('my_variable'), 'this is the base value updated');
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionIncrementValue()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require('cl_session.php');
        $session = new Session();
        $session->start();

        // Create a new session value
        $session->incValue('my_variable', 10);

        // Verifiy creation of session value
        $this->assertEquals($session->getValue('my_variable'), 10);

        // Append something to the value
        $session->incValue('my_variable', 5.5);

        // Verifiy changed session value
        $this->assertEquals($session->getValue('my_variable'), 15.5);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionDeleteValue()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require('cl_session.php');
        $session = new Session();
        $session->start();

        // Create a new session string value
        $session->setValue('my_variable', 'i exist');

        // Verifiy creation of session value
        $this->assertEquals($session->getValue('my_variable'), 'i exist');

        // Delete the value
        $session->dropValue('my_variable');

        // Verify decoy value isn't in session array (no decoy cookie in use)
        $session_dump = $session->dump(2);
        $this->assertFalse(array_key_exists('my_variable', $session_dump['clValues']));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionRegenerate()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require('cl_session.php');
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

        require('cl_session.php');
        $session = new Session();
        $session->start();

        // Verify fingerprint matches expected recipe
        $this->assertEquals($session->getValue('fingerprint'), sha1($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . session_id()));

        // Verify regenerating session id maintains valid fingerprint
        $session->regenerate();
        $this->assertEquals($session->getValue('fingerprint'), sha1($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . session_id()));

        // Randomly change either the user agent or ip address
        if (rand(0, 1) == 1) {
            $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows; U; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)';
        }
        else {
            $_SERVER['REMOTE_ADDR'] = '10.0.10.1';
        }

        // Verify that whatever we changed causes the fingerprint comparison to fail
        $this->assertNOTEquals($session->getValue('fingerprint'), sha1($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . session_id()));
    }

    /**
     * @runInSeparateProcess
     */
    public function testExceptionThrow()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require('cl_session.php');
        $session = new Session();
        $session->start();

        $missing_value = 'this should not change';

        // Try to retrieve a session value that doesn't exist
        try {
            $missing_value = $session->getValue('there_is_no_spoon');
        }

        // Catch the exception we should have thrown and verify the message is correct
        catch(\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Session Value Name');
        }

        // make sure our value hasn't changed
        finally {
            $this->assertEquals($missing_value, 'this should not change');
        }
    }
}