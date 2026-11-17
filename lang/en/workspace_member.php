<?php

declare(strict_types=1);

return [
    'label' => 'Member',
    'plural_label' => 'Members',
    'fields' => [
        'user_id' => ['label' => 'User (email)'],
        'user.email' => ['label' => 'User (email)'],
        'role' => ['label' => 'Role'],
        'created_at' => ['label' => 'Added at'],
    ],
];
