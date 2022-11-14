# Laravel Route Versioning

[中文](README.md) | [ENGLISH](README-EN.md)

这个包提供了Laravel下的根据Http请求头进行版本化路由功能。

## Features

- 可以指定请求头中的Version键（默认为`x-version`）。
- 支持数字版本或者数字化的SemVer版本（不支持含字母的SemVer版本如"2.0.0-rc1"）。
- 版本回落。
- Laravel路由缓存。直接使用`artisan route:cache`。
- 支持 Laravel v9.x。

## 安装

```bash
composer require dongivan/laravel-route-versioning
```

## 使用

### 插入容器

使用这个包需要修改 `bootstrap/app.php` 以获得对容器中 "router" 的控制。 
```diff
<?php
$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

+ $app->singleton(
+    "router",
+    function ($app) {
+        return new \Dongivan\RouteVersioning\Router($app['events'], $app);
+    }
+ );

return $app;
```

实际上，这一步需要在 `Kernel` 得到初始化之前完成（一般发生在 `public/index.php` 文件中），否则当 `Kernel` 由容器创建时，默认的 `Illuminate\Routing\Router` 将被初始化。

### 为路由设置版本

当 `\Dongivan\RouteVersioning\Router` 被插入容器后，Facade `Router` 可以使用`version`。
```php
<?php
// api.php

use \Illuminate\Support\Facades\Route;

Route::version(1)->get("users", [App\Http\Controllers\UsersController::class, "index"])->name("v1:users.index");
```

*使用`\Dongivan\RouteVersioning\Facades\Route`来代替`\Illuminate\Support\Facades\Route`的话可以得到IDE的更多提示。*

### 设置字符串版本

```php
Route::version("v2.1")->get("users", [App\Http\Controllers\V2_1\UsersController::class, "index"])->name("v2.1:users.index");
```
设置字符串版本时，可以设置为 `vX.Y.Z` 或者 `X.Y.Z` 这样的格式。但是不支持其它格式，如 `v2.0.0-rc1`、 `v2.1.3.5`。

### 为Group设置版本

```php
Route::version("v1")->name("v1:posts.")->group(function() {
    Route::get("posts", [App\Http\Controllers\PostsController::class, "index"])->name("index")
    Route::post("posts", [App\Http\Controllers\PostsController::class, "store"])->name("store")
    Route::put("posts/{post}", [App\Http\Controllers\PostsController::class, "update"])->name("update")
});
```

### 为Resource设置版本

```php
Route::version("v2")->group(function() {
    Route::resource("comments", App\Http\Controllers\V2\CommentsController::class)->only(["store", "index"]);
})
```

### 不设置版本

没有设置版本的路由将被认为有一个null值的版本，而这个版本在版本回落时将拥有最低优先级。

## 配置文件

如果需要修改请求头中Version的键或者修改版本回落策略，可以创建一个 `config/route-version.php` 文件，并写入以下内容：
```php
<?php

return [
    "headerKey" => "X-VERSION",
    "strict" => false,
];
```
- `headerKey` 用于设置请求头中的Version键；
- `strict` 用于设置版本回落策略：true 不进行版本回落，false 进行版本回落；
- 如果没有这个文件，将以 `headerKey = "x-version"` 、 `strict = false` 为默认值。

## 版本回落

### `config("route-version.strict") === false`

| x-version: | null | v1 | v1.2 | v1.5 | v1.8 | v1.10 | v1.15 | v2 | v3 |
|-|-|-|-|-|-|-|-|-|-|
|Route::get()|-|-|-|-|-|-|-|-|-|
|Route::version("v1")->get()|-|✓|✓|-|-|-|-|-|-|
|Route::version("v1.5")->get()|-|-|-|✓|✓|-|-|-|-|
|Route::version("v1.10")->get()|-|-|-|-|-|✓|✓|-|-|
|Route::version("v2")->get()|✓|-|-|-|-|-|-|✓|✓|

*小心：没有版本的路由将回落至最高版本。*

### `config("route-version.strict") === true`

| x-version: | null | v1 | v1.2 | v1.5 | v1.8 | v1.10 | v1.15 | v2 | v3 |
|-|-|-|-|-|-|-|-|-|-|
|Route::get()|✓|-|-|-|-|-|-|-|-|
|Route::version("v1")->get()|-|✓|-|-|-|-|-|-|-|
|Route::version("v1.5")->get()|-|-|-|✓|-|-|-|-|-|
|Route::version("v1.10")->get()|-|-|-|-|-|✓|-|-|-|
|Route::version("v2")->get()|-|-|-|-|-|-|-|✓|-|

*小心：如果 `strict` 设置为 true 则将不进行版本回落。*

## 缓存

请使用 artisan 来完成：
```bash
artisan route:cache
```

Laravel 使用了 Symfony 来对路由进行编译，所以为了解析缓存路由的版本比较，我们需要库 `symfony/expression-language` 。

## 协议

MIT 许可 (MIT).