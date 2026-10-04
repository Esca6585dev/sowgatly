<?php

// Texts the API sends to the apps (section titles, notification texts).
return [
    'home_delivery_today' => 'Şu gün eltip bermek',
    'home_popular' => 'Meşhur harytlar',
    'home_new' => 'Täze gelenler',

    'order_status' => [
        'pending' => 'Garaşylýar',
        'processing' => 'Taýýarlanýar',
        'delivering' => 'Eltilýär',
        'completed' => 'Ýerine ýetirildi',
        'cancelled' => 'Ýatyryldy',
    ],
    'notifications' => [
        'order_created' => ['title' => 'Sargyt № :number kabul edildi', 'body' => 'Sargydyňyz dükana geçirildi. Tassyklanansoň habar bereris.'],
        'order_status' => ['title' => 'Sargyt № :number', 'body' => 'Sargydyň ýagdaýy: :status'],
        'order_status_processing' => ['title' => 'Sargyt № :number taýýarlanýar', 'body' => 'Dükan sargydyňyzy tassyklady we taýýarlamaga başlady.'],
        'order_status_delivering' => ['title' => 'Sargyt № :number ýolda', 'body' => 'Kurýer sargydyňyzy eltip barýar.'],
        'order_status_completed' => ['title' => 'Sargyt № :number ýerine ýetirildi', 'body' => 'Sargydyňyz üçin sag boluň! Harytlara baha beriň.'],
        'order_status_cancelled' => ['title' => 'Sargyt № :number ýatyryldy', 'body' => 'Sargydyňyz ýatyryldy.'],
        'product_available' => ['title' => 'Haryt ýene satuwda', 'body' => 'Garaşyş sanawyňyzdaky haryt ýene elýeterli.'],
        'chat_message' => ['title' => 'Täze habar', 'body' => 'Çatda täze habar bar.'],
        'shop_application' => ['title' => 'Dükan arzaňyz', 'body' => 'Dükan açmak arzaňyzyň ýagdaýy üýtgedi.'],
    ],
];
