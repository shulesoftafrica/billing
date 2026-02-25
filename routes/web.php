<?php

use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', function () {
    return view('home');
})->name('home');

// Documentation
Route::get('/docs', function () {
    return view('docs.index');
})->name('docs');

// Legal Pages
Route::get('/privacy', function () {
    return view('legal.privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('legal.terms');
})->name('terms');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', function () {
        // Handle login logic
        return redirect()->route('dashboard');
    });
    
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');
    
    Route::post('/register', function () {
        // Handle registration logic
        return redirect()->route('dashboard');
    });
    
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    
    Route::post('/forgot-password', function () {
        // Handle forgot password logic
        return back()->with('success', 'Password reset link sent!');
    })->name('password.email');
});

// Dashboard Routes (Protected)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');
    
    // Settings
    Route::get('/settings', function () {
        return view('dashboard.settings');
    })->name('settings');
    
    Route::put('/settings/profile', function () {
        return back()->with('success', 'Profile updated successfully!');
    })->name('settings.profile.update');
    
    Route::put('/settings/password', function () {
        return back()->with('success', 'Password updated successfully!');
    })->name('settings.password.update');
    
    Route::put('/settings/company', function () {
        return back()->with('success', 'Company information updated!');
    })->name('settings.company.update');
    
    Route::put('/settings/address', function () {
        return back()->with('success', 'Address updated successfully!');
    })->name('settings.address.update');
    
    Route::put('/settings/notifications', function () {
        return back()->with('success', 'Notification preferences updated!');
    })->name('settings.notifications.update');
    
    // Customers
    Route::get('/customers', function () {
        return view('dashboard.customers.index');
    })->name('web.customers.index');
    
    Route::get('/customers/create', function () {
        return view('dashboard.customers.create');
    })->name('web.customers.create');
    
    // Subscriptions
    Route::get('/subscriptions', function () {
        return view('dashboard.subscriptions.index');
    })->name('web.subscriptions.index');
    
    Route::get('/subscriptions/create', function () {
        return view('dashboard.subscriptions.create');
    })->name('web.subscriptions.create');
    
    // Invoices
    Route::get('/invoices', function () {
        return view('dashboard.invoices.index');
    })->name('web.invoices.index');
    
    Route::get('/invoices/create', function () {
        return view('dashboard.invoices.create');
    })->name('web.invoices.create');
    
    // Payments
    Route::get('/payments', function () {
        return view('dashboard.payments.index');
    })->name('web.payments.index');
    
    // API Keys
    Route::get('/api-keys', function () {
        return view('dashboard.api-keys.index');
    })->name('web.api-keys.index');
    
    // Webhooks
    Route::get('/webhooks', function () {
        return view('dashboard.webhooks.index');
    })->name('web.webhooks.index');
    
    // Logs
    Route::get('/logs', function () {
        return view('dashboard.logs.index');
    })->name('web.logs.index');
    
    // Logout
    Route::post('/logout', function () {
        // Handle logout logic
        return redirect()->route('home');
    })->name('logout');
});

