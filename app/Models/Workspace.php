<?php

declare(strict_types=1);

namespace Modules\Passaparola\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Passaparola\Database\Factories\WorkspaceFactory;
use Modules\Xot\Models\XotBaseModel;

/**
 * Un'attività cliente di Passaparola.
 *
 * La base di conoscenza dell'attività vive nel modulo AI (AiKnowledgeEntry) con
 * `collection` = knowledgeCollection(): Passaparola chiama il modulo AI, non il contrario.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property list<string>|null $handoff_keywords
 * @property string|null $report_email
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ChatWidget> $chatWidgets
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Conversation> $conversations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, WorkspaceMember> $members
 *
 * @method static Builder<static>|Workspace newModelQuery()
 * @method static Builder<static>|Workspace newQuery()
 * @method static Builder<static>|Workspace query()
 * @method static WorkspaceFactory factory($count = null, $state = [])
 */
class Workspace extends XotBaseModel
{
    public const string COLLECTION_PREFIX = 'passaparola-';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'handoff_keywords',
        'report_email',
        'is_active',
    ];

    /** @return HasMany<ChatWidget, $this> */
    public function chatWidgets(): HasMany
    {
        return $this->hasMany(ChatWidget::class);
    }

    /** @return HasMany<Conversation, $this> */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /** @return HasMany<WorkspaceMember, $this> */
    public function members(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    /** Collection della base di conoscenza nel modulo AI. */
    public function knowledgeCollection(): string
    {
        return self::COLLECTION_PREFIX.$this->slug;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'handoff_keywords' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
