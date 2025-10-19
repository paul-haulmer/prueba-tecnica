<?php

namespace App\Http\Requests;

use App\Enums\PackageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageStatusRequest extends FormRequest
{
    /**
     * Método que autoriza la solicitud de actualización de estado.
     * @return bool
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Método que define las reglas de validación para actualizar el estado.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(PackageStatus::values())],
        ];
    }
}
