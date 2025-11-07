<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {

            $status = 500;
            $message = 'Gözlənilməz xəta baş verdi.';

            switch (true) {

                case $e instanceof ValidationException:
                    $status = 422;
                    $message = collect($e->errors())->flatten()->first() ?? 'Məlumat doğrulama xətası.';
                    break;

                case $e instanceof AuthenticationException:
                    $status = 401;
                    $message = 'İcazəsiz giriş.';
                    break;

                case $e instanceof NotFoundHttpException:
                    $status = 404;
                    $message = 'Sorğu edilən resurs tapılmadı.';
                    break;

                case $e instanceof QueryException:
                    $status = 400;
                    $message = 'Verilənlər bazası xətası: ' . $e->getMessage();
                    break;

                case $e instanceof PostTooLargeException:
                    $status = 413;
                    $message = 'Yüklənən fayl həcmi icazə veriləndən böyükdür.';
                    break;

                case $e instanceof ExcelValidationException:
                    $status = 422;
                    $message = 'Excel validasiya xətası: ' . $e->getMessage();
                    break;

                case $e instanceof PhpSpreadsheetException:
                    $status = 422;
                    $message = 'Excel faylı oxunarkən xəta baş verdi. Fayl korlanmış və ya düzgün formatda deyil.';
                    break;

                case $e instanceof \Exception:
                    $status = 422;
                    $message = $e->getMessage();
                    break;
            }

            Log::channel('daily')->error('Excel/XML error', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user' => optional($request->user())->id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $e instanceof ValidationException ? $e->errors() : null,
            ], $status);
        }

        return parent::render($request, $e);
    }
}
