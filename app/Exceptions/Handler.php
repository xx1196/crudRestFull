<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Exception $exception
     * @return Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ValidationException)
            return $this->convertValidationExceptionToResponse($exception, $request);

        if ($exception instanceof ModelNotFoundException) {
            $model = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse("No existe resultados de $model", 404);
        }

        if ($exception instanceof NotFoundHttpException)
            return $this->errorResponse("No existe la ruta especificada", 404);

        if ($exception instanceof AuthenticationException)
            return $this->unauthenticated($request, $exception);

        if ($exception instanceof AuthorizationException)
            return $this->errorResponse('No posee permisos para esta acción', 403);

        if ($exception instanceof MethodNotAllowedHttpException)
            return $this->errorResponse('No es valido este verbo HTTP para esta ruta', 405);

        if ($exception instanceof HttpException)
            return $this->errorResponse($exception->getMessage(), $exception->getCode());

        if ($exception instanceof QueryException) {
            $code = $exception->errorInfo[1];
            if ($code === 1451)
                return $this->errorResponse('Este recurso ya esta relacionado con otros', 409);
        }

        if (!config('app.debug'))
            return $this->errorResponse('No eres tu somos nosotros, disculpa las molestias generadas estamos trabajando para arreglarlo');

        return parent::render($request, $exception);
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param ValidationException $e
     * @param Request $request
     * @return Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        return $this->errorResponse($e->validator->errors()->getMessages(), 422);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->errorResponse('No te encuentras logeado en nuestro sistema', 401);
    }

}
