<?php
	//	Create Session
	include('../cl_session.php');
	$session_values = [
					'name'		=>	'RegenSession',
					'decoy'		=>	false
					];
	$session = new cl_session($session_values);
	$session->start();
	
	
	//	Regenerate Session
	$session->regenerate();
	
	
	//	Populate Session Variable
	$session->setValue('my_var','some value');
	
	
	//	Retrieve Session Variable
	$my_session_variable = $session->getValue('my_var');
	
	
	//	Output Session Variable
	echo '<h3>Session Variables</h3>'.
	'<p>my_var: '.$my_session_variable.'</p>';
	
	
	//	Output Session Settings
	echo
	'<hr><h3>Session Settings</h3>'.
	'<p>Session ID: '.session_id().'</p>';
	
	
	//	Output Session Contents
	echo '<hr><h3>Debug</h3>';
	$session->dump();