<?php
/**
 * Increment value of a session variable
 */

include('../src/Session.php');

$session = new Asdfdotdev\Session([
    'name'   => 'IncrementSession',
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
        Each page refresh will increment the session value by a random amount of between 0 and 10.
        Delete the session cookie, or close your browser, to generate a new session and reset the value to 0.
    </p>
HTML;


/**
 * Increment the session value.
 */

$increment_by = rand(0, 10);

$session->incValue(
    'my_var',
    $increment_by
);

$my_var = $session->getValue('my_var');


/**
 * Output results
 */
echo <<<HTML
    <hr>
    <h3>Session Variable</h3>
    <p>
        <b>Value:</b> {$my_var}
    </p>
    <p>
        <b>Incremented by:</b> {$increment_by}
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
