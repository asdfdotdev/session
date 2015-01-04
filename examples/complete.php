<?php
	//	Create Session
	include('../cl_session.php');
	$session_values = [
					'name'		=>	'CompleteSession',
					'path'		=>	'/',
					'domain'	=>	'localhost',
					'secure'	=>	false,
					'hash'		=>	1,
					'decoy'		=>	true,
					'min'			=>	5,
					'max'			=>	10
					];
	$session = new cl_session($session_values);
	$session->start();
	
	
	//	Populate Session Variables
	$session->setValue('boolean', true);
	$session->appValue('extending_string','littering and ');
	$session->incValue('count', 5);
	$session->setValue('random', (string)rand(0, 5000));
	$session->setValue('fixed','some value');
	
	
	// Session Variable Set Outside of Class
	$_SESSION['Outside'] = 'a session value set the old fashioned way';
	
	
	//	Retrieve Session Variables
	$boolean = $session->getValue('boolean');
	$extending = $session->getValue('extending_string');
	$incrementing = $session->getValue('count');
	$random = $session->getValue('random');
	$fixed = $session->getValue('fixed');
	
	
	//	Output Session Variables
	echo
	'<h3>Session Variables</h3>'.
	'<p>Boolean: '.($boolean ? 'is true' : 'is false').'</p>'.
	'<p>Extending: '.$extending.'</p>'.
	'<p>Incrementing: '.$incrementing.'</p>'.
	'<p>Random: '.$random.'</p>'.
	'<p>Fixed: '.$fixed.'</p>';
	
	
	//	Output Session Settings
	echo
	'<hr><h3>Session Settings</h3>'.
	'<p>Session ID: '.session_id().'</p>'.
	($session->getValue('decoy_value') != '' ? 'Decoy PHPSESSID ID: '.$session->getValue('decoy_value') : '').
	'<p>Current Time: '.date("U").'</p>'.
	'<p>Regenerate At: '.$session->getValue('lifespan').'</p>';
	
	
	//	Output Session Contents
	echo '<hr><h3>Debug</h3>';
	$session->dump();