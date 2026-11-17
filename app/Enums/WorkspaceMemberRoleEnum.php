<?php

declare(strict_types=1);

namespace Modules\Passaparola\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Modules\Xot\Traits\EnumTrait;

/**
 * Ruolo di un utente in un'attività.
 * owner: gestisce widget e impostazioni dell'attività e lavora la coda.
 * operator: lavora solo la coda delle conversazioni.
 */
enum WorkspaceMemberRoleEnum: string implements HasColor, HasIcon, HasLabel
{
    use EnumTrait;

    case OWNER = 'owner';
    case OPERATOR = 'operator';

    public function canManageWorkspace(): bool
    {
        return $this === self::OWNER;
    }
}
