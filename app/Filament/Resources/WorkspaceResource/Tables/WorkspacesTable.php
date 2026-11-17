<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\WorkspaceResource\Tables;

use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Modules\UI\Filament\Tables\Columns\IDColumn;
use Modules\Xot\Filament\Resources\Tables\XotBaseResourceTable;

class WorkspacesTable extends XotBaseResourceTable
{
    /** @return array<string, Column> */
    public function getTableColumns(): array
    {
        return [
            'id' => IDColumn::make('id')->sortable(),
            'name' => TextColumn::make('name')->sortable()->searchable(),
            'slug' => TextColumn::make('slug')->badge()->searchable(),
            'report_email' => TextColumn::make('report_email')->toggleable(),
            'is_active' => IconColumn::make('is_active')->boolean()->sortable(),
            'updated_at' => TextColumn::make('updated_at')->dateTime('d/m/Y H:i')->sortable(),
        ];
    }
}
