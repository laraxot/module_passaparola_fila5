---
title: "Progetto indipendente da Fixcity e dal tema Sixteen"
type: story
epic: 1
status: review
owner: Passaparola
created: 2026-11-17
updated: 2026-11-17
issues: [7, 8, 9, 10]
discussions: []
related:
  - ../project-audit.md
  - ../architecture.md
---

# Story: progetto indipendente da Fixcity e dal tema Sixteen

Lo scheletro è stato copiato da Fixcity. Questa story porta il progetto a dipendere solo dai propri
moduli e temi. Dettaglio dei problemi: [project-audit.md](../project-audit.md).

## Fatto

- [x] Tenant `config/local/passaparola` (main_module `Passaparola`, pub_theme `TwentyOne`)
- [x] `Models\User` del progetto (estende `BaseUser`) in auth, commenti, morph map
- [x] `config/app.php` registra `PassaparolaServiceProvider` (prima `FixcityServiceProvider`, inesistente)
- [x] Pagine d'errore `passaparola::errors.not-found` e `passaparola::errors.access` (autonome, it/en)
- [x] Rotte e scheduler senza classi di Fixcity e Sixteen
- [x] Homepage CMS con blocchi di base di TwentyOne; pagine e voci di menu di Fixcity rimosse
- [x] Strumenti, ambiente, deploy, regole degli agenti allineati
- [x] Copia del modulo AI allineata a module_ai_fila5
- [x] Tema TwentyOne: il layout `app` non compilava senza Predict; ora i componenti di modulo sono facoltativi
  (theme_twentyone_fila5, `docs/optional-module-components.md`)

- [x] Copie di 12 moduli (AI, User, Activity, Notify, Geo, Tenant, Gdpr, Lang, Cms, Media, Job, Xot) riallineate ai repository: risolti 140 file con marcatori di conflitto

## Da fare

- [ ] Verifica nell'app host (issue #7)
- [ ] Privacy: conservazione dati (issue #8)
- [ ] Testi legali e pagine CMS (issue #9)
- [ ] Pulizia della documentazione ereditata (issue #10)
