<?php

declare(strict_types=1);

namespace Modules\Passaparola\Tests\Unit\Access;

use Modules\Passaparola\Actions\Access\ResolveAccessibleWorkspaceIdsAction;
use Modules\Passaparola\Enums\WorkspaceMemberRoleEnum;
use Modules\Passaparola\Models\Policies\ChatWidgetPolicy;
use Modules\Passaparola\Models\Policies\ConversationPolicy;
use Modules\Passaparola\Models\Policies\WorkspaceMemberPolicy;
use Modules\Passaparola\Models\Policies\WorkspacePolicy;
use Modules\Passaparola\Tests\TestCase;
use Modules\Xot\Models\Policies\XotBasePolicy;
use PHPUnit\Framework\Assert;

uses(TestCase::class);

test('_admin_roles_match_what_the_base_policy_lets_through', function (): void {
    // XotBasePolicy::before lascia passare super-admin e `{modulo}::admin`: le liste filtrate devono
    // mostrare tutto agli stessi ruoli, altrimenti un admin vedrebbe azioni su record che non trova.
    Assert::assertSame(['super-admin', 'passaparola::admin'], ResolveAccessibleWorkspaceIdsAction::ADMIN_ROLES);
});

test('_policies_live_where_laravel_discovers_them_and_extend_the_xot_base', function (string $policy): void {
    Assert::assertTrue(is_subclass_of($policy, XotBasePolicy::class));
    Assert::assertStringStartsWith('Modules\\Passaparola\\Models\\Policies\\', $policy);
})->with([WorkspacePolicy::class, ChatWidgetPolicy::class, ConversationPolicy::class, WorkspaceMemberPolicy::class]);

test('_only_owners_manage_a_workspace', function (): void {
    Assert::assertTrue(WorkspaceMemberRoleEnum::OWNER->canManageWorkspace());
    Assert::assertFalse(WorkspaceMemberRoleEnum::OPERATOR->canManageWorkspace());
});
