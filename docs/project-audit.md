---
title: "Audit del progetto Passaparola"
type: audit
module: Passaparola
created: 2026-11-17
updated: 2026-11-17
status: active
issues: [7, 8, 9, 10]
discussions: []
related:
  - ./architecture.md
  - ./stories/epic-1-mvp.md
---

# Audit del progetto Passaparola

Audit completo dopo la creazione dello scheletro (copiato da Fixcity). Aspetti controllati:
avvio dell'app, configurazione del tenant, autenticazione, rotte, gestione errori, contenuti CMS,
strumenti (composer, npm, PHPUnit, PHPStan, release, deploy), ambiente, regole degli agenti,
codice dei moduli AI e Passaparola, privacy.

**Limite**: nell'ambiente dell'audit non c'erano `vendor/` né accesso a Packagist, quindi niente
`pest`, `phpstan` o avvio reale dell'app. Verifiche fatte: sintassi PHP, validità di JSON/XML,
`bash -n` sugli script, confronto tra i riferimenti a classi, viste e cartelle e i file presenti.

## Problemi bloccanti trovati e corretti

Story: [project-decoupling-from-fixcity.story.md](stories/project-decoupling-from-fixcity.story.md).

| # | Problema | Effetto | Correzione |
|---|---|---|---|
| 1 | `config/app.php` registrava `Modules\Fixcity\Providers\FixcityServiceProvider` | L'app non partiva (classe inesistente) | Sostituito con `PassaparolaServiceProvider` |
| 2 | Configurazione tenant in `config/local/fixcity` | Con `passaparola.local` il tenant non veniva trovato | Rinominata in `config/local/passaparola` |
| 3 | `xra.php`: `main_module` Fixcity, `pub_theme` Sixteen | Modulo e tema inesistenti | `Passaparola` e `TwentyOne` |
| 4 | `auth.php`, `comments.php`, `morph_map.php` usavano `Modules\Fixcity\Models\User` | Login e commenti rotti | Nuovo `Modules\Passaparola\Models\User` (estende `BaseUser`) |
| 5 | `morph_map.php` con chiavi duplicate, segnaposto `Modules\<nome progetto>` e classi inesistenti | Errori su relazioni polimorfiche | Solo classi esistenti, più `workspace` e `ai_knowledge_entry` |
| 6 | `routes/web.php` usava classi del tema Sixteen e del modulo Fixcity | Rotte verso classi inesistenti | Rimosse; restano sitemap e logout |
| 7 | `routes/console.php` pianificava un'azione del modulo Fixcity | Lo scheduler falliva ogni giorno | Rimossa (vedi aperto P1) |
| 8 | Pagine d'errore `fixcity::errors.*` | 404/403/419 senza pagina dedicata | Viste autonome `passaparola::errors.*` (it/en) |
| 9 | Homepage CMS con 6 blocchi del tema Sixteen | Homepage rotta con TwentyOne | Nuova homepage con `hero`, `features`, `cta` di TwentyOne |
| 10 | Pagine CMS di Fixcity (ticket, segnalazioni, demo PA in `tests/`) e 48 voci di menu verso di esse | Pagine e link rotti | Rimosse |
| 11 | `deploy.sh` controllava asset del tema Sixteen | Il deploy si fermava sempre | Controllo sul manifest di TwentyOne |
| 12 | `app/Models/ModulesFixcityAppModelsTicket.php`, `app/Observers/TicketObserver.php` | Codice morto verso classi inesistenti | Rimossi |
| 13 | `app/Application.php` puntava gli asset nativi a `themes/Sixteen` | App NativePHP senza asset | `themes/TwentyOne` |
| 14 | Il layout `app` di TwentyOne conteneva `<x-predict::daily-bonus />` e `<x-predict::win-celebration />` | Blade risolve i componenti in compilazione: senza il modulo Predict ogni pagina andava in errore | `ResolveAvailableComponentsAction` + `<x-dynamic-component>` nel tema (vedi `Themes/TwentyOne/docs/optional-module-components.md`) |

## Identità, strumenti e regole corretti

- `composer.json`, `package.json`, `laravel/composer.json`, release (`.releaserc.json`, `release-please-config.json`),
  `phpmd.xml`, `.mcp.json`: nome e descrizione Passaparola.
