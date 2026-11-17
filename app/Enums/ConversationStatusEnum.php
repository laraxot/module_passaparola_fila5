<?php

declare(strict_types=1);

namespace Modules\Passaparola\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Modules\Xot\Traits\EnumTrait;

/**
 * Stato di una conversazione del widget.
 *
 * open -> handed_off (l'assistente ha passato la parola a una persona) -> closed
 *
 * Label/colore/icona da `passaparola::conversation_status_enum.values.<valore>.*` (EnumTrait).
 */
enum ConversationStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    use EnumTrait;

    case OPEN = 'open';
    case HANDED_OFF = 'handed_off';
    case CLOSED = 'closed';

    /** Una nuova domanda del visitatore puo' ancora arrivare all'assistente. */
    public function acceptsMessages(): bool
    {
        return $this !== self::CLOSED;
    }
}
