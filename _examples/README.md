todo: fix it

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
