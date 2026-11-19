<?php

declare(strict_types=1);

return [
    'title' => 'Assistente operatore',
    'navigation' => ['group' => 'Passaparola', 'label' => 'Assistente'],
    'intro' => "Chiedi all'assistente di mostrarti la coda, riassumere una conversazione, preparare una risposta o aggiornare la base di conoscenza. Invii e salvataggi chiedono sempre la tua conferma.",
    'suggestions' => [
        'Chi sta aspettando una risposta?',
        "Riassumi l'ultima conversazione in coda",
        'Com\'è andata questa settimana?',
    ],
    'input' => 'Messaggio per l\'assistente',
    'placeholder' => 'Scrivi una richiesta…',
    'send' => 'Invia',
    'js' => [
        'approval_title' => 'Conferma richiesta',
        'approve' => 'Approva',
        'reject' => 'Rifiuta',
        'approved' => 'Approvato.',
        'rejected' => 'Rifiutato.',
        'error' => 'Errore',
    ],
];
