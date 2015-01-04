<?php
	//	Create Session
	include('../cl_session.php');
	$session_values = [
					'name'		=>	'IncrementSession',
					'decoy'		=>	false
					];
	$session = new cl_session($session_values);
	$session->start();
	
	
	//	Populate Session Variable
	$session->incValue('my_var', 5);
	
	
	//	Retrieve Session Variable
	$my_session_variable = $session->getValue('my_var');
	
	
	//	Output Session Variable
	echo '<h3>Session Variables</h3>'.
	'<p>my_var: '.$my_session_variable.'</p>';
	
	
	//	Output Session Settings
	echo
	'<hr><h3>Session Settings</h3>'.
	'<p>Session ID: '.session_id().'</p>'.
	'<p>Current Time: '.date("U").'</p>'.
	'<p>Regenerate At: '.$session->getValue('lifespan').'</p>';
	
	
	//	Output Session Contents
	echo '<hr><h3>Debug</h3>';
	$session->dump();