---
title: "Passaparola - Architettura"
type: architecture
created: 2026-11-16
updated: 2026-11-17
status: draft
---

# Architettura proposta (non ancora implementata)

## Regole Laraxot

Moduli separati, nomi del codice in inglese, logica applicativa in Actions, test Pest, estensioni Filament tramite classi base Xot ove pertinenti. La conformità PHPStan deve essere verificata eseguendo l'analisi, non semplicemente dichiarata.

## Confine con AI

`Modules/AI` deve restare riutilizzabile. Il futuro `Modules/Passaparola` implementerà la logica di business e invocherà capacità AI controllate. I nomi delle azioni descritti sotto sono **proposte**, non API attualmente verificate.

| Esigenza | Integrazione prevista |
|---|---|
| Base di conoscenza per attività | `AiKnowledgeEntry` con `collection` = `Workspace::knowledgeCollection()` (`passaparola-{slug}`) |
| Rispondere a domande | `Assistant\AnswerQuestionAction` (solo fonti approvate, passaggio a una persona) |
| Storico conversazione | `AiThread`/`AiMessage` (`CreateAiThreadAction`, `AppendAiMessageAction`); il visitatore è legato dal token di `Conversation` |
| Passaggio a un operatore | proposta `assistant_handoff` + `Conversation.status = handed_off` |
| Report | `Assistant\BuildAssistantReportAction` per collection |

## Modello dati di dominio

| Modello | Campi | Stato |
|---|---|---|
| `Workspace` | name, slug, handoff_keywords, report_email, is_active | fatto (story 1.3) |
| `ChatWidget` | workspace_id, name, public_key, allowed_origins, greeting, is_active | fatto (story 1.4, [widget.md](widget.md)) |
| `Conversation` | workspace_id, chat_widget_id, ai_thread_id, visitor_token, status (open/handed_off/closed), last_message_at | fatto (story 1.4) |
| `User` | estende `Modules\User\Models\BaseUser` | fatto |
| Risposta dell'operatore | messaggio del thread AI con payload `kind = operator_reply` (nessuna tabella dedicata) | fatto (story 1.5) |

I dati sono separati per workspace: ogni conversazione e widget ha `workspace_id`, la base di conoscenza
ha la sua `collection`. Da verificare nell'app host (migrazioni e test).

## Flussi

1. Il visitatore apre il widget, vede l'avviso che risponde un'AI e invia una domanda ([widget.md](widget.md)). **Fatto.**
2. L'assistente risponde con le conoscenze approvate; altrimenti passa la conversazione a una persona. **Fatto.**
3. L'operatore gestisce la coda e risponde ([operator-queue.md](operator-queue.md)). **Fatto.**
4. Il titolare riceve il report settimanale ([weekly-report.md](weekly-report.md)) e aggiorna la base di conoscenza. **Fatto** (report); gestione della base di conoscenza per attività da rifinire.
5. L'amministratore Passaparola configura workspace e verifica qualità, accessi e privacy.

## Decisioni aperte

- Isolamento tenant tramite `workspace_id` o database dedicati.
- Autenticazione operatore e autorizzazioni per workspace.
- Privacy/GDPR: conservazione, DPA, localizzazione dati, audit ed esportazioni.
- Compatibilità del modulo AI con sessioni di visitatori anonimi.
- API pubblica: antiabuso, rate limits e convalida domini.
- Pianificazione report e trattamento errori delle code.

## Stato della verifica

Al 2026-11-16 il branch `dev` contiene la specifica ma non la directory `laravel/Modules/Passaparola`. Le funzionalità di dominio non possono essere considerate implementate. Consultare [il manuale](manual/it/README.md) per le procedure e le lacune.
