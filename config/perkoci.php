<?php

return [

    'login' => [
        'status_label'    => 'Sistem Inventori & POS Terhubung • Online',
        'title'           => 'Masuk Panel Administrator',
        'subtitle'        => 'Sistem Manajemen Inventori, Katalog Menu & Konfigurasi QR Meja Perkoci Eatery.',
        'role_label'      => 'Role: Master Administrator & Inventory Controller',

        'email_label'     => 'ID Administrator / Email',
        'email_hint'      => 'Kredensial SSO Aktif',
        'password_label'  => 'Kata Sandi',
        'forgot_label'    => 'Lupa Kredensial?',
        'remember_label'  => 'Ingat Saya di perangkat terminal ini',
        'submit_label'    => 'Masuk ke Panel Admin',

        'info_title'      => 'Perkoci Admin Suite v2.4.0',
        'info_text'       => 'Akses terbatas hanya untuk staf berwenang dan terdaftar.',

        'footnote' => [
            ['icon' => 'shield-check', 'text' => '256-Bit SSL Encrypted'],
            ['icon' => 'database',     'text' => 'MySQL Database Connected'],
        ],

        // The 2FA boxes are UI-only for now (not verified server-side).
        'show_two_factor' => (bool) env('LOGIN_SHOW_2FA', true),
    ],

];
