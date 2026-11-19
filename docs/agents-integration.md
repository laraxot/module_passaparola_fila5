---
title: "Aprire Passaparola agli agenti: MCP, AG-UI, A2UI, WebMCP (da datomatic/laravelday-2026)"
type: analysis
module: Passaparola
created: 2026-11-18
updated: 2026-11-19
status: in-progress
issues: [11]
discussions: []
related:
  - ./architecture.md
  - ./widget.md
  - ./operator-queue.md
  - ../../AI/docs/deferred-operator-assistant-agent.md
---

# Aprire Passaparola agli agenti

Fonte: [datomatic/laravelday-2026](https://github.com/datomatic/laravelday-2026), demo del talk
"Apri la tua app agli agenti: MCP, AG-UI e A2UI" (LaravelDay 2026). È un'app Laravel 13 di esempio
(programma di una conferenza), non una libreria: si integrano i **pattern**, non il codice del dominio.

## L'idea della demo

Una sola logica applicativa raggiunta da quattro "porte", senza riscriverla:

| Porta | Chi la usa | Tecnologia della demo |
|---|---|---|
| Pagina web | una persona | rotta + controller |
| Server MCP | Claude, Cursor e altri client MCP, con token Sanctum dell'utente | `laravel/mcp` 1.1 |
| Chat agentica in pagina, in streaming, con **approvazione umana** delle azioni importanti | l'utente nell'app | `neuron-core/neuron-ai` 4.1 + `@ag-ui/client` |
| Interfaccia generata (schede, form, grafici) | l'agente compone, l'app disegna | A2UI 1.0 (`@a2ui/web_core`, `@a2ui/lit`) |
| Agente del browser | l'agente di Chrome | WebMCP (`navigator.modelContext`, origin trial) |

Punto chiave: i tool MCP sono classi scritte **una volta** e riusate dall'agente in chat in-process
(`McpTool`, circa 20 righe, adatta un tool `laravel/mcp` a neuron-ai).

## Cosa c'è già in Passaparola

| Serve | C'è già |
|---|---|
| Logica unica da esporre | le Action (`ReplyAsOperatorAction`, `CloseConversationAction`, `SendWeeklyReportsAction`, `AnswerQuestionAction`...) |
| Utente e permessi per ogni chiamata | `ResolveAccessibleWorkspaceIdsAction` + policy (story 1.9) |
| "L'AI propone, l'umano conferma" | `AiActionProposal` + `AiActionHandlerRegistry` del modulo AI |
| Conversazioni persistite | `AiThread` / `AiMessage` (assistente), `ai_agent_messages` (agenti) |
| Fornitori del modello | cascata Groq → OpenRouter → OpenAI |
| `routes/ai.php` | presente ma vuoto |
| `laravel/mcp`, Sanctum, `neuron-ai`, librerie AG-UI/A2UI | **mancano** |

## Proposta, in fasi

### Fase A — Server MCP Passaparola (consigliata per prima)

Il titolare o l'operatore collega Claude o Cursor al proprio account e gestisce l'assistenza da lì.

- Dipendenze: `laravel/mcp`. Autenticazione con **Passport** (già nel modulo User, OAuth per i client MCP), non Sanctum.
- **Fatta**: [mcp-server.md](mcp-server.md).
- `Mcp::web('/mcp/passaparola', PassaparolaServer::class)->middleware(['auth:api', 'throttle:60,1'])`.
- Tool (in `Modules/Passaparola/app/Mcp/Tools`, ognuno chiama un'Action esistente, filtro per attività dell'utente del token):

| Tool | Action | Annotazione |
|---|---|---|
| `list_workspaces` | `ResolveAccessibleWorkspaceIdsAction` | read-only |
| `search_knowledge` | `SearchKnowledgeEntriesAction` (modulo AI) | read-only |
| `save_knowledge_entry` | nuova Action su `AiKnowledgeEntry` | idempotente |
| `list_waiting_conversations` | query della coda | read-only |
| `show_conversation` | `ListConversationMessagesAction` | read-only |
| `reply_to_conversation` | `ReplyAsOperatorAction` | scrive al cliente |
| `close_conversation` | `CloseConversationAction` | distruttiva |
| `weekly_report` | `SendWeeklyReportsAction::build()` | read-only |

- Prompt MCP `improve_knowledge_base`: legge le domande senza risposta del report e propone le voci da aggiungere.
- Pagina nel pannello per creare e revocare il token MCP, con le istruzioni per Claude e Cursor.

### Fase B — Assistente dell'operatore nel pannello (AG-UI + neuron-ai)

Una chat nel pannello Passaparola ("rispondi a tutti quelli che chiedono della spedizione", "cosa manca
nella base di conoscenza?") che usa **gli stessi tool** della fase A in-process.

**Fatta**: [operator-assistant.md](operator-assistant.md) (manuale) e story 2.3.

- Modulo AI (agnostico, [AI/docs/agents.md](../../AI/docs/agents.md)): `XotBaseAgent`, `McpToolAdapter`,
  endpoint AG-UI `StreamAgentRunAction`, fornitore dalla cascata Groq → OpenRouter.
- Conversazioni dell'agente in una tabella **dedicata** `ai_agent_messages` (e i run in pausa in
  `ai_agent_runs`), non in `AiThread`/`AiMessage`: neuron-ai salva messaggi completi (tool call,
  risultati) e riprende i run dopo un'approvazione, con colonne e regole sue. Le tabelle
  dell'assistente clienti restano com'erano.
- Approvazioni (`withApprovalPolicy`) su `reply_to_conversation`, `close_conversation` e
  `save_knowledge_entry`: ogni tool che cambia qualcosa (non read-only) chiede conferma, e un test lo verifica.
- Passaparola: `OperatorAssistant` (agente), `RunOperatorAssistantAction` (rotta POST con sessione e CSRF),
  pagina Filament `OperatorAssistant`, client JS `resources/js/operator-assistant.js` → `public/js/operator-assistant.js`.

### Fase C — Interfacce generate (A2UI)

- Nel pannello: schede delle conversazioni e grafici del report composti dall'agente.
- Nel widget: per esempio un form "lascia la tua email" quando la domanda passa a una persona.
  Costo: il widget oggi è senza dipendenze; il renderer A2UI lo appesantirebbe → solo come opzione.

### Fase D — WebMCP nel widget

Il widget registra nel browser un tool `ask_business` (`navigator.modelContext.registerTool`), così
l'agente del browser del visitatore può fare domande all'attività passando dalla stessa API pubblica.
Pochissimo codice, progressivo (se il browser non lo supporta non succede nulla). Tecnologia in origin trial.
**Fatta**: [widget.md](widget.md#webmcp-lagente-del-browser-del-visitatore).

## Adattamenti alle regole Laraxot

- Service e Controller della demo → Spatie queueable Action; endpoint come Action invocabili.
- Codice agnostico (adattatori MCP↔neuron, store dei messaggi, endpoint AG-UI) nel modulo AI;
  tool e permessi di dominio in Passaparola.
- Utenti UUID: Passport (modulo User) gestisce già token e client.
- Le chiavi API restano in `.env`.

## Rischi

- `laravel/mcp`, `neuron-ai` e le librerie A2UI si muovono in fretta (A2UI 1.0, `@a2ui/lit` senza export `v1`, WebMCP in origin trial).
- Un tool MCP che scrive al cliente agisce a nome dell'operatore: annotazioni, limiti e log in `AiToolLog`.
