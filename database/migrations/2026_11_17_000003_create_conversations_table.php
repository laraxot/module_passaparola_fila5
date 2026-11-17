<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Modules\Xot\Database\Migrations\XotBaseMigration;

return new class() extends XotBaseMigration
{
    protected ?string $table_name = 'conversations';

    /**
     * Conversazione di un visitatore con il widget. I messaggi stanno nel modulo AI
     * (AiThread/AiMessage); qui il legame con attività, widget e stato del passaggio a una persona.
     * `visitor_token` e' la "chiave" del visitatore anonimo: chi la conosce legge la conversazione.
     */
    public function up(): void
    {
        $this->tableCreate(function (Blueprint $table): void {
            $table->id();

            $table->foreignId('workspace_id')->index();

            $table->foreignId('chat_widget_id')->index();

            $table->unsignedBigInteger('ai_thread_id')->index();

            $table->string('visitor_token', 64)->unique();

            $table->string('status')->default('open')->index();

            $table->dateTime('last_message_at')->nullable()->index();

            $this->updateTimestamps($table);
        });
    }
};
