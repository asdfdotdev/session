<?php
/**
 * A complete session with lots of different values, including old timey style
 */

    //	Create Session
    require_once('../cl_session.php');

    $session_values = array(
        'name'		=>	'CompleteSession',
        'path'		=>	'/',
        'domain'	=>	'localhost',
        'secure'	=>	false,
        'hash'		=>	'sha512',
        'decoy'		=>	true,
        'min'       =>	5,
        'max'       =>	10
    );
    $session = new ChristopherL\Session($session_values);
    $session->start();

	//	Populate Session Variables
	$session->setValue('boolean', true);
	$session->appValue('extending_string','littering and ');
	$session->incValue('count', 5);
	$session->setValue('random', (string)rand(0, 5000));
	$session->setValue('fixed','some value');
    $session->setValue('array', array('one thing', 'two thing', 'has_key' => 'red thing', 'blue_thing' => array('text', false, 50)));
    $session->setValue('hashed', 'this is my string', true);
	
	// Session Variable Set Outside of Class
	$_SESSION['Outside'] = 'a session value set the old fashioned way';
	
	//	Retrieve and output Session Variables
	$boolean = ($session->getValue('boolean') ? 'is true' : 'is false');
	$extending = $session->getValue('extending_string');
	$incrementing = $session->getValue('count');
	$random = $session->getValue('random');
	$fixed = $session->getValue('fixed');
    $array = print_r($session->getValue('array'), true);
    $hashed = $session->getValue('hashed');
	
	
	//	Output Session Variables
    echo <<<HTML
        <h3>Session Variables</h3>
	    <p>
	        <b>boolean:</b> {$boolean}
        </p>
	    <p>
	        <b>extending:</b> {$extending}
        </p>
	    <p>
	        <b>incrementing:</b> {$incrementing}
        </p>
	    <p>
	        <b>random:</b> {$random}
        </p>
	    <p>
	        <b>fixed:</b> {$fixed}
        </p>
	    <p>
	        <b>array:</b> {$array}
        </p>
        <p>
            <b>hashed:</b> {$hashed}        
        </p>
	    <p>
	       <b>Outside:</b> {$_SESSION['Outside']}
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
