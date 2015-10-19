ns-php/core
===========
A set of basic classes and functions for PHP

# Usage
```php
include_once ('path/to/ns-php-core/core.inc.php');
```
ns-php-core uses the PHP autoload feature to automatically load class files. 
Files containing function definitions have to be included manually

# File name policy

* A file name starting with a upper case letter contains class definition(s)
* A file name starting with a small case letter contains function definitions
* A file with the extension .inc.php must be included using `include`/`include_once`
* A file with the extension .php should use `require`/`require_once`