- `laravel/package.json`: script `dev`, `lint:js`, `lint:css` puntavano a `Themes/Sixteen`.
- `laravel/composer.json`: tolto `guard:fixcity-wizard` (script di un altro progetto).
- `laravel/phpunit.xml`: suite `Fixcity` (cartella inesistente) sostituita da `Passaparola` e `AI`.
- `phpstan-tests.neon`: percorso `Modules/Fixcity/app` sostituito da `Modules/Passaparola/app`.
- `.gitignore`: regole per file di Fixcity e Sixteen.
- Ambiente: `.env.fixcity` → `.env.passaparola`, `.env.testing`, `.env.example`, `.env.production.example`,
  `config/mail.php`, `config/health.php`, `config/notify-env.php`, `config/nativephp.php`: nomi, database, mittenti.
- `config/filesystems.php`: tolto il disco dei ticket Fixcity.
- `gitmodules.ini`: tolto il modulo Fixcity. `modules_statuses.json`: tolti `Fixcity`, `Ticket`, `Chart`, `Setting` (assenti).
- Regole degli agenti: `GEMINI.md` puntava con percorsi assoluti a `base_fixcity_fila5` (gli agenti leggevano
  l'altro progetto); `CLAUDE.md` e `AGENTS.md` citavano il tema Sixteen e la config di Fixcity; `AGENTS.md` conteneva
  righe di log di sessioni Fixcity al posto delle regole.

## Qualità del codice

- Modulo AI: `BuildAssistantReportAction` filtra nel database (`payload->kind`, `payload->collection`)
  invece di leggere i messaggi di tutte le attività.
- Modulo AI: rimosso `Actions/Support/MakeAIRequestAction`, copia non usata di `Actions/MakeAIRequestAction`.
- Copia `laravel/Modules/AI` del progetto allineata a `module_ai_fila5` (era indietro: mancava l'assistente).
  Prima di sostituirla è stato verificato che ogni file della copia esistesse già nella storia del modulo.

## Aperti, in ordine di priorità

| Pri. | Tema | Cosa fare |
|---|---|---|
| P0 | Verifica nell'app host | `composer install`, `php artisan migrate`, `pest` (suite Passaparola e AI), `phpstan`, apertura di `/`, `/it/auth/login`, pannelli Filament |
| P1 | Privacy: account cancellati | Lo scheduler di Fixcity eliminava gli account cancellati dopo `gdpr.retention.deleted_account_days`. Serve un'azione equivalente (modulo User o Gdpr, agnostica) |
| P1 | Privacy: conversazioni | `ai_messages` conserva le domande dei visitatori senza scadenza: definire la conservazione e una pulizia pianificata |
| P1 | Contenuti legali | `privacy`, `note-legali`, `terms`, `cookie-policy`, `accessibilita` e `config/local/passaparola/lang/*/*.md` sono testi di Fixcity (servizio per i Comuni): vanno riscritti per Passaparola |
| P1 | Accesso per attività | Il pannello Passaparola mostra conversazioni, widget e attività di tutti: serve legare gli utenti alle attività (owner/membri) e filtrare risorse e coda |
| P1 | Sicurezza | `laravel/.env.passaparola` è versionato e contiene credenziali del database locale: valutare di toglierlo dal repository |
| P2 | Contenuti CMS | `domande-frequenti`, `services`, `events`, `lista-categorie`, `area-personale/*`, `sections/*` hanno ancora testi Fixcity |
| P2 | Tema | Scelta definitiva del tema pubblico (ora `TwentyOne`, l'unico con i blocchi base); `Barthelemy` e `Meetup` non sono usati; le tab mobili di TwentyOne hanno voci pensate per Predict (mercati, classifica) |
| P2 | Documentazione | ~3.000 file in `docs/`, `bmad-output/` e alla radice parlano di Fixcity: pulizia da fare a blocchi, tenendo le regole Laraxot generiche di `docs/wiki/` |
| P2 | Modulo AI | `MakeAIRequestAction` usa `gpt-4` di proposito quando non c'è un'impostazione (c'è un test che lo fissa) e un prompt di sistema predefinito sui ticket PA: decisione in issue #26 di module_ai_fila5 |
| P3 | Moduli | `Event` è nella cartella dei moduli ma non in `gitmodules.ini` |
