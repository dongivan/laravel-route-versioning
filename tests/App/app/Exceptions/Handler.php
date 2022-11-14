<?php

namespace Tests\App\App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        /*
            render Symfony\Component\HttpKernel\Exception\HttpException,
            see Illuminate\Foundation\Exceptions\Handler line 363
        */
        $this->renderable(function (Throwable $e, $request) {
            if (!$this->shouldReturnJson($request, $e)) {
                return;
            }

            if ($e instanceof HttpException) {
                new \Illuminate\Http\JsonResponse(
                    $this->convertExceptionToArray($e),
                    $this->isHttpException($e) ? $e->getStatusCode() : 500,
                    $this->isHttpException($e) ? $e->getHeaders() : [],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                );
            }
        });
    }

    /**
     * Get default message by http exception status.
     *
     * @param int $statusCode
     * @return string
     */
    protected function getHttpExceptionMessage($statusCode)
    {
        return [
            404 => "未找到指定页面",
            500 => "服务器内部错误",
        ][$statusCode] ?? "未知错误($statusCode)";
    }

    /**
     * Convert an authentication exception into a response.
     * render Illuminate\Auth\AuthenticationException,
     * see Illuminate\Foundation\Exceptions\Handler line 364
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->shouldReturnJson($request, $exception)
            ? response()->json(["statusCode" => 401, "message" => $exception->getMessage() ?: $this->getHttpExceptionMessage(401)], 401)
            : redirect()->guest($exception->redirectTo() ?? route("login"));
    }

    /**
     * Convert a validation exception into a JSON response.
     * render Illuminate\Auth\AuthenticationException,
     * see Illuminate\Foundation\Exceptions\Handler line 365 / 473 / 505
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'statusCode' => $exception->status,
            'message' => $exception->getMessage() ?: $this->getHttpExceptionMessage($exception->status),
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Convert the given exception to an array.
     *
     * render Throwable,
     * see Illuminate\Foundation\Exceptions\Handler line 366 / 455 / 686

     * @param  \Throwable  $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        $statusCode = method_exists($e, "getStatusCode") ? call_user_func([$e, "getStatusCode"]) : 500;
        return [
            'statusCode' => $statusCode,
            'message' => $e->getMessage() ?: $this->getHttpExceptionMessage($statusCode),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
        ];
    }
}
