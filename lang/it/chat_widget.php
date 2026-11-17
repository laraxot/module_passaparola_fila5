<?php

declare(strict_types=1);

return [
    'label' => 'Widget chat',
    'plural_label' => 'Widget chat',
    'navigation' => ['group' => 'Passaparola', 'label' => 'Widget chat'],
    'fields' => [
        'id' => ['label' => 'ID'],
        'section' => ['label' => 'Widget'],
        'workspace_id' => ['label' => 'Attività'],
        'workspace.name' => ['label' => 'Attività'],
        'name' => ['label' => 'Nome', 'helper_text' => 'Per riconoscerlo, es. "Sito principale".'],
        'allowed_origins' => ['label' => 'Siti ammessi', 'helper_text' => 'Indirizzi completi dei siti che possono mostrare il widget, es. https://www.negozio.it. Senza siti il widget non risponde.'],
        'greeting' => ['label' => 'Messaggio di benvenuto'],
        'is_active' => ['label' => 'Attivo'],
        'public_key' => ['label' => 'Chiave pubblica'],
        'embed_snippet' => ['label' => 'Codice da incollare nel sito', 'helper_text' => 'Incollalo prima di </body> in ogni pagina in cui vuoi la chat.'],
        'updated_at' => ['label' => 'Aggiornato il'],
    ],
];
