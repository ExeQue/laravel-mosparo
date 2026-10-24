<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Workbench\App\Http\Controllers\NewsletterController;
use Workbench\App\Livewire\Rejected;
use Workbench\App\Livewire\SignIn;

Route::view('/', 'index');

Route::get('/livewire', SignIn::class);
Route::get('/rejected', Rejected::class);
Route::view('/visible', 'visible');

Route::get('/form', [NewsletterController::class, 'show']);
Route::post('/form', [NewsletterController::class, 'store']);
