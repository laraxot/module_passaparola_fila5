<?php

declare(strict_types=1);

return [
    'title' => 'Operator assistant',
    'navigation' => ['group' => 'Passaparola', 'label' => 'Assistant'],
    'intro' => 'Ask the assistant to show the queue, summarise a conversation, draft a reply or update the knowledge base. Replies and saves always ask for your confirmation.',
    'suggestions' => [
        'Who is waiting for a reply?',
        'Summarise the latest conversation in the queue',
        'How did this week go?',
    ],
    'input' => 'Message for the assistant',
    'placeholder' => 'Write a request…',
    'send' => 'Send',
    'js' => [
        'approval_title' => 'Confirmation required',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'approved' => 'Approved.',
        'rejected' => 'Rejected.',
        'error' => 'Error',
    ],
];
