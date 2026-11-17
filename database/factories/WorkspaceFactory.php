<?php

declare(strict_types=1);

namespace Modules\Passaparola\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Passaparola\Models\Workspace;

/** @extends Factory<Workspace> */
class WorkspaceFactory extends Factory
{
    /** @var class-string<Workspace> */
    protected $model = Workspace::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'handoff_keywords' => ['rimbors', 'reclam'],
            'report_email' => $this->faker->safeEmail(),
            'is_active' => true,
        ];
    }
}
