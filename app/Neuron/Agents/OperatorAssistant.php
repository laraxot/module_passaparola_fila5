<?php

declare(strict_types=1);

namespace Modules\Passaparola\Neuron\Agents;

use Modules\AI\Neuron\Agents\XotBaseAgent;
use Modules\AI\Neuron\Tools\McpToolAdapter;
use Modules\Passaparola\Mcp\Tools\CloseConversationTool;
use Modules\Passaparola\Mcp\Tools\ListWaitingConversationsTool;
use Modules\Passaparola\Mcp\Tools\ListWorkspacesTool;
use Modules\Passaparola\Mcp\Tools\ReplyToConversationTool;
use Modules\Passaparola\Mcp\Tools\SaveKnowledgeEntryTool;
use Modules\Passaparola\Mcp\Tools\SearchKnowledgeTool;
use Modules\Passaparola\Mcp\Tools\ShowConversationTool;
use Modules\Passaparola\Mcp\Tools\WeeklyReportTool;
use NeuronAI\Tools\ToolInterface;

/**
 * Assistente dell'operatore nel pannello: gli stessi tool del server MCP, eseguiti nel processo
 * con l'utente della sessione e le stesse policy. Le azioni che arrivano al cliente o cambiano
 * la base di conoscenza chiedono conferma all'operatore prima di partire.
 */
final class OperatorAssistant extends XotBaseAgent
{
    /** Tool che richiedono conferma, con il motivo mostrato sulla card di approvazione. */
    public const array APPROVALS = [
        'reply_to_conversation' => 'Il cliente riceverà questa risposta nella chat del sito.',
        'close_conversation' => 'La conversazione verrà chiusa: il cliente non potrà più scrivere.',
        'save_knowledge_entry' => "La risposta entrerà nella base di conoscenza e l'assistente del sito la userà con i clienti.",
    ];

    /** @var list<class-string<\Laravel\Mcp\Server\Tool>> */
    public const array TOOLS = [
        ListWorkspacesTool::class,
        SearchKnowledgeTool::class,
        SaveKnowledgeEntryTool::class,
        ListWaitingConversationsTool::class,
        ShowConversationTool::class,
        ReplyToConversationTool::class,
        CloseConversationTool::class,
        WeeklyReportTool::class,
    ];

    protected function instructions(): string
    {
        return <<<'TEXT'
        Sei l'assistente degli operatori di Passaparola, il servizio di assistenza clienti per piccole attività.
        Aiuti l'operatore a gestire la coda delle conversazioni passate a una persona, a rispondere ai clienti
        e a migliorare la base di conoscenza. Rispondi in italiano, breve, in Markdown.

        - Non inventare conversazioni, id, attività o risposte: usa sempre i tool.
        - Quando proponi una risposta a un cliente, scrivila prima per intero all'operatore; inviala con
          reply_to_conversation solo se te lo chiede. L'invio richiede comunque la sua conferma.
        - Nella base di conoscenza salva solo informazioni confermate dall'operatore, mai supposizioni.
        - Presenta le conversazioni in elenco: id, attività, cliente, ultimo messaggio e da quanto attende.
        TEXT;
    }

    /** @return list<ToolInterface> */
    protected function tools(): array
    {
        return array_map(static function (string $class): ToolInterface {
            $tool = new McpToolAdapter(app($class));
            $reason = self::APPROVALS[$tool->getName()] ?? null;

            return $reason === null ? $tool : $tool->withApprovalPolicy(static fn (): string => $reason);
        }, self::TOOLS);
    }
}
