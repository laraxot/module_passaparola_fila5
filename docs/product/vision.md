---
title: "Passaparola - Visione di prodotto"
type: prd
created: 2026-11-16
updated: 2026-11-16
status: draft
issues: []
discussions: []
---

# Visione di prodotto

## Problema

Il titolare di una piccola attività risponde ogni giorno alle stesse domande (spedizioni, resi,
orari, prenotazioni, taglie, stato di una pratica). Lo fa di sera, oppure pensa di assumere
qualcuno che non si può permettere. Il problema non è "manca l'AI": è **troppe richieste, risposte lente**.

## Offerta

Non si vende "AI", si vende **"ti restituiamo le serate"**. Tre parti:

1. **Assistente sul sito** (widget chat) che risponde con le risposte ufficiali dell'attività.
2. **Passaggio a una persona** per tutto ciò che è delicato (rimborsi, reclami, clienti arrabbiati,
   questioni legali o mediche), con una coda di lavoro per l'operatore.
3. **Report settimanale**: cosa chiedono di più i clienti e quali domande non hanno ancora risposta.
   È la parte che dà più valore: spesso la causa si risolve alla radice (una pagina poco chiara).

Evoluzione: smistamento delle email in arrivo con bozze di risposta da approvare.

## Utenti

| Utente | Bisogno |
|---|---|
| **Titolare / gestore** | Meno messaggi, risposte corrette, capire cosa chiedono i clienti |
| **Operatore** (titolare o collaboratore) | Una coda chiara delle richieste passate a una persona |
| **Cliente finale / cittadino** | Risposta subito, oppure sapere che una persona risponderà |
| **Amministratore Passaparola** (noi) | Attivare clienti, preparare la base di conoscenza, controllare la qualità |

## Mercati iniziali

- Piccoli negozi online e attività locali (ristoranti: orari, prenotazioni, menu).
- Comuni ed enti (sinergia con Fixcity e fornitura PA): richiede requisiti più rigidi (accessibilità, privacy).

## Modello di prezzo (ipotesi da validare)

Costo di attivazione (raccolta e pulizia delle risposte, configurazione) + canone mensile
(aggiornamento risposte, revisione conversazioni, report). L'articolo di partenza indica per il
mercato USA 1.500-3.000 $ di attivazione e 400-900 $/mese: cifre **dichiarate e non verificate**,
da riprezzare per il mercato italiano. Ipotesi alternativa SaaS a fasce per volume di conversazioni.

## Regole di qualità (non negoziabili)

- L'assistente usa **solo** la base di conoscenza dell'attività; se non sa, passa a una persona.
- Mai promettere rimborsi o eccezioni, mai consigli legali o medici, passaggio a una persona sempre disponibile.
- Prima di ogni attivazione: almeno **50 domande difficili** di prova (maleducate, vaghe, scritte male).
- Al titolare si consegna una pagina che spiega cosa l'assistente può e non può fare.
- Le prime giornate di ogni attivazione servono a **raccogliere e pulire** le risposte vere: la qualità viene da lì.

## Obblighi e rischi

- **Trasparenza**: l'utente deve sapere che sta parlando con un'AI (AI Act).
- **Privacy (GDPR)**: le conversazioni contengono dati personali → informativa, nomina a responsabile
  del trattamento (DPA) con ogni attività cliente, scelta consapevole del fornitore del modello e di dove vanno i dati, conservazione limitata.
- **Errori dell'assistente**: mitigati da base di conoscenza curata, guardrail, passaggio a una persona, report.
