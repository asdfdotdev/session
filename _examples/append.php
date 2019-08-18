<?php
/**
 * Append values to a session variable.
 */

include('../src/Session.php');

$session = new Asdfdotdev\Session([
    'name'   => 'AppendSession',
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
        Each page refresh will append to both the array and string session values.
        Delete the session cookie, or close your browser, to generate a new session and reset the values.
    </p>
HTML;


/**
 * Increment the session value.
 */

$session->appValue('my_var_string', 'text and more ');
$session->appValue('my_var_array', [rand()]);

$my_var_string = $session->getValue('my_var_string');
$my_var_array = print_r(
    $session->getValue('my_var_array'),
    true
);


/**
 * Output results
 */
echo <<<HTML
    <hr>
    <h3>Session Variable</h3>
    <p>
        <b>String:</b> {$my_var_string}
    </p>
    <p>
        <b>Array:</b> {$my_var_array}
    </p>
HTML;


/**
 * Debugging
 */
$session_content = $session->dump();
echo <<<HTML
    <hr>
    <h3>Debug</h3>
    <pre style="overflow:scroll;">{$session_content}</pre>
HTML;
