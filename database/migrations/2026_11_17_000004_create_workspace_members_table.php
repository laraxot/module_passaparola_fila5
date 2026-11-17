<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Modules\Xot\Database\Migrations\XotBaseMigration;

return new class() extends XotBaseMigration
{
    protected ?string $table_name = 'workspace_members';

    /**
     * Chi lavora su un'attività: titolare (owner) o operatore. `user_id` e' stringa perche' gli
     * utenti Laraxot hanno UUID (e gli utenti stanno su un'altra connessione: niente chiave esterna).
     */
    public function up(): void
    {
        $this->tableCreate(function (Blueprint $table): void {
            $table->id();

            $table->foreignId('workspace_id')->index();

            $table->string('user_id', 36)->index();

            $table->string('role')->default('operator');

            $table->unique(['workspace_id', 'user_id']);

            $this->updateTimestamps($table);
        });
    }
};
