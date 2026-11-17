<?php

declare(strict_types=1);

namespace Modules\Passaparola\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Passaparola\Enums\WorkspaceMemberRoleEnum;
use Modules\Xot\Models\XotBaseModel;

/**
 * Utente che lavora su un'attività (titolare o operatore).
 *
 * @property int $id
 * @property int $workspace_id
 * @property string $user_id
 * @property WorkspaceMemberRoleEnum $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Workspace|null $workspace
 * @property-read User|null $user
 *
 * @method static Builder<static>|WorkspaceMember newModelQuery()
 * @method static Builder<static>|WorkspaceMember newQuery()
 * @method static Builder<static>|WorkspaceMember query()
 */
class WorkspaceMember extends XotBaseModel
{
    /** @var list<string> */
    protected $fillable = [
        'workspace_id',
        'user_id',
        'role',
    ];

    /** @return BelongsTo<Workspace, $this> */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Utente (connessione `user`): relazione senza join, Eloquent fa una query separata.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'role' => WorkspaceMemberRoleEnum::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
