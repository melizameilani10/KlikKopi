<?php

return [

    'login' => [
        'status_label'      => 'Sistem Terhubung • Online',
        'title'             => 'Masuk ke Sistem Perkoci',
        'subtitle'          => 'Satu halaman masuk untuk Administrator, Manajer, dan Kasir Perkoci Eatery.',

        'email_label'       => 'ID Pengguna / Email',
        'email_hint'        => 'Email atau username',
        'email_placeholder' => 'nama@perkoci.id atau username',
        'password_label'    => 'Kata Sandi',
        'forgot_label'      => 'Lupa Kata Sandi?',
        'remember_label'    => 'Ingat sesi di perangkat ini',
        'submit_label'      => 'Masuk',

        'info_title'        => 'Perkoci Suite v2.4.0',
        'info_text'         => 'Akses terbatas hanya untuk staf berwenang dan terdaftar.',

        'footnote' => [
            ['icon' => 'shield-check', 'text' => '256-Bit SSL Encrypted'],
            ['icon' => 'database',     'text' => 'MySQL Database Connected'],
        ],
    ],

    // Daftar role yang boleh masuk. 'route' = dashboard tujuan setelah login.
    'roles' => [
        'admin' => [
            'label' => 'Administrator',
            'route' => 'admin.dashboard',
            'icon'  => 'shield-check',
        ],
        'manager' => [
            'label' => 'Manajer',
            'route' => 'manager.dashboard',
            'icon'  => 'id-card',
        ],
        'kasir' => [
            'label' => 'Kasir',
            'route' => 'kasir.dashboard',
            'icon'  => 'lock',
        ],
    ],

];
