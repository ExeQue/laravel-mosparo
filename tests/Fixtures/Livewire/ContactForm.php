<?php

declare(strict_types=1);

namespace Tests\Fixtures\Livewire;

use Livewire\Form;

class ContactForm extends Form
{
    public string $email = '';

    public string $message = '';

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'message' => ['required', 'string'],
        ];
    }
}
