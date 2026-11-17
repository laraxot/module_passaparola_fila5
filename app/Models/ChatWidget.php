<?php

declare(strict_types=1);

namespace Modules\Passaparola\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\Passaparola\Database\Factories\ChatWidgetFactory;
use Modules\Xot\Models\XotBaseModel;

/**
 * Widget chat incorporabile nel sito di un'attività.
 *
 * `public_key` finisce nello snippet HTML: non e' un segreto, quindi la protezione e' `allowed_origins`.
 * Elenco vuoto = nessun sito ammesso (il widget non risponde finche' non si indica il dominio).
 *
 * @property int $id
 * @property int $workspace_id
 * @property string $name
 * @property string $public_key
 * @property list<string>|null $allowed_origins
 * @property string|null $greeting
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Workspace|null $workspace
 *
 * @method static Builder<static>|ChatWidget newModelQuery()
 * @method static Builder<static>|ChatWidget newQuery()
 * @method static Builder<static>|ChatWidget query()
 * @method static ChatWidgetFactory factory($count = null, $state = [])
 */
class ChatWidget extends XotBaseModel
{
    /** @var list<string> */
    protected $fillable = [
        'workspace_id',
        'name',
        'public_key',
        'allowed_origins',
        'greeting',
        'is_active',
    ];

    public static function generatePublicKey(): string
    {
        return 'pp_'.Str::random(40);
    }

    /** @return BelongsTo<Workspace, $this> */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Il sito che chiama (header Origin) e' tra quelli ammessi? Confronto su schema, host e porta,
     * senza distinzione di maiuscole e ignorando la barra finale.
     */
    public function allowsOrigin(?string $origin): bool
    {
        $normalized = self::normalizeOrigin($origin);
        if ($normalized === null) {
            return false;
        }

        foreach ($this->allowed_origins ?? [] as $allowed) {
            if (self::normalizeOrigin($allowed) === $normalized) {
                return true;
            }
        }

        return false;
    }

    public static function normalizeOrigin(?string $origin): ?string
    {
        if ($origin === null || trim($origin) === '') {
            return null;
        }

        $parts = parse_url(trim($origin));
        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.strtolower($parts['host']).$port;
    }

    protected static function booted(): void
    {
        static::creating(static function (ChatWidget $widget): void {
            if (! isset($widget->public_key) || $widget->public_key === '') {
                $widget->public_key = self::generatePublicKey();
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'allowed_origins' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
