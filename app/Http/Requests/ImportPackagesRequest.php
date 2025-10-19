<?php

namespace App\Http\Requests;

use App\Enums\PackagePriority;
use App\Enums\PackageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportPackagesRequest extends FormRequest
{
    /**
     * Método que autoriza la ejecución de la solicitud de importación.
     * @return bool
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Método que define las reglas de validación para la importación de paquetes.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function rules(): array
    {
        return [
            'packages' => ['required', 'array', 'min:1'],
            'packages.*.id' => ['required', 'string', 'distinct'],
            'packages.*.priority' => ['required', 'string', Rule::in(PackagePriority::values())],
            'packages.*.status' => ['required', 'string', Rule::in(PackageStatus::values())],
            'packages.*.imported_at' => ['required', 'date'],
        ];
    }
}
