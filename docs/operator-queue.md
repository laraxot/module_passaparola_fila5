---
title: "Coda dell'operatore"
type: guide
module: Passaparola
created: 2026-11-17
updated: 2026-11-17
status: active
issues: []
discussions: []
related:
  - ./stories/1.5.operator-queue.story.md
  - ./widget.md
---

# Coda dell'operatore

Quando l'assistente non può rispondere da solo (argomento delicato, nessuna risposta nella base di
conoscenza, cliente arrabbiato, modello non raggiungibile) la conversazione passa a **"Passata a una persona"**
e finisce nella coda.

## Per chi parte da zero

1. Entra nel pannello **Passaparola** e apri **Coda conversazioni**. Il numero accanto alla voce di menu
   dice quante conversazioni aspettano una persona. La lista si aggiorna da sola ogni 30 secondi.
2. Il filtro parte da **Passata a una persona**; puoi cambiarlo per vedere anche quelle aperte o chiuse,
   o filtrare per attività.
3. Apri una conversazione: vedi tutti i messaggi (visitatore, assistente AI, operatore).
4. **Rispondi**: scrivi la risposta. Il visitatore la vede nella chat del sito entro 15 secondi, se la chat è aperta,
   o quando la riapre.
5. **Chiudi conversazione** quando hai finito. Se il visitatore riscrive, si apre una nuova conversazione.

## Tipi di utente

| Utente | Cosa fa |
|---|---|
| **Operatore** | Lavora la coda: legge, risponde, chiude |
| **Titolare** | Come l'operatore; guarda il report settimanale per ridurre le richieste |
| **Visitatore** | Riceve la risposta nella chat del sito |

## Come funziona

| Cosa | Dove |
|---|---|
| Elenco e filtri | `ConversationResource` (`ListConversations`: ordinata per ultimo messaggio, aggiornamento ogni 30 s) |
| Messaggi | `ConversationInfolist` + vista `passaparola::filament.conversation-transcript` (stessi ruoli del widget) |
| Risposta | `Operator\ReplyAsOperatorAction`: messaggio nel thread AI con `kind = operator_reply`; conferma le richieste `assistant_handoff` in attesa (presa in carico) |
| Chiusura | `Operator\CloseConversationAction` |

## Limiti noti

- **Accesso per attività**: ognuno vede solo la coda delle proprie attività ([access-control.md](access-control.md)).
- Nessuna notifica (email o push) all'arrivo di una nuova richiesta: solo il contatore e l'aggiornamento della lista.
