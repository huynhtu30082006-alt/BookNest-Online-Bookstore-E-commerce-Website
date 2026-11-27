<?php

return [
    // Fixed delivery fee in VND, applied to every non-empty order.
    'delivery_fee' => (int) env('BOOKNEST_DELIVERY_FEE', 30000),

    // Maximum quantity of a single book in one cart line.
    'max_quantity_per_item' => 20,

    // Simulated payment methods: key => label.
    'payment_methods' => [
        'cod' => 'Cash on delivery (COD)',
        'card' => 'Credit / debit card (simulated)',
        'ewallet' => 'E-wallet (simulated)',
    ],
];
