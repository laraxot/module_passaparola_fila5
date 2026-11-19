<?php

declare(strict_types=1);

namespace Modules\Passaparola\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('improve_knowledge_base')]
#[Title('Migliora la base di conoscenza')]
#[Description("Trova le domande dei clienti rimaste senza risposta e propone le voci da aggiungere; le salva solo dopo la conferma del titolare.")]
class ImproveKnowledgeBasePrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate(['workspace' => ['required', 'string', 'max:100']]);

        return Response::text(<<<PROMPT
            Voglio migliorare le risposte dell'assistente della mia attività "{$validated['workspace']}".

            1. Leggi il report con weekly_report (ultimi 30 giorni) e guarda le domande senza risposta e quelle passate a una persona.
            2. Per ognuna controlla con search_knowledge se esiste già una voce simile.
            3. Proponimi in una tabella le voci da aggiungere o correggere: domanda tipo e bozza di risposta. Segna con "DA CONFERMARE" ogni dato che non conosci (prezzi, orari, tempi): non inventarlo.
            4. Salva con save_knowledge_entry solo le voci che confermo, con il testo che approvo.
            PROMPT);
    }

    /** @return array<int, Argument> */
    public function arguments(): array
    {
        return [
            new Argument(name: 'workspace', description: "Codice dell'attività (vedi list_workspaces).", required: true),
        ];
    }
}
