<?php

declare(strict_types=1);

return [
    'values' => [
        'open' => [
            'label' => 'Open',
            'color' => 'info',
            'icon' => 'heroicon-o-chat-bubble-left-right',
            'description' => 'The assistant is answering',
        ],
        'handed_off' => [
            'label' => 'Handed off',
            'color' => 'warning',
            'icon' => 'heroicon-o-user',
            'description' => 'Waiting for an operator',
        ],
        'closed' => [
            'label' => 'Closed',
            'color' => 'gray',
            'icon' => 'heroicon-o-check-circle',
            'description' => 'Conversation ended',
        ],
    ],
];
