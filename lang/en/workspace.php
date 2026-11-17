<?php

declare(strict_types=1);

return [
    'label' => 'Business',
    'plural_label' => 'Businesses',
    'navigation' => ['group' => 'Passaparola', 'label' => 'Businesses'],
    'fields' => [
        'id' => ['label' => 'ID'],
        'section' => ['label' => 'Business details'],
        'name' => ['label' => 'Name'],
        'slug' => ['label' => 'Code', 'helper_text' => 'Used for the knowledge base (passaparola-<code>): do not change it after activation.'],
        'report_email' => ['label' => 'Weekly report email'],
        'is_active' => ['label' => 'Active'],
        'handoff_keywords' => ['label' => 'Words to hand off to a person', 'helper_text' => 'Word stems, e.g. refund, complain, lawyer.'],
        'updated_at' => ['label' => 'Updated at'],
    ],
];
