<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Basis FormRequest API: otorisasi default true.
 * Payload error divalidasi dipetakan seragam oleh exception handler di bootstrap/app.php.
 */
abstract class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
