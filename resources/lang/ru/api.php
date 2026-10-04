<?php

return [
    'home_delivery_today' => 'Доставка сегодня',
    'home_popular' => 'Популярное',
    'home_new' => 'Новинки',

    'order_status' => [
        'pending' => 'Ожидает',
        'processing' => 'В работе',
        'delivering' => 'Доставляется',
        'completed' => 'Выполнен',
        'cancelled' => 'Отменён',
    ],
    'notifications' => [
        'order_created' => ['title' => 'Заказ № :number принят', 'body' => 'Ваш заказ передан магазину. Сообщим, когда его подтвердят.'],
        'order_status' => ['title' => 'Заказ № :number', 'body' => 'Статус заказа: :status'],
        'order_status_processing' => ['title' => 'Заказ № :number в работе', 'body' => 'Магазин подтвердил заказ и начал его готовить.'],
        'order_status_delivering' => ['title' => 'Заказ № :number в пути', 'body' => 'Курьер везёт ваш заказ.'],
        'order_status_completed' => ['title' => 'Заказ № :number выполнен', 'body' => 'Спасибо за заказ! Оцените товары.'],
        'order_status_cancelled' => ['title' => 'Заказ № :number отменён', 'body' => 'Ваш заказ отменён.'],
        'product_available' => ['title' => 'Товар снова в наличии', 'body' => 'Товар из вашего листа ожидания снова доступен.'],
        'chat_message' => ['title' => 'Новое сообщение', 'body' => 'В чате новое сообщение.'],
        'shop_application' => ['title' => 'Заявка на магазин', 'body' => 'Статус вашей заявки на открытие магазина изменился.'],
    ],
];
