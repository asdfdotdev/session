# Examples

The following outlines a few usage options.

### Creating a session

```
// Using Composer
require __DIR__ . '/vendor/autoload.php';
$session = new Asdfdotdev\Session();
$session->start();

// Direct
include('/path/to/Session.php');
$session = new Asdfdotdev\Session();
$session->start();
```

### Creating a Session Variable

```
$session->setValue('my_variable','value');
```

### Changing a Session Variable Value

```
//  Set New Value
$session->setValue('my_variable','new value');

//  Increment Value
$session->incValue('my_variable', 1);

//  Append to Value
$session->appValue('my_variable','appended to current value');

//  Hash Stored Value
$session->setValue('my_variable','value_to_hash', true);
```

### Force Session ID Regeneration

```
$session->regenerate();
```

## Included Examples

- [Basic Session Usage](./basic.php)
- [Append a Session Value](./append.php)
- [Hash a Session Value](./hashed)
- [Increment a Session Value](./increment)
- [Regenerate Session ID](./regenerate.php)
