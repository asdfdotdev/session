<?php
/**
 * Incrementing value of a session variable
 */

    //	Create Session
    require_once('../cl_session.php');

    $session_values = array(
        'name'		=>	'IncrementSession',
        'decoy'		=>	false,
    );
    $session = new ChristopherL\Session($session_values);
    $session->start();


    //	Create/Update Session Variable (Refresh the page to increment)
    $session->incValue('my_var', 5.3);


    //	Retrieve and output Session Variable
    $my_session_variable = $session->getValue('my_var');

    echo <<<HTML
        <h3>Session Variables</h3>
        <p>
            my_var: {$my_session_variable}
        </p>
HTML;


    //	Output Session Settings
    $session_id = session_id();
    $epoch_time = date("U");
    $session_lifespan = $session->getValue('lifespan');

    echo <<<HTML
        <hr>
        <h3>Session Settings</h3>
        <p>
            Session ID: {$session_id}
        </p>
        <p>
            Current Time: {$epoch_time}
        </p>
        <p>
            Regenerate At: {$session_lifespan}
        </p>
HTML;


    //	Output Session Contents
    $session_content = $session->dump();

    echo <<<HTML
        <hr>
        <h3>Debug</h3>
        <pre>{$session_content}</pre>
HTML;
