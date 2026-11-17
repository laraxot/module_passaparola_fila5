<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Modules\Xot\Database\Migrations\XotBaseMigration;

return new class() extends XotBaseMigration
{
    protected ?string $table_name = 'workspaces';

    /**
     * Un'attività cliente di Passaparola (negozio, ristorante, ente...).
     */
    public function up(): void
    {
        $this->tableCreate(function (Blueprint $table): void {
            $table->id();

            $table->string('name');

            $table->string('slug')->unique();

            $table->json('handoff_keywords')->nullable();

            $table->string('report_email')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $this->updateTimestamps($table);
        });
    }
};
