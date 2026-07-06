<?php

return [

    'validation' => [
        'username_regex' => 'El usuario solo puede contener letras y numeros (sin espacios ni simbolos).',
        'username_max' => 'El usuario no puede tener mas de 16 caracteres: es el limite del cliente de World of Warcraft 3.3.5a.',
        'password_regex' => 'La contraseña solo puede contener caracteres ASCII imprimibles (sin acentos ni ñ).',
        'password_max' => 'La contraseña no puede tener mas de 16 caracteres: es el limite del cliente de World of Warcraft 3.3.5a.',
    ],

    'register' => [
        'success' => '¡Cuenta creada! Estamos preparando tu personaje en cada reino, puedes ver el progreso en el panel.',
    ],

    'verify_email' => [
        'subject' => 'Verifica tu correo electronico',
        'greeting' => '¡Hola, :name!',
        'line' => 'Por favor confirma tu correo electronico haciendo clic en el siguiente boton.',
        'action' => 'Verificar correo electronico',
        'footer' => 'Si no creaste esta cuenta, puedes ignorar este mensaje.',
        'verified' => 'Tu correo fue verificado correctamente.',
    ],

    'forgot_password' => [
        'sent' => 'Te enviamos un enlace para restablecer tu contraseña.',
    ],

    'reset_password' => [
        'subject' => 'Restablece tu contraseña',
        'greeting' => '¡Hola, :name!',
        'line' => 'Recibimos una solicitud para restablecer tu contraseña.',
        'action' => 'Restablecer contraseña',
        'expires' => 'Este enlace expirara en 60 minutos.',
        'footer' => 'Si no solicitaste esto, puedes ignorar este mensaje. Tu contraseña actual seguira funcionando tanto en el panel como en el juego.',
        'success' => 'Tu contraseña fue actualizada. Tambien la actualizamos en cada reino habilitado.',
    ],

];
