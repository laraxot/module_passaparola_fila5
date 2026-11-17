<?php

declare(strict_types=1);

return [
    'values' => [
        'open' => [
            'label' => 'Aperta',
            'color' => 'info',
            'icon' => 'heroicon-o-chat-bubble-left-right',
            'description' => 'Risponde l\'assistente',
        ],
        'handed_off' => [
            'label' => 'Passata a una persona',
            'color' => 'warning',
            'icon' => 'heroicon-o-user',
            'description' => 'In attesa di un operatore',
        ],
        'closed' => [
            'label' => 'Chiusa',
            'color' => 'gray',
            'icon' => 'heroicon-o-check-circle',
            'description' => 'Conversazione conclusa',
        ],
    ],
];
