# ChristopherL Session Class

The ChristopherL Session class endeavors to make it easy to use basic session best practices in PHP scripts.

* Regenerate session id at random intervals.
* Default to SHA1 for session hash
* Custom session naming
* Session fingerprint validation
* HTTPOnly session cookie
* Decoy PHPSESSID cookie
* Easy to create, manage, and destroy session values.
* Works in PHP 5.5-7.0

----
## Examples

Creating a session:
```
include('/path/to/cl_session.php');
$session = new ChristopherL\Session();
$session->start();
```

Creating a Session Variable
```
$session->setValue('my_variable','value');
```

Changing a Session Variable Value
```
//  Set New Value
$session->setValue('my_variable','new value');

//  Increment Value
$session->incValue('my_variable', 1);

//  Append to Value
$session->appValue('my_variable','appended to current value');

//  Hash Stored Value (SHA1)
$session->setValue('my_variable','value_to_hash', 1);
```

Force Session ID Regeneration
```
$session->regenerate();
```

Additional examples included in /examples

----
## License
cl_session is made available under the [LGPL](http://www.gnu.org/licenses/lgpl-2.1.html).