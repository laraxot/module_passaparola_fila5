<?php

declare(strict_types=1);

namespace Modules\Passaparola\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\AI\Models\AiThread;
use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\Xot\Models\XotBaseModel;

/**
 * Conversazione di un visitatore con il widget. I messaggi sono nel thread AI (`ai_thread_id`).
 *
 * @property int $id
 * @property int $workspace_id
 * @property int $chat_widget_id
 * @property int $ai_thread_id
 * @property string $visitor_token
 * @property ConversationStatusEnum $status
 * @property Carbon|null $last_message_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Workspace|null $workspace
 * @property-read ChatWidget|null $chatWidget
 * @property-read AiThread|null $thread
 *
 * @method static Builder<static>|Conversation newModelQuery()
 * @method static Builder<static>|Conversation newQuery()
 * @method static Builder<static>|Conversation query()
 */
class Conversation extends XotBaseModel
{
    /** @var list<string> */
    protected $fillable = [
        'workspace_id',
        'chat_widget_id',
        'ai_thread_id',
        'visitor_token',
        'status',
        'last_message_at',
    ];

    public static function generateVisitorToken(): string
    {
        return Str::random(48);
    }

    /** @return BelongsTo<Workspace, $this> */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /** @return BelongsTo<ChatWidget, $this> */
    public function chatWidget(): BelongsTo
    {
        return $this->belongsTo(ChatWidget::class);
    }

    /** @return BelongsTo<AiThread, $this> */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(AiThread::class, 'ai_thread_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => ConversationStatusEnum::class,
            'last_message_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
