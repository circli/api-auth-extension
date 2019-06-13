# Api Authentication Extension

Extension to help with basic api authentication

The package include 3 different authentication providers

1. [BasicAuth](#BasicAuth)
2. [AccessKey](#AccessKey)
3. Token


## BasicAuth

### Usage

```php
use Circli\ApiAuth\Repository\ArrayBasicAuthRepository;
use Circli\ApiAuth\Provider\BasicAuthProvider;
use Circli\ApiAuth\Middleware\ApiAuthenticationMiddleware;

$authRepository = new ArrayBasicAuthRepository(['admin' => 'password']);

$middleware = new ApiAuthenticationMiddleware(new BasicAuthProvider($authRepository));
```

## AccessKey

### Usage


```php
use Circli\ApiAuth\Repository\AccessKeyRepository;
use Circli\ApiAuth\Provider\AccessKeyProvider;
use Circli\ApiAuth\Middleware\ApiAuthenticationMiddleware;

$repository = new YourImplementationOfAccessKeyRepository();

$middleware = new ApiAuthenticationMiddleware(new AccessKeyProvider($repository));
```


## Token

### Usage


```php
use Circli\ApiAuth\Repository\AuthTokenRepository;
use Circli\ApiAuth\Provider\AuthTokenProvider;
use Circli\ApiAuth\Middleware\ApiAuthenticationMiddleware;

$repository = new YourImplementationOfAuthTokenRepository();

$middleware = new ApiAuthenticationMiddleware(new AuthTokenProvider($repository));
```
