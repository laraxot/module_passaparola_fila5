---
title: "Passaparola - Architettura"
type: architecture
created: 2026-11-16
updated: 2026-11-16
status: draft
---

# Architettura proposta (non ancora implementata)

## Regole Laraxot

Moduli separati, nomi del codice in inglese, logica applicativa in Actions, test Pest, estensioni Filament tramite classi base Xot ove pertinenti. La conformità PHPStan deve essere verificata eseguendo l'analisi, non semplicemente dichiarata.

## Confine con AI

`Modules/AI` deve restare riutilizzabile. Il futuro `Modules/Passaparola` implementerà la logica di business e invocherà capacità AI controllate. I nomi delle azioni descritti sotto sono **proposte**, non API attualmente verificate.

| Esigenza | Integrazione prevista |
|---|---|
| Base di conoscenza per attività | collection per workspace; schema e implementazione da verificare |
| Rispondere a domande | action AI con sorgenti approvate e fallback umano |
| Storico conversazione | thread/messaggi con associazione sicura al visitatore |
| Passaggio a un operatore | richiesta di handoff e coda operatore |
| Report | aggregazione periodica senza esporre dati sensibili |

## Modello dati di dominio (bozza)

- **Workspace**: name, slug, owner, settings e configurazione privacy.
- **Widget**: workspace_id, public_key, allowed_domains, is_active.
- **Conversation**: workspace_id, ai_thread_id nullable, visitor_token hash, visitor_email nullable, status.
- **OperatorReply**: conversation_id, user_id, body, sent_at.

Le tabelle, le relazioni e i vincoli sono ancora da realizzare e testare. I dati devono essere isolati per workspace.

## Flussi da implementare

1. Il visitatore apre il widget, riceve informativa e invia una domanda.
2. L'assistente cerca una risposta tra le conoscenze approvate; in assenza di riscontro valido propone il passaggio umano.
3. L'operatore gestisce la coda e risponde.
4. Il titolare revisiona conoscenze, metriche e report settimanale.
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
