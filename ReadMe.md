# cl_session Class

The cl_session class endeavors to make it easy to use basic session best practices in PHP scripts.

* Regenerate session id at random intervals.
* Default to SHA1 for session hash
* Custom session naming
* Session fingerprint checking
* HTTPOnly session cookie
* Decoy PHPSESSID cookie
* Easy to create, manage & destroy session values.

----
## Examples

Creating a session:
```
include('/path_to_class/cl_session.php');
$session_settings = [
				'name'		=>	'mySession',
				'path'		=>	'/',
				'domain'	=>	'localhost',
				'secure'	=>	false,
				'hash'		=>	1,
				'decoy'		=>	true
				];
$session = new cl_session($session_settings);
$session->start();
```

Creating a Session Variable
```
$session->setValue('my_variable','value');
```

Changing a Session Variable Value
```
//	Set New Value
$session->setValue('my_variable','new value');

//	Increment Value
$session->incValue('my_variable', 1);

//	Append to Value
$session->appValue('my_variable','appended to current value');

//	Hash Stored Value (SHA1)
$session->setValue('my_variable','value_to_hash', 1);
```

Force Session ID Regeneration
```
$session->regenerate();
```

Additional examples included in /examples
----
## License
cl_session is made available under the [GPL](http://www.gnu.org/licenses/gpl-2.0.html).