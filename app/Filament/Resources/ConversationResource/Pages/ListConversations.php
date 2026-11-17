<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\ConversationResource\Pages;

use Modules\Passaparola\Filament\Resources\ConversationResource;
use Modules\Xot\Filament\Resources\Pages\XotBaseListRecords;

class ListConversations extends XotBaseListRecords
{
    protected static string $resource = ConversationResource::class;

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'last_message_at';
    }

    /** La coda si aggiorna da sola: nuove richieste senza ricaricare la pagina. */
    protected function getTablePollInterval(): ?string
    {
        return '30s';
    }
}
