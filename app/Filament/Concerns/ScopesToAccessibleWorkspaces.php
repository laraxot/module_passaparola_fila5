<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\Passaparola\Actions\Access\ResolveAccessibleWorkspaceIdsAction;
use Modules\Xot\Contracts\UserContract;

/**
 * Le risorse Passaparola mostrano solo i record delle attività dell'utente
 * (tutte per super-admin e `passaparola::admin`). Le policy decidono le azioni; questo filtra le liste.
 */
trait ScopesToAccessibleWorkspaces
{
    /** Colonna con l'id dell'attività nel modello della risorsa. */
    protected static function workspaceColumn(): string
    {
        return 'workspace_id';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (! $user instanceof UserContract) {
            return $query->whereRaw('1 = 0');
        }

        $ids = app(ResolveAccessibleWorkspaceIdsAction::class)->execute($user);

        return $ids === null ? $query : $query->whereIn($query->qualifyColumn(static::workspaceColumn()), $ids);
    }
}
