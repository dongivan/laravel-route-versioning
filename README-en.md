# Laravel Route Versioning

[中文](README.md) | [ENGLISH](README-EN.md)

This package provides a way to versioning laravel routes by http request headers.

## Features

- Speicify versioning key in header (default as `x-version`).
- Support integer version or numeric SemVer version (SemVer version with letters like "2.0.0-rc1" is not supported).
- Version fallback.
- Laravel route caching. Just use `artisan route:cache`.
- Support Laravel v9.x .

## Install

```bash
composer require dongivan/laravel-route-versioning
```

## Usage

### Inject into the IoC container

Using this package we need to modify `bootstrap/app.php` to take control of the "router" in the IoC container. 
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

Actually this shoule be done before `Kernel` is initialized (which happens in `public/index.php` usually), or when `Kernel` is made by the IoC container the default `Illuminate\Routing\Router` would be initialized.

### Versioning a route

When `\Dongivan\RouteVersioning\Router` is injected, the Facade `Router` will have ability to `version` routes.
```php
<?php
// api.php

use \Illuminate\Support\Facades\Route;

Route::version(1)->get("users", [App\Http\Controllers\UsersController::class, "index"])->name("v1:users.index");
```

*Instead of `\Illuminate\Support\Facades\Route`, You can use `\Dongivan\RouteVersioning\Facades\Route` to get more IDE hints.*

### Using string version

```php
Route::version("v2.1")->get("users", [App\Http\Controllers\V2_1\UsersController::class, "index"])->name("v2.1:users.index");
```
When using string version, you can pass a string like `vX.Y.Z` or `X.Y.Z`. However other formats are not supported (e.g. `v2.0.0-rc1` or `v2.1.3.5`).

### Group versioning

```php
Route::version("v1")->name("v1:posts.")->group(function() {
    Route::get("posts", [App\Http\Controllers\PostsController::class, "index"])->name("index")
    Route::post("posts", [App\Http\Controllers\PostsController::class, "store"])->name("store")
    Route::put("posts/{post}", [App\Http\Controllers\PostsController::class, "update"])->name("update")
});
```

### Resource versioning

```php
Route::version("v2")->group(function() {
    Route::resource("comments", App\Http\Controllers\V2\CommentsController::class)->only(["store", "index"]);
})
```

### Without version

The route without version will be treated as version null which has lowest priority while fallbacking.

## Config File

To change the header key or fallback strategy you can create a file with name `config/route-version.php` and content:
```php
<?php

return [
    "headerKey" => "X-VERSION",
    "strict" => false,
];
```
- The `headerKey` provides the version key in the request headers.
- The `strict` provides fallback strategy: true to no version fallback, and false to fallback version from higher to lower.
- Without this config file this package will take `headerKey = "x-version"` and `strict = false` as default.

## Version Fallback

### `config("route-version.strict") === false`

| x-version: | null | v1 | v1.2 | v1.5 | v1.8 | v1.10 | v1.15 | v2 | v3 |
|-|-|-|-|-|-|-|-|-|-|
|Route::get()|-|-|-|-|-|-|-|-|-|
|Route::version("v1")->get()|-|✓|✓|-|-|-|-|-|-|
|Route::version("v1.5")->get()|-|-|-|✓|✓|-|-|-|-|
|Route::version("v1.10")->get()|-|-|-|-|-|✓|✓|-|-|
|Route::version("v2")->get()|✓|-|-|-|-|-|-|✓|✓|

*Becareful: the route without version will fallback to highest version.*

### `config("route-version.strict") === true`

| x-version: | null | v1 | v1.2 | v1.5 | v1.8 | v1.10 | v1.15 | v2 | v3 |
|-|-|-|-|-|-|-|-|-|-|
|Route::get()|✓|-|-|-|-|-|-|-|-|
|Route::version("v1")->get()|-|✓|-|-|-|-|-|-|-|
|Route::version("v1.5")->get()|-|-|-|✓|-|-|-|-|-|
|Route::version("v1.10")->get()|-|-|-|-|-|✓|-|-|-|
|Route::version("v2")->get()|-|-|-|-|-|-|-|✓|-|
*Becareful: there would be no fallback when `strict` is set to true.*

## Cache

Please use artisan to do this job:
```bash
artisan route:cache
```

Laravel uses Symfony to compile its routes, so we needs `symfony/expression-language` to resolve the version comparison in cached routes.

## License

The MIT License (MIT).