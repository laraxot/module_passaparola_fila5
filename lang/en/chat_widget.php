<?php

declare(strict_types=1);

return [
    'label' => 'Chat widget',
    'plural_label' => 'Chat widgets',
    'navigation' => ['group' => 'Passaparola', 'label' => 'Chat widgets'],
    'fields' => [
        'id' => ['label' => 'ID'],
        'section' => ['label' => 'Widget'],
        'workspace_id' => ['label' => 'Business'],
        'workspace.name' => ['label' => 'Business'],
        'name' => ['label' => 'Name', 'helper_text' => 'To recognise it, e.g. "Main website".'],
        'allowed_origins' => ['label' => 'Allowed websites', 'helper_text' => 'Full addresses of the websites that can show the widget, e.g. https://www.shop.com. Without websites the widget does not answer.'],
        'greeting' => ['label' => 'Welcome message'],
        'is_active' => ['label' => 'Active'],
        'public_key' => ['label' => 'Public key'],
        'embed_snippet' => ['label' => 'Code to paste into the website', 'helper_text' => 'Paste it before </body> on every page where you want the chat.'],
        'updated_at' => ['label' => 'Updated at'],
    ],
];
