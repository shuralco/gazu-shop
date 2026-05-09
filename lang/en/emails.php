<?php

return [
    'np' => [
        'subject' => [
            'shipped' => "Order #:id shipped · Tracking :ttn",
            'in_warehouse' => "Order #:id arrived at branch",
            'delivered' => "Order #:id delivered",
            'returned' => "Order #:id returned",
        ],
        'title' => [
            'shipped' => '📦 Your order has been shipped!',
            'in_warehouse' => '🏪 Your order has arrived at the branch',
            'delivered' => '✅ Order received — thank you!',
            'returned' => '↩️ Order returned',
        ],
        'body' => [
            'shipped' => 'Your order has been handed over to Nova Poshta and is now in transit. Track the status using the tracking number.',
            'in_warehouse' => 'Your order has arrived at a Nova Poshta branch and is waiting for you. Do not forget to bring your ID.',
            'delivered' => 'Thank you for your purchase! We look forward to seeing you again.',
            'returned' => 'Unfortunately, your order has been returned. We will contact you regarding next steps.',
        ],
        'greeting' => 'Hi',
        'order_label' => 'Order',
        'np_status_label' => 'NP status',
        'awaiting' => 'Pending',
        'ttn_label' => 'Tracking number',
        'eta_label' => 'Estimated delivery',
        'warehouse_label' => 'Branch',
        'track_button' => 'Track on Nova Poshta',
        'questions' => 'If you have any questions, please email us at',
        'regards' => 'Best regards',
    ],
];
