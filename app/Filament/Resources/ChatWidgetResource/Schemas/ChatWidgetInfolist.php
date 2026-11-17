<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\ChatWidgetResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Modules\Passaparola\Actions\Widget\BuildEmbedSnippetAction;
use Modules\Passaparola\Models\ChatWidget;
use Modules\Xot\Filament\Resources\Schemas\XotBaseResourceInfolist;

class ChatWidgetInfolist extends XotBaseResourceInfolist
{
    /** @return array<string, Component> */
    public function getInfolistSchema(): array
    {
        return [
            'workspace.name' => TextEntry::make('workspace.name'),
            'name' => TextEntry::make('name'),
            'is_active' => TextEntry::make('is_active')->badge(),
            'allowed_origins' => TextEntry::make('allowed_origins')->badge()->columnSpanFull(),
            'embed_snippet' => TextEntry::make('embed_snippet')
                ->state(static fn (ChatWidget $record): string => app(BuildEmbedSnippetAction::class)->execute($record))
                ->fontFamily('mono')
                ->copyable()
                ->columnSpanFull(),
            'public_key' => TextEntry::make('public_key')->copyable(),
            'greeting' => TextEntry::make('greeting')->columnSpanFull(),
        ];
    }
}
