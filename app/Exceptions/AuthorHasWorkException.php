<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Http\JsonResponse;

class AuthorHasWorkException extends Exception
{
    public function render(Request $request): JsonResponse {
        return response()->json([
            'message' => 'impossibile eliminare l\'autore: ha ancora opere associate',
        ], 409);
    }
}
