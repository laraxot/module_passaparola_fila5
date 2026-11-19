// Assistente operatore: client AG-UI della pagina Filament OperatorAssistant.
// Il server (RunOperatorAssistantAction) tiene la cronologia: qui solo il turno corrente,
// il testo in streaming, i tool usati e le card di conferma per le azioni che arrivano al cliente.
import { HttpAgent, buildResumeArray } from '@ag-ui/client';
import { renderMarkdown } from '@a2ui/markdown-it';

export function mountOperatorAssistant(root) {
    const messages = root.querySelector('#pp-oa-messages');
    const form = root.querySelector('#pp-oa-form');
    const input = root.querySelector('#pp-oa-input');
    const button = form.querySelector('button[type="submit"]');
    const t = JSON.parse(root.dataset.i18n || '{}');

    const agent = new HttpAgent({
        url: root.dataset.agentUrl,
        threadId: root.dataset.threadId,
        headers: { 'X-CSRF-TOKEN': root.dataset.csrf, Accept: 'text/event-stream' },
    });

    // crypto.randomUUID esiste solo in contesti sicuri (https o localhost).
    const messageId = () => globalThis.crypto?.randomUUID?.() ?? `${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`;

    let assistantBubble = null;
    const toolNames = {};
    // Renderer A2UI caricato alla prima interfaccia (Lit, Chart.js): la pagina parte leggera.
    // Il posto della superficie si riserva subito, cosi' resta prima del testo che arriva intanto.
    let loadRenderer = null;
    let slot = null;
    const surface = (content) => {
        const target = append(document.createElement('div'));
        target.className = 'pp-oa-surface';
        assistantBubble = null;
        loadRenderer ??= import('../../../AI/resources/js/a2ui/render-surfaces.js')
            .then(({ createSurfaceRenderer }) => createSurfaceRenderer((element) => slot.append(element)));

        return loadRenderer.then((render) => {
            slot = target;
            if (!render(content)) {
                target.remove();
                note(content, 'text-warning-600');
            }
            messages.scrollTop = messages.scrollHeight;
        });
    };

    const append = (element) => {
        messages.append(element);
        messages.scrollTop = messages.scrollHeight;

        return element;
    };

    const bubble = (role, text = '') => {
        const element = document.createElement('div');
        element.className = role === 'user'
            ? 'pp-oa-user ml-auto w-fit max-w-[85%] rounded-2xl rounded-br-sm bg-primary-600 px-3.5 py-2 text-sm text-white'
            : 'pp-oa-assistant prose prose-sm dark:prose-invert mr-auto w-fit max-w-[85%] rounded-2xl rounded-bl-sm border border-gray-200 px-3.5 py-2 dark:border-white/10';
        element.textContent = text;

        return append(element);
    };

    // Il testo arriva a pezzi: si rigenera tutto, scartando i render superati da pezzi piu' nuovi.
    const appendMarkdown = async (element, delta) => {
        const text = (element.dataset.markdown ?? '') + delta;
        element.dataset.markdown = text;
        const html = await renderMarkdown(text);

        if (element.dataset.markdown === text) {
            element.innerHTML = html;
            messages.scrollTop = messages.scrollHeight;
        }
    };

    const note = (text, tone = 'text-gray-400') => {
        const element = document.createElement('p');
        element.className = `pp-oa-note font-mono text-xs ${tone}`;
        element.textContent = text;

        return append(element);
    };

    const setBusy = (busy) => {
        button.disabled = busy;
        input.disabled = busy;
        if (!busy) {
            input.focus();
        }
    };

    const approvalCard = (interrupt) => {
        const card = document.createElement('div');
        card.className = 'pp-oa-approval rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm dark:bg-amber-500/10';

        const title = document.createElement('p');
        title.className = 'font-medium';
        title.textContent = `${t.approval_title ?? 'Conferma richiesta'}: ${interrupt.metadata?.name ?? ''}`;

        const reason = document.createElement('p');
        reason.className = 'text-gray-600 dark:text-gray-300';
        reason.textContent = interrupt.message ?? '';

        // metadata = Action di neuron-ai: name, description, inputs (argomenti del tool), reason.
        // Gli argomenti si mostrano come "campo: valore", leggibili anche da chi non e' tecnico.
        const details = document.createElement('dl');
        details.className = 'pp-oa-inputs mt-1 text-xs text-gray-600 dark:text-gray-300';
        for (const [field, value] of Object.entries(interrupt.metadata?.inputs ?? {})) {
            const term = document.createElement('dt');
            term.className = 'font-medium';
            term.textContent = field;
            const description = document.createElement('dd');
            description.className = 'mb-1 whitespace-pre-wrap';
            description.textContent = typeof value === 'string' ? value : JSON.stringify(value);
            details.append(term, description);
        }

        const actions = document.createElement('div');
        actions.className = 'mt-2 flex gap-2';

        const decide = (approved) => {
            card.remove();
            note(approved ? (t.approved ?? 'Approvato.') : (t.rejected ?? 'Rifiutato.'));
            run({ resume: buildResumeArray([interrupt], { [interrupt.id]: { status: 'resolved', payload: { approved } } }) });
        };

        for (const [label, approved, style] of [
            [t.approve ?? 'Approva', true, 'bg-emerald-600 text-white'],
            [t.reject ?? 'Rifiuta', false, 'border border-gray-300'],
        ]) {
            const choice = document.createElement('button');
            choice.type = 'button';
            choice.className = `rounded-lg px-3 py-1 ${style}`;
            choice.textContent = label;
            choice.addEventListener('click', () => decide(approved));
            actions.append(choice);
        }

        card.append(title, reason, details, actions);

        return append(card);
    };

    const subscriber = {
        onTextMessageStartEvent() {
            assistantBubble = bubble('assistant');
        },
        onTextMessageContentEvent({ event }) {
            assistantBubble ??= bubble('assistant');
            appendMarkdown(assistantBubble, event.delta);
        },
        onToolCallStartEvent({ event }) {
            toolNames[event.toolCallId] = event.toolCallName;
            note(`→ ${event.toolCallName}`);
        },
        onToolCallResultEvent({ event }) {
            if (toolNames[event.toolCallId] === 'render_surface') {
                surface(event.content);
            }
        },
        onRunErrorEvent({ event }) {
            note(`${t.error ?? 'Errore'}: ${event.message}`, 'text-danger-600');
        },
    };

    async function run(parameters = {}) {
        setBusy(true);
        try {
            await agent.runAgent(parameters, subscriber);
            agent.pendingInterrupts.forEach(approvalCard);
        } catch (error) {
            note(`${t.error ?? 'Errore'}: ${error.message}`, 'text-danger-600');
        } finally {
            setBusy(agent.pendingInterrupts.length > 0);
        }
    }

    messages.addEventListener('click', (event) => {
        const suggestion = event.target.closest('[data-suggestion]');
        if (suggestion && !input.disabled) {
            input.value = suggestion.dataset.suggestion;
            input.focus();
        }
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const content = input.value.trim();
        if (!content || input.disabled) {
            return;
        }
        input.value = '';
        bubble('user', content);
        agent.addMessage({ id: messageId(), role: 'user', content });
        run();
    });

    return { agent, run };
}

const root = typeof document !== 'undefined' ? document.getElementById('pp-operator-assistant') : null;
if (root && !root.dataset.mounted) {
    root.dataset.mounted = '1';
    mountOperatorAssistant(root);
}
