<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\ConversationResource\Tables;

use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\SelectFilter;
use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\UI\Filament\Tables\Columns\IDColumn;
use Modules\Xot\Filament\Resources\Tables\XotBaseResourceTable;

class ConversationsTable extends XotBaseResourceTable
{
    /** @return array<string, Column> */
    public function getTableColumns(): array
    {
        return [
            'id' => IDColumn::make('id')->sortable(),
            'workspace.name' => TextColumn::make('workspace.name')->sortable()->searchable(),
            'status' => TextColumn::make('status')->badge()->sortable(),
            'last_message_at' => TextColumn::make('last_message_at')->dateTime('d/m/Y H:i')->sortable()->since(),
            'created_at' => TextColumn::make('created_at')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /** @return array<string, BaseFilter> */
    public function getTableFilters(): array
    {
        return [
            'status' => SelectFilter::make('status')
                ->options(ConversationStatusEnum::class)
                ->default(ConversationStatusEnum::HANDED_OFF->value),
            'workspace_id' => SelectFilter::make('workspace_id')
                ->relationship('workspace', 'name')
                ->searchable()
                ->preload(),
        ];
    }
}
