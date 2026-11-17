---
title: "Chi vede cosa: accesso per attività"
type: guide
module: Passaparola
created: 2026-11-17
updated: 2026-11-17
status: active
issues: []
discussions: []
related:
  - ./stories/1.9.workspace-access.story.md
---

# Chi vede cosa: accesso per attività

## Tipi di utente

| Utente | Ruolo | Vede | Può fare |
|---|---|---|---|
| **Amministratore Passaparola** | `super-admin` o `passaparola::admin` | tutte le attività | tutto: crea attività, widget, aggiunge membri |
| **Titolare** | `passaparola::operator` + membro *Titolare* dell'attività | solo le sue attività | modifica attività e widget, lavora la coda, vede i membri |
| **Operatore** | `passaparola::operator` + membro *Operatore* dell'attività | solo le sue attività | lavora la coda (risponde, chiude) |

## Per chi parte da zero: dare accesso a un titolare

1. Crea l'utente (modulo User) e assegnagli il ruolo **`passaparola::operator`**: è il ruolo che apre il
   pannello Passaparola (regola di `BaseUser::canAccessPanel`).
2. Apri **Passaparola → Attività**, l'attività, scheda **Membri** → aggiungi l'utente con ruolo **Titolare**
   (o **Operatore**). Solo l'amministrazione può aggiungere o togliere membri.
3. L'utente entra nel pannello e vede solo le attività di cui è membro, i loro widget e la loro coda.

## Come funziona

| Pezzo | Cosa fa |
|---|---|
| `WorkspaceMember` (`workspace_members`) | utente ↔ attività con ruolo `owner`/`operator`; `user_id` stringa (UUID), nessuna chiave esterna verso la connessione `user` |
| `Access\ResolveAccessibleWorkspaceIdsAction` | id delle attività dell'utente; `null` = tutte (super-admin, `passaparola::admin`) |
| `Filament\Concerns\ScopesToAccessibleWorkspaces` | filtra `getEloquentQuery()` di Attività, Widget e Coda: le liste mostrano solo le attività dell'utente |
| Policy (`Models/Policies`) | decidono le azioni: vedere (membro), modificare attività e widget (titolare), rispondere e chiudere (membro), creare attività e membri (solo amministrazione, da `XotBasePolicy::before`) |
| Form del widget | nella scelta dell'attività compaiono solo quelle di cui l'utente è titolare |
| Contatore della coda | conta solo le conversazioni delle attività dell'utente |

L'API pubblica del widget non usa questi controlli: il visitatore non ha un account, la protezione è il
sito ammesso del widget ([widget.md](widget.md)).
