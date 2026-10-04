<?php

return [
    'home_delivery_today' => 'Delivery today',
    'home_popular' => 'Popular',
    'home_new' => 'New arrivals',

    'order_status' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'delivering' => 'Delivering',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],
    'notifications' => [
        'order_created' => ['title' => 'Order № :number received', 'body' => 'Your order was passed to the shop. We will tell you once it is confirmed.'],
        'order_status' => ['title' => 'Order № :number', 'body' => 'Order status: :status'],
        'order_status_processing' => ['title' => 'Order № :number is being prepared', 'body' => 'The shop confirmed your order and started preparing it.'],
        'order_status_delivering' => ['title' => 'Order № :number is on its way', 'body' => 'The courier is delivering your order.'],
        'order_status_completed' => ['title' => 'Order № :number completed', 'body' => 'Thank you for your order! Please rate the products.'],
        'order_status_cancelled' => ['title' => 'Order № :number cancelled', 'body' => 'Your order was cancelled.'],
        'product_available' => ['title' => 'Back in stock', 'body' => 'A product from your waiting list is available again.'],
        'chat_message' => ['title' => 'New message', 'body' => 'You have a new chat message.'],
        'shop_application' => ['title' => 'Your shop application', 'body' => 'The status of your shop application changed.'],
    ],
];
