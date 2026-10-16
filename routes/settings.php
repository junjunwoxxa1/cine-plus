<?php

use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/settings', fn () => redirect()->route('profile.edit'));
    Route::get('/settings/profile', Profile::class)->name('profile.edit');
    Route::get('/settings/security', fn () => redirect()->route('profile.edit'))->name('security.edit');
    Route::get('/settings/appearance', fn () => redirect()->route('profile.edit'))->name('appearance.edit');
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('profile.edit'),
        'manage' => route('profile.edit'),
    ]);
})->name('well-known.passkeys');
