<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\ChatWidgetResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\Passaparola\Actions\Access\ResolveAccessibleWorkspaceIdsAction;
use Modules\Xot\Contracts\UserContract;
use Modules\Xot\Filament\Resources\Schemas\XotBaseResourceForm;

/**
 * Form di ChatWidgetResource. Etichette da `passaparola::chat_widget.fields.*`.
 */
class ChatWidgetForm extends XotBaseResourceForm
{
    /** @return array<string, Component> */
    public function getFormSchema(): array
    {
        return [
            'section' => Section::make()
                ->schema([
                    'workspace_id' => Select::make('workspace_id')
                        ->relationship(
                            'workspace',
                            'name',
                            // solo le attivita' di cui l'utente e' titolare (tutte per l'amministrazione)
                            static function (Builder $query): Builder {
                                $user = Auth::user();
                                $ids = $user instanceof UserContract
                                    ? app(ResolveAccessibleWorkspaceIdsAction::class)->execute($user, onlyOwner: true)
                                    : [];

                                return $ids === null ? $query : $query->whereKey($ids);
                            },
                        )
                        ->searchable()
                        ->preload()
                        ->required(),

                    'name' => TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    'allowed_origins' => TagsInput::make('allowed_origins')
                        ->placeholder('https://www.example.com')
                        ->nestedRecursiveRules(['url', 'starts_with:http://,https://'])
                        ->columnSpanFull(),

                    'greeting' => Textarea::make('greeting')
                        ->maxLength(500)
                        ->columnSpanFull(),

                    'is_active' => Toggle::make('is_active')
                        ->default(true),
                ])
                ->columns(2),
        ];
    }
}
