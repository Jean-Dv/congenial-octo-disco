<?php

return [

    'validation' => [
        'username_regex' => 'Username can only contain letters and numbers (no spaces or symbols).',
        'username_max' => 'Username cannot be longer than 16 characters: that is the limit of the World of Warcraft 3.3.5a client.',
        'password_regex' => 'Password can only contain printable ASCII characters.',
        'password_max' => 'Password cannot be longer than 16 characters: that is the limit of the World of Warcraft 3.3.5a client.',
    ],

    'register' => [
        'success' => 'Account created! We are setting up your character on every realm, you can track progress on the dashboard.',
    ],

    'login' => [
        'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    ],

    'verify_email' => [
        'subject' => 'Verify your email address',
        'greeting' => 'Hello, :name!',
        'line' => 'Please confirm your email address by clicking the button below.',
        'action' => 'Verify email address',
        'footer' => 'If you did not create this account, no further action is required.',
        'verified' => 'Your email was verified successfully.',
    ],

    'forgot_password' => [
        'sent' => 'We emailed you a link to reset your password.',
    ],

    'reset_password' => [
        'subject' => 'Reset your password',
        'greeting' => 'Hello, :name!',
        'line' => 'We received a request to reset your password.',
        'action' => 'Reset password',
        'expires' => 'This link will expire in 60 minutes.',
        'footer' => 'If you did not request this, you can safely ignore this email. Your current password will keep working both on the panel and in-game.',
        'success' => 'Your password was updated. We also pushed it to every enabled realm.',
    ],

];
