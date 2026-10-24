<?php

declare(strict_types=1);

namespace Workbench\App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Workbench\App\Http\Requests\SubscribeRequest;

class NewsletterController
{
    public function show(): View
    {
        return view('form');
    }

    public function store(SubscribeRequest $request): RedirectResponse
    {
        return back()->with('result', "Subscribed {$request->validated('email')}.");
    }
}
