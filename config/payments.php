<?php

/*
|--------------------------------------------------------------------------
| Payment methods offered at checkout
|--------------------------------------------------------------------------
|
| "cash" is paid to the courier or at pickup. "online" methods are the banks
| shown in the app; until a bank gateway is connected an online order is
| stored with payment_status "unpaid" and settled manually.
|
*/

return [
    'methods' => [
        [
            'code' => 'cash',
            'type' => 'cash',
            'name' => ['tm' => 'Nagt', 'ru' => 'Наличными', 'en' => 'Cash'],
        ],
        [
            'code' => 'rysgal',
            'type' => 'online',
            'name' => ['tm' => 'Rysgal banky', 'ru' => 'АКБ «Рысгал»', 'en' => 'Rysgal Bank'],
        ],
        [
            'code' => 'senagat',
            'type' => 'online',
            'name' => ['tm' => 'Senagat banky', 'ru' => 'Сенагатбанк', 'en' => 'Senagat Bank'],
        ],
        [
            'code' => 'vneshekonombank',
            'type' => 'online',
            'name' => ['tm' => 'Daşary ykdysady iş banky', 'ru' => 'Внешэкономбанк', 'en' => 'Vnesheconombank'],
        ],
        [
            'code' => 'halkbank',
            'type' => 'online',
            'name' => ['tm' => 'Halkbank', 'ru' => 'Халкбанк', 'en' => 'Halkbank'],
        ],
    ],
];
