<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\WorkspaceResource\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Modules\Xot\Filament\Resources\Schemas\XotBaseResourceForm;

/**
 * Form di WorkspaceResource. Nessun ->label(): etichette da `passaparola::workspace.fields.*`.
 */
class WorkspaceForm extends XotBaseResourceForm
{
    /** @return array<string, Component> */
    public function getFormSchema(): array
    {
        return [
            'section' => Section::make()
                ->schema([
                    'name' => TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    'slug' => TextInput::make('slug')
                        ->required()
                        ->alphaDash()
                        ->maxLength(100)
                        ->unique(ignoreRecord: true),

                    'report_email' => TextInput::make('report_email')
                        ->email(),

                    'is_active' => Toggle::make('is_active')
                        ->default(true),

                    'handoff_keywords' => TagsInput::make('handoff_keywords')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }
}
