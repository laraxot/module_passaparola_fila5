<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources;

use Filament\Widgets\Widget;
use Modules\Passaparola\Filament\Resources\WorkspaceResource\Pages;
use Modules\Passaparola\Models\Workspace;
use Modules\Passaparola\Filament\Concerns\ScopesToAccessibleWorkspaces;
use Modules\Xot\Filament\Resources\XotBaseResource;

/**
 * Attività clienti di Passaparola.
 */
class WorkspaceResource extends XotBaseResource
{
    use ScopesToAccessibleWorkspaces;

    protected static function workspaceColumn(): string
    {
        return 'id';
    }

    protected static ?string $model = Workspace::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    /** @return array<class-string<Widget>> */
    public static function getHeaderWidgets(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return array_merge(parent::getPages(), [
            'index' => Pages\ListWorkspaces::route('/'),
            'create' => Pages\CreateWorkspace::route('/create'),
            'edit' => Pages\EditWorkspace::route('/{record}/edit'),
        ]);
    }
}
