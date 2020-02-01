# DSSO Client PHP Helper

## Support version

![PHP5.6](https://img.shields.io/badge/php-5.6-green.svg?style=flat-square&logo=php&colorB=orange)
![PHP7.0](https://img.shields.io/badge/php-7.0-green.svg?style=flat-square&logo=php&colorB=orange)
![PHP7.1](https://img.shields.io/badge/php-7.1-green.svg?style=flat-square&logo=php&colorB=orange)
![PHP7.2](https://img.shields.io/badge/php-7.2-green.svg?style=flat-square&logo=php&colorB=orange)
![PHP7.3](https://img.shields.io/badge/php-7.3-green.svg?style=flat-square&logo=php&colorB=orange)
![PHP7.4](https://img.shields.io/badge/php-7.4-green.svg?style=flat-square&logo=php&colorB=orange)

## Get Started

Please first clone this repository and require lib/DSSOClient.class.php like:

```php
require_once(dirname(__FILE__).'/class/DSSOClient.class.php');
```

### Initialization

**Create SSO client instance**

Run like:
```php
$SSO = new DSSOClient('id.example.org'); // Minimum initialization
```
Or full parameter support like:
```php
$SSO = new DSSOClient('id.example.org', 443, 'https');
```

**Configuration**

Run:
```php
define('APP_ID', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
define('SECRET_KEY', 'xxxxxxxxxx');
define('REDIRECT_URI', 'http://www.yoursite.com/sso/login');
$SSO->config(REDIRECT_URI, APP_ID, SECRET_KEY);
```

**Start authentication and get user information**

Run:
```php
$User = $SSO->authorize();
print_r($User);
```

**$User array' s struct**

```php
Array
(
    [status]    =>  'ok|fail', //OK for success and fail for failure
    [data]  =>  'array|undefined', //Success will return an array, otherwise will not have this field
    [errId] =>  'int|undefined', //Success will not have this field , otherwise will return error number
    [msg]   =>  'string|undefined' //Success will not have this field , otherwise will return error message
)
```

**Error number guide**
```java
201: Security policy limited, need change password first. (User Problem)
400: Client configuration check failed. (Application Problem)
401: Can not entry current application. (User Problem)
403: Token expired. (Not explicitly)
405: Authentication process timeout, need login again. (Not explicitly)
50x: Detect RPC error. (IDP Server Problem)
```