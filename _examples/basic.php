<?php
/**
 * A simple session with a single value.
 */

include('../src/Session.php');

$session = new Asdfdotdev\Session([
    'name'   => 'BasicSession',
    'decoy'  => false,
    'min'    => 5,
    'max'    => 10,
    'debug'  => true,
]);
$session->start();


/**
 * About this example.
 */

echo <<<HTML
    <h3>About this Example</h3>
    <p style="max-width:750px;">
        On initial creation a random number will be appended to the <code>my_var</code>
        string. In subsequent page refreshes this number will not change as it will be
        retrieved from the saved session value. Delete the session cookie, or close your
        browser, to generate a new session and value.
    </p>
HTML;


/**
 * Create, or retrieve, the session variable.
 */
$my_var = $session->getValue('my_var');
if (!isset($my_var)) {
    $session->setValue(
        'my_var',
        'A string that ends in a random number. ' . rand()
    );
    $my_var = $session->getValue('my_var');
    $result = 'Created session variable.';
} else {
    $result = 'Retrieved existing session variable.';
}


/**
 * Output results
 */
echo <<<HTML
    <hr>
    <h3>Session Variable</h3>
    <p>
        <b>Status:</b> {$result}
    </p>
    <p>
        <b>Value:</b> {$my_var}
    </p>
HTML;


/**
 * Debugging
 */
$session_content = $session->dump();
echo <<<HTML
    <hr>
    <h3>Debug</h3>
    <pre>{$session_content}</pre>
HTML;
