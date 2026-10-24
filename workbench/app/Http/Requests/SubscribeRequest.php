<?php

declare(strict_types=1);

namespace Workbench\App\Http\Requests;

use ExeQue\Mosparo\Rules\ValidMosparo;
use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            '_mosparo_submitToken' => [new ValidMosparo()],
        ];
    }
}
