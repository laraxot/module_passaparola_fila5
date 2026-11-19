<?php

declare(strict_types=1);

namespace Modules\Passaparola\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Modules\Passaparola\Mcp\Prompts\ImproveKnowledgeBasePrompt;
use Modules\Passaparola\Mcp\Tools\CloseConversationTool;
use Modules\Passaparola\Mcp\Tools\ListWaitingConversationsTool;
use Modules\Passaparola\Mcp\Tools\ListWorkspacesTool;
use Modules\Passaparola\Mcp\Tools\ReplyToConversationTool;
use Modules\Passaparola\Mcp\Tools\SaveKnowledgeEntryTool;
use Modules\Passaparola\Mcp\Tools\SearchKnowledgeTool;
use Modules\Passaparola\Mcp\Tools\ShowConversationTool;
use Modules\Passaparola\Mcp\Tools\WeeklyReportTool;

/**
 * Server MCP di Passaparola: titolari e operatori gestiscono base di conoscenza e coda da
 * Claude, Cursor o altri client MCP. Ogni tool chiama le stesse Action del pannello e rispetta
 * gli stessi permessi (utente del token OAuth Passport, attività di cui e' membro).
 *
 * Rotta in `laravel/routes/ai.php`: /mcp/passaparola (auth:api + limite di richieste).
 */
#[Name('Passaparola')]
#[Version('1.0.0')]
#[Instructions("Assistenza clienti di piccole attività. Ogni attività ha un codice (list_workspaces). La base di conoscenza contiene le sole risposte ufficiali usate dall'assistente del sito: non aggiungere informazioni non confermate dal titolare. Prima di inviare una risposta a un cliente (reply_to_conversation) mostra il testo all'utente e chiedi conferma.")]
class PassaparolaServer extends Server
{
    /** @var array<int, class-string<Server\Tool>> */
    protected array $tools = [
        ListWorkspacesTool::class,
        SearchKnowledgeTool::class,
        SaveKnowledgeEntryTool::class,
        ListWaitingConversationsTool::class,
        ShowConversationTool::class,
        ReplyToConversationTool::class,
        CloseConversationTool::class,
        WeeklyReportTool::class,
    ];

    /** @var array<int, class-string<Server\Prompt>> */
    protected array $prompts = [
        ImproveKnowledgeBasePrompt::class,
    ];
}
