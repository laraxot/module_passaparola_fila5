<?php

declare(strict_types=1);

return [
    'label' => 'Conversation',
    'plural_label' => 'Conversation queue',
    'navigation' => ['group' => 'Passaparola', 'label' => 'Conversation queue'],
    'fields' => [
        'id' => ['label' => 'ID'],
        'workspace.name' => ['label' => 'Business'],
        'workspace_id' => ['label' => 'Business'],
        'status' => ['label' => 'Status'],
        'last_message_at' => ['label' => 'Last message'],
        'created_at' => ['label' => 'Started at'],
        'transcript' => ['label' => 'Messages'],
        'body' => ['label' => 'Reply', 'helper_text' => 'The visitor sees it in the website chat.'],
    ],
    'actions' => [
        'reply' => ['label' => 'Reply'],
        'close' => ['label' => 'Close conversation', 'modal_description' => 'If the visitor writes again a new conversation starts.'],
    ],
    'notifications' => ['reply_sent' => 'Reply sent', 'closed' => 'Conversation closed'],
    'roles' => ['visitor' => 'Visitor', 'assistant' => 'AI assistant', 'operator' => 'Operator'],
    'empty' => 'No messages.',
];
