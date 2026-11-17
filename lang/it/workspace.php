<?php

declare(strict_types=1);

return [
    'label' => 'Attività',
    'plural_label' => 'Attività',
    'navigation' => ['group' => 'Passaparola', 'label' => 'Attività'],
    'fields' => [
        'id' => ['label' => 'ID'],
        'section' => ['label' => "Dati dell'attività"],
        'name' => ['label' => 'Nome'],
        'slug' => ['label' => 'Codice', 'helper_text' => 'Usato per la base di conoscenza (passaparola-<codice>): non cambiarlo dopo l\'attivazione.'],
        'report_email' => ['label' => 'Email per il report settimanale'],
        'is_active' => ['label' => 'Attiva'],
        'handoff_keywords' => ['label' => 'Parole da passare a una persona', 'helper_text' => 'Radici di parole, es. rimbors, reclam, avvocat.'],
        'updated_at' => ['label' => 'Aggiornata il'],
    ],
];
