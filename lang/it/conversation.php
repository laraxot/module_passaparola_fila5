<?php

declare(strict_types=1);

return [
    'label' => 'Conversazione',
    'plural_label' => 'Coda conversazioni',
    'navigation' => ['group' => 'Passaparola', 'label' => 'Coda conversazioni'],
    'fields' => [
        'id' => ['label' => 'ID'],
        'workspace.name' => ['label' => 'Attività'],
        'workspace_id' => ['label' => 'Attività'],
        'status' => ['label' => 'Stato'],
        'last_message_at' => ['label' => 'Ultimo messaggio'],
        'created_at' => ['label' => 'Iniziata il'],
        'transcript' => ['label' => 'Messaggi'],
        'body' => ['label' => 'Risposta', 'helper_text' => 'Il visitatore la vede nella chat del sito.'],
    ],
    'actions' => [
        'reply' => ['label' => 'Rispondi'],
        'close' => ['label' => 'Chiudi conversazione', 'modal_description' => 'Se il visitatore riscrive si apre una nuova conversazione.'],
    ],
    'notifications' => ['reply_sent' => 'Risposta inviata', 'closed' => 'Conversazione chiusa'],
    'roles' => ['visitor' => 'Visitatore', 'assistant' => 'Assistente AI', 'operator' => 'Operatore'],
    'empty' => 'Nessun messaggio.',
];
