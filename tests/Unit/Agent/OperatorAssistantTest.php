<?php

declare(strict_types=1);

namespace Modules\Passaparola\Tests\Unit\Agent;

use Laravel\Mcp\Server\Tool;
use Modules\Passaparola\Actions\Agent\RunOperatorAssistantAction;
use Modules\Passaparola\Mcp\Servers\PassaparolaServer;
use Modules\Passaparola\Neuron\Agents\OperatorAssistant;
use Modules\Passaparola\Tests\TestCase;
use Modules\Xot\Contracts\UserContract;
use NeuronAI\Tools\ToolInterface;
use PHPUnit\Framework\Assert;
use ReflectionClass;
use ReflectionMethod;

uses(TestCase::class);

/** @return array<string, ToolInterface> nome => tool dell'agente */
function operatorAssistantTools(): array
{
    /** @var list<ToolInterface> $tools */
    $tools = (new ReflectionMethod(OperatorAssistant::class, 'tools'))->invoke(app(OperatorAssistant::class));

    $byName = [];
    foreach ($tools as $tool) {
        $byName[$tool->getName()] = $tool;
    }

    return $byName;
}

test('_the_assistant_uses_exactly_the_tools_of_the_mcp_server', function (): void {
    $server = (new ReflectionClass(PassaparolaServer::class))->getProperty('tools')->getDefaultValue();

    Assert::assertSame($server, OperatorAssistant::TOOLS);
});

test('_every_tool_that_changes_something_asks_the_operator_first', function (): void {
    foreach (OperatorAssistant::TOOLS as $class) {
        /** @var Tool $tool */
        $tool = app($class);
        $definition = $tool->toArray();
        $readOnly = (bool) ($definition['annotations']['readOnlyHint'] ?? false);

        Assert::assertSame(! $readOnly, array_key_exists($tool->name(), OperatorAssistant::APPROVALS), $tool->name());
    }
});

test('_gated_tools_carry_the_reason_shown_on_the_approval_card', function (): void {
    $tools = operatorAssistantTools();

    foreach (OperatorAssistant::APPROVALS as $name => $reason) {
        Assert::assertSame($reason, $tools[$name]->requiresApproval(), $name);
    }

    Assert::assertFalse($tools['list_waiting_conversations']->requiresApproval());
});

test('_threads_are_bound_to_the_user', function (): void {
    $user = \Mockery::mock(UserContract::class);
    $user->allows('getKey')->andReturn('9a1f');

    Assert::assertSame('operator-9a1f-', RunOperatorAssistantAction::threadPrefix($user));
});

test('_the_assistant_can_show_kpis_charts_and_tables_without_confirmation', function (): void {
    $tools = operatorAssistantTools();

    Assert::assertArrayHasKey('render_surface', $tools);
    Assert::assertFalse($tools['render_surface']->requiresApproval());
    Assert::assertCount(count(OperatorAssistant::TOOLS) + 1, $tools);
});
