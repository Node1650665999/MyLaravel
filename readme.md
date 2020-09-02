
## JWT
### [安装与配置](https://learnku.com/articles/10885/full-use-of-jwt)
#### 安装
```php
composer require tymon/jwt-auth 1.*@rc
```

#### 生成 config/jwt.php 配置文件
```php
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

#### 生成加密密钥
会在 .env 文件下生成一个加密密钥，如：JWT_SECRET=foobar。
```php
php artisan jwt:secret
```

#### 注册中间件
```php
    protected $routeMiddleware = [
        ....
        'auth.jwt' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
    ];
```


#### 修改 guards 的 driver
```php
// config/auth.php
'guards' => [
    'web' => [
        'driver'   => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver'   => 'jwt',        // 原来是 token 改成 jwt
        'provider' => 'users',
    ],
]
```
此外，如果想实现多表认证，可以这样配置：
```php
return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'admin'),
    ],

    'guards' => [
        'admin' => [
            'driver' => 'jwt',                           #### 更改为JWT驱动
            'provider' => 'admins',
        ],
        'user' => [
            'driver' => 'jwt',                           #### 更改为JWT驱动
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model'  => \App\Admin::class,        #### 指定用于token验证的模型类
        ],
        'users' => [
            'driver' => 'eloquent',
            'model'  => \App\User::class,        #### 指定用于token验证的模型类
        ],
    ],

    'passwords' => [
        //
    ],
];
```
之后如果想获取认证用户的信息的话，传入对应的 $guard 就可以了：
```php
Auth::guard($guard)->user();
```

#### 自定义异常处理
laravel 对于未认证此类异常通常返回 401,这里我们自定义下异常处理逻辑。
```php
//文件位于 app/Exceptions/Handler.php

public function render($request, Exception $exception)
{
    //return parent::render($request, $exception);
    $data = [
        'code'  => 500,
        'msg'   => $exception->getMessage(),
        'data'  => []
    ];
    return response()->json($data);
}
```

### 相关API
#### 获取用户
如下这几种方法都可以获取用户信息。
```php
//guard 指的是config/auth.php 中 guard 选项中的 web/api/...
$guard = 'api';

$user = Auth::guard($guard)->user();

$user = auth($guard)->user();

$user = JWTAuth::authenticate($token);

$user = JWTAuth::parseToken()->authenticate();
```

#### 解析token
```php
// 辅助函数
$token = auth($guard)->getToken();

// Facade
$token = JWTAuth::parseToken()->getToken();

// 从 request 中解析 token 到对象中，以便进行下一步操作
JWTAuth::parseToken();

//从 request 中获取 token,这个不用 parseToken ，因为方法内部会自动执行一次
$token = JWTAuth::getToken();

```

#### 刷新token
```php
$newToken = JWTAuth::parseToken()->refresh();
```

#### 检测token
```php
JWTAuth::parseToken()->check();
```

#### 销毁token
```php

$guard = 'api'; //web/api

JWTAuth::invalidate($token);

auth($guard)->logout();
```

