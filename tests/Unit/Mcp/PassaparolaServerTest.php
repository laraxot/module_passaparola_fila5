<?php

declare(strict_types=1);

namespace Modules\Passaparola\Tests\Unit\Mcp;

use Laravel\Mcp\Server\Tool;
use Modules\Passaparola\Mcp\Servers\PassaparolaServer;
use Modules\Passaparola\Mcp\Tools\CloseConversationTool;
use Modules\Passaparola\Mcp\Tools\ListWaitingConversationsTool;
use Modules\Passaparola\Mcp\Tools\ListWorkspacesTool;
use Modules\Passaparola\Mcp\Tools\ReplyToConversationTool;
use Modules\Passaparola\Mcp\Tools\SaveKnowledgeEntryTool;
use Modules\Passaparola\Mcp\Tools\SearchKnowledgeTool;
use Modules\Passaparola\Mcp\Tools\ShowConversationTool;
use Modules\Passaparola\Mcp\Tools\WeeklyReportTool;
use Modules\Passaparola\Tests\TestCase;
use PHPUnit\Framework\Assert;

uses(TestCase::class);

/** @return array<string, array<string, mixed>> nome del tool => toArray() */
function passaparolaTools(): array
{
    $tools = [];
    foreach ([ListWorkspacesTool::class, SearchKnowledgeTool::class, SaveKnowledgeEntryTool::class, ListWaitingConversationsTool::class,
        ShowConversationTool::class, ReplyToConversationTool::class, CloseConversationTool::class, WeeklyReportTool::class] as $class) {
        /** @var Tool $tool */
        $tool = new $class;
        $tools[$tool->name()] = $tool->toArray();
    }

    return $tools;
}

test('_exposes_the_expected_tools_with_unique_snake_case_names', function (): void {
    Assert::assertSame([
        'list_workspaces', 'search_knowledge', 'save_knowledge_entry', 'list_waiting_conversations',
        'show_conversation', 'reply_to_conversation', 'close_conversation', 'weekly_report',
    ], array_keys(passaparolaTools()));
});

test('_annotations_tell_clients_what_each_tool_does', function (): void {
    $tools = passaparolaTools();

    foreach (['list_workspaces', 'search_knowledge', 'list_waiting_conversations', 'show_conversation', 'weekly_report'] as $name) {
        Assert::assertTrue($tools[$name]['annotations']['readOnlyHint'] ?? false, $name.' deve essere in sola lettura');
    }

    Assert::assertTrue($tools['close_conversation']['annotations']['destructiveHint'] ?? false);
    Assert::assertTrue($tools['save_knowledge_entry']['annotations']['idempotentHint'] ?? false);
    // scrive a una persona fuori dall'app: il client deve trattarlo con cautela
    Assert::assertTrue($tools['reply_to_conversation']['annotations']['openWorldHint'] ?? false);
    Assert::assertArrayNotHasKey('readOnlyHint', $tools['reply_to_conversation']['annotations']);
});

test('_the_reply_tool_asks_for_human_confirmation_in_its_description', function (): void {
    Assert::assertStringContainsString('chiedi conferma', passaparolaTools()['reply_to_conversation']['description']);
});

test('_required_arguments_are_declared_in_the_input_schema', function (): void {
    $tools = passaparolaTools();

    Assert::assertSame(['workspace', 'query'], $tools['search_knowledge']['inputSchema']['required']);
    Assert::assertSame(['conversation_id', 'message'], $tools['reply_to_conversation']['inputSchema']['required']);
    Assert::assertSame(['workspace', 'question', 'answer'], $tools['save_knowledge_entry']['inputSchema']['required']);
});

test('_without_a_user_every_tool_refuses', function (string $tool, array $arguments): void {
    PassaparolaServer::tool($tool, $arguments)->assertHasErrors();
})->with([
    'list_workspaces' => [ListWorkspacesTool::class, []],
    'list_waiting_conversations' => [ListWaitingConversationsTool::class, []],
    'show_conversation' => [ShowConversationTool::class, ['conversation_id' => 1]],
    'reply_to_conversation' => [ReplyToConversationTool::class, ['conversation_id' => 1, 'message' => 'Ciao']],
]);
