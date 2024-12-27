<?php

namespace LaravelDynamicApi\Exceptions;

use LaravelDynamicApi\Traits\EngineRequestFunctions;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    use EngineRequestFunctions;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Don't redirect the user to login page.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $requestUser = $this->updateRequestFromHandler(
            $request,
            JsonResponse::HTTP_UNAUTHORIZED,
            $exception->getMessage()
        );
        $this->saveFailedRequestFromHandler($requestUser, $request);
        return response()->json([
            'message' => $exception->getMessage(),
            'code' => JsonResponse::HTTP_UNAUTHORIZED,
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function handleApiRequestException($request, Throwable $exception)
    {
        // Validation error
        if ($exception instanceof ValidationException) {
            $requestUser = $this->updateRequestFromHandler(
                $request,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                $exception->getMessage()
            );
            $this->saveFailedRequestFromHandler($requestUser, $request);
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        $requestUser = $this->updateRequestFromHandler(
            $request,
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            $exception->getMessage()
        );
        $this->saveFailedRequestFromHandler($requestUser, $request);
        // Production error.
        if (config('app.debug')) {
            return parent::render($request, $exception);
        } else {
            return response()->json([
                'message' => 'Unexpected Exception. Try later.',
                'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
