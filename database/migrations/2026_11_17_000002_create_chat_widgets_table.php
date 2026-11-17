<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Modules\Xot\Database\Migrations\XotBaseMigration;

return new class() extends XotBaseMigration
{
    protected ?string $table_name = 'chat_widgets';

    /**
     * Widget chat incorporabile nel sito di un'attività. `public_key` va nello snippet HTML
     * (non e' un segreto); `allowed_origins` limita i siti che possono usarlo.
     */
    public function up(): void
    {
        $this->tableCreate(function (Blueprint $table): void {
            $table->id();

            $table->foreignId('workspace_id')->index();

            $table->string('name');

            $table->string('public_key', 64)->unique();

            $table->json('allowed_origins')->nullable();

            $table->text('greeting')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $this->updateTimestamps($table);
        });
    }
};
