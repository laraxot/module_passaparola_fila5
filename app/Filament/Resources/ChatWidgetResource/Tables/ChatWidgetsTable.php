<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\ChatWidgetResource\Tables;

use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Modules\UI\Filament\Tables\Columns\IDColumn;
use Modules\Xot\Filament\Resources\Tables\XotBaseResourceTable;

class ChatWidgetsTable extends XotBaseResourceTable
{
    /** @return array<string, Column> */
    public function getTableColumns(): array
    {
        return [
            'id' => IDColumn::make('id')->sortable(),
            'workspace.name' => TextColumn::make('workspace.name')->sortable()->searchable(),
            'name' => TextColumn::make('name')->searchable(),
            'allowed_origins' => TextColumn::make('allowed_origins')->badge(),
            'is_active' => IconColumn::make('is_active')->boolean()->sortable(),
            'updated_at' => TextColumn::make('updated_at')->dateTime('d/m/Y H:i')->sortable(),
        ];
    }
}
