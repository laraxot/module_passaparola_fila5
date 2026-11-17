---
title: "Report settimanale"
type: guide
module: Passaparola
created: 2026-11-17
updated: 2026-11-17
status: active
issues: []
discussions: []
related:
  - ./stories/1.6.weekly-report.story.md
---

# Report settimanale

Ogni lunedì alle 7:40 ogni attività attiva con un'**Email per il report settimanale** riceve un'email con:

- domande ricevute, risolte dall'assistente (con percentuale) e passate a una persona;
- le 5 domande più frequenti, con il testo della voce della base di conoscenza usata;
- fino a 10 domande senza risposta, da aggiungere alla base di conoscenza.

È la parte del servizio che dà più valore al titolare: se una domanda torna spesso, di solito una pagina
del sito non è chiara.

## Per chi parte da zero

1. Nel pannello **Passaparola → Attività** apri l'attività e compila **Email per il report settimanale**.
2. Serve che lo scheduler di Laravel giri sul server (`php artisan schedule:run` ogni minuto, di solito da cron).
3. Il lunedì successivo arriva la prima email. Senza domande nella settimana l'email lo dice.

## Come funziona

| Cosa | Dove |
|---|---|
| Pianificazione | `PassaparolaServiceProvider::boot()` → `weeklyOn(1, '7:40')`, nome `passaparola-weekly-reports` |
| Dati | `Report\SendWeeklyReportsAction::build()` → `BuildAssistantReportAction` del modulo AI sulla collection dell'attività, testi delle voci da `AiKnowledgeEntry` |
| Email | `Mail\WeeklyReportMail` con vista `passaparola::mail.weekly-report`, testi in `lang/*/weekly_report.php` |
| Errori | un errore su un'attività viene scritto nel log e non ferma le altre |

Prova manuale dall'app: `php artisan tinker` → `app(\Modules\Passaparola\Actions\Report\SendWeeklyReportsAction::class)->execute()`.
