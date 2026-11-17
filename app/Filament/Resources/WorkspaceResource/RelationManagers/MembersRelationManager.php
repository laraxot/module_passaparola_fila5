<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\WorkspaceResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Modules\Passaparola\Enums\WorkspaceMemberRoleEnum;
use Modules\Passaparola\Models\User;
use Modules\Xot\Filament\Resources\RelationManagers\XotBaseRelationManager;

/**
 * Chi lavora sull'attività. Aggiunge e toglie membri solo l'amministrazione Passaparola
 * (WorkspaceMemberPolicy); il titolare vede l'elenco.
 */
class MembersRelationManager extends XotBaseRelationManager
{
    protected static string $relationship = 'members';

    /** @return array<string, Component> */
    public function getFormSchema(): array
    {
        return [
            // Gli utenti stanno su un'altra connessione: ricerca diretta sul modello, niente join.
            'user_id' => Select::make('user_id')
                ->searchable()
                ->required()
                ->getSearchResultsUsing(static fn (string $search): array => User::query()
                    ->where(static fn ($query) => $query->where('email', 'like', '%'.$search.'%')->orWhere('name', 'like', '%'.$search.'%'))
                    ->limit(20)
                    ->pluck('email', 'id')
                    ->all())
                ->getOptionLabelUsing(static fn (mixed $value): ?string => User::query()->whereKey($value)->value('email')),
            'role' => Select::make('role')
                ->options(WorkspaceMemberRoleEnum::class)
                ->default(WorkspaceMemberRoleEnum::OPERATOR->value)
                ->required(),
        ];
    }

    /** @return array<string, Column> */
    protected function getTableColumns(): array
    {
        return [
            'user.email' => TextColumn::make('user.email')->searchable(false),
            'role' => TextColumn::make('role')->badge(),
            'created_at' => TextColumn::make('created_at')->dateTime('d/m/Y'),
        ];
    }
}
