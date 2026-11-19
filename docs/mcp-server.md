---
title: "Server MCP: Passaparola da Claude, Cursor e altri client"
type: guide
module: Passaparola
created: 2026-11-19
updated: 2026-11-19
status: active
issues: [11]
discussions: []
related:
  - ./agents-integration.md
  - ./access-control.md
  - ./stories/2.1.mcp-server.story.md
---

# Server MCP

Titolari e operatori possono gestire Passaparola da un assistente AI che parla MCP (Claude, Cursor, ...):
"quali clienti aspettano una risposta?", "aggiungi alla base di conoscenza che il negozio è chiuso il lunedì",
"cosa hanno chiesto i clienti questo mese?".

Indirizzo: `https://<server>/mcp/passaparola`

## Per chi parte da zero: collegare Claude

1. L'utente deve avere un account Passaparola con il ruolo `passaparola::operator` ed essere membro di almeno
   un'attività ([access-control.md](access-control.md)).
2. In Claude: **Impostazioni → Connettori → Aggiungi connettore personalizzato**, incolla l'indirizzo qui sopra.
3. Claude apre la pagina di accesso di Passaparola: entra con il tuo account e approva l'accesso
   (pagina di consenso OAuth, scope `mcp:use`).
4. Da quel momento Claude agisce **a tuo nome**, solo sulle tue attività. Puoi revocare l'accesso dal
   pannello amministrativo (modulo User → Passport → token di accesso).

Cursor e gli altri client MCP funzionano allo stesso modo: indirizzo del server, poi login nel browser.

## Tipi di utente

| Utente | Cosa può fare via MCP |
|---|---|
| **Titolare** | tutto ciò che segue, compreso salvare voci nella base di conoscenza |
| **Operatore** | leggere coda, conversazioni, report e base di conoscenza; rispondere e chiudere conversazioni |
| **Amministrazione Passaparola** | come il titolare, su tutte le attività |

## Tool

| Tool | Cosa fa | Tipo |
|---|---|---|
| `list_workspaces` | attività dell'utente, con codice e conversazioni in attesa | sola lettura |
| `search_knowledge` | cerca le risposte ufficiali di un'attività | sola lettura |
| `save_knowledge_entry` | aggiunge o corregge una risposta ufficiale (solo titolare) | idempotente |
| `list_waiting_conversations` | coda: conversazioni passate a una persona | sola lettura |
| `show_conversation` | tutti i messaggi di una conversazione | sola lettura |
| `reply_to_conversation` | invia una risposta al visitatore a nome dell'operatore | scrive a un cliente |
| `close_conversation` | chiude una conversazione | distruttiva |
| `weekly_report` | numeri, domande frequenti, domande senza risposta | sola lettura |

Prompt: `improve_knowledge_base` — legge il report, trova le domande senza risposta, propone le voci e le
salva solo dopo la conferma del titolare, segnando "DA CONFERMARE" i dati che non conosce.

## Come funziona

- **Rotta**: `laravel/routes/ai.php` → `Mcp::oauthRoutes()` + `Mcp::web('/mcp/passaparola', PassaparolaServer::class)`
  con `auth:api` (Passport, già usato dal modulo User) e 60 richieste al minuto.
- **OAuth**: discovery e registrazione dinamica dei client da `laravel/mcp`; pagina di consenso `mcp::authorize`
  impostata dal `PassaparolaServiceProvider`. Senza token il server risponde 401 JSON (`bootstrap/app.php`), da cui il
  client parte per il login.
- **Permessi**: l'utente è quello del token. Attività da `FindWorkspaceForUserAction`, conversazioni con le
  stesse policy del pannello (`ConversationPolicy`). Un'attività di un altro utente dà lo stesso errore di una inesistente.
- **Una logica sola**: ogni tool chiama le Action che usa il pannello (`ReplyAsOperatorAction`,
  `CloseConversationAction`, `SendWeeklyReportsAction::build`, `SearchKnowledgeEntriesAction`, `SaveKnowledgeEntryAction`).
- **Codice**: `app/Mcp/Servers/PassaparolaServer.php`, `app/Mcp/Tools/*`, `app/Mcp/Prompts/*`, `app/Mcp/Concerns/ResolvesPassaparolaUser.php`.

## Test

`tests/Unit/Mcp/PassaparolaServerTest.php`: nomi, annotazioni, argomenti obbligatori, rifiuto senza utente.
Da fare nell'app host: prova con un client vero (`php artisan mcp:inspector mcp/passaparola`).
