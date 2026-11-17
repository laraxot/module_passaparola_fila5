<?php

declare(strict_types=1);

namespace Modules\Passaparola\Tests\Unit;

use Modules\Passaparola\Models\User;
use Modules\Passaparola\Models\Workspace;
use Modules\Passaparola\Tests\TestCase;
use Modules\User\Models\BaseUser;
use PHPUnit\Framework\Assert;

uses(TestCase::class);

test('_knowledge_collection_is_prefixed_slug', function (): void {
    $workspace = new Workspace;
    $workspace->forceFill(['slug' => 'ceramiche-rossi']);

    Assert::assertSame('passaparola-ceramiche-rossi', $workspace->knowledgeCollection());
});

test('_casts_keywords_and_active_flag', function (): void {
    $workspace = new Workspace;
    $workspace->forceFill(['handoff_keywords' => ['rimbors'], 'is_active' => 1]);

    Assert::assertSame(['rimbors'], $workspace->handoff_keywords);
    Assert::assertTrue($workspace->is_active);
});

test('_project_user_is_a_laraxot_base_user', function (): void {
    Assert::assertTrue(is_subclass_of(User::class, BaseUser::class));
});
