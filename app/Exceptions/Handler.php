<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'status' => false,
                'error' => 'Method not supported'
            ], 419);
        });


        $this->renderable(function (NotFoundHttpException $e, $request) {
            if($request->is('api/*')){
                return response()->json([
                    'status' => false,
                    'error' => 'Not Found'
                ], 404);
            }
        });
    }

    public function shouldReturnJson($request, $exception){
        return true;
    }
}
