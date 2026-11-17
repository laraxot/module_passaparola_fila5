<?php

declare(strict_types=1);

namespace Modules\Passaparola\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Passaparola\Models\ChatWidget;
use Modules\Passaparola\Models\Workspace;

/** @extends Factory<ChatWidget> */
class ChatWidgetFactory extends Factory
{
    /** @var class-string<ChatWidget> */
    protected $model = ChatWidget::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name' => 'Sito principale',
            'public_key' => ChatWidget::generatePublicKey(),
            'allowed_origins' => ['https://'.$this->faker->domainName()],
            'greeting' => 'Ciao! Come posso aiutarti?',
            'is_active' => true,
        ];
    }
}
