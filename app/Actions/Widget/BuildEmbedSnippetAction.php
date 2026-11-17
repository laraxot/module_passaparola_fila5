<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Widget;

use Modules\Passaparola\Models\ChatWidget;
use Spatie\QueueableAction\QueueableAction;

/**
 * Codice HTML da incollare nel sito dell'attivita' per mostrare il widget.
 */
final class BuildEmbedSnippetAction
{
    use QueueableAction;

    public function execute(ChatWidget $widget, string $lang = 'it'): string
    {
        return sprintf(
            '<script src="%s" data-key="%s" data-lang="%s" defer></script>',
            e(rtrim((string) config('app.url'), '/').'/assets/passaparola/widget.js'),
            e($widget->public_key),
            e($lang),
        );
    }
}
