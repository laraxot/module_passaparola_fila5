// Test del client AG-UI dell'assistente operatore (bundle public/js/operator-assistant.js) con jsdom
// e un server AG-UI finto che risponde in SSE. Esecuzione: NODE_PATH=<node_modules con jsdom> node --test tests/js/
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { JSDOM } = require('jsdom');
// Dal file (non da data:) perche' il bundle importa i chunk condivisi con percorsi relativi.
const bundle = new URL('../../public/js/operator-assistant.js', import.meta.url);

const html = `<div id="pp-operator-assistant" data-agent-url="https://app.test/run" data-thread-id="operator-7-t1"
  data-csrf="tok" data-i18n='{"approve":"Approva","reject":"Rifiuta","approval_title":"Conferma richiesta"}'>
  <div id="pp-oa-messages"></div>
  <form id="pp-oa-form"><input id="pp-oa-input"><button type="submit">Invia</button></form></div>`;

const sse = (events) => new Response(events.map((e) => `data: ${JSON.stringify(e)}\n\n`).join(''), {
    status: 200, headers: { 'Content-Type': 'text/event-stream' },
});

const textTurn = (runId, text) => [
    { type: 'RUN_STARTED', threadId: 'operator-7-t1', runId },
    { type: 'TEXT_MESSAGE_START', messageId: `m-${runId}`, role: 'assistant' },
    { type: 'TEXT_MESSAGE_CONTENT', messageId: `m-${runId}`, delta: text },
    { type: 'TEXT_MESSAGE_END', messageId: `m-${runId}` },
    { type: 'RUN_FINISHED', threadId: 'operator-7-t1', runId },
];

const approvalTurn = [
    { type: 'RUN_STARTED', threadId: 'operator-7-t1', runId: 'r2' },
    { type: 'TOOL_CALL_START', toolCallId: 'call_1', toolCallName: 'reply_to_conversation' },
    { type: 'TOOL_CALL_ARGS', toolCallId: 'call_1', delta: '{"conversation_id":4,"body":"Siamo aperti"}' },
    { type: 'TOOL_CALL_END', toolCallId: 'call_1' },
    { type: 'RUN_FINISHED', threadId: 'operator-7-t1', runId: 'r2', outcome: { type: 'interrupt', interrupts: [{
        id: 'call_1', reason: 'confirmation', message: 'Il cliente riceverà questa risposta nella chat del sito.',
        metadata: { id: 'call_1', name: 'reply_to_conversation', inputs: { conversation_id: 4, body: 'Siamo aperti' } },
    }] } },
];

async function setup(responses) {
    const dom = new JSDOM(html, { url: 'https://app.test/admin' });
    const requests = [];
    const w = dom.window;
    // il renderer A2UI (Lit) usa le API dei web component: le prende da jsdom
    Object.assign(globalThis, {
        window: w, document: w.document, HTMLElement: w.HTMLElement, Element: w.Element, Node: w.Node,
        customElements: w.customElements, ShadowRoot: w.ShadowRoot, Document: w.Document, DocumentFragment: w.DocumentFragment,
        MutationObserver: w.MutationObserver, getComputedStyle: w.getComputedStyle.bind(w), CSSStyleSheet: w.CSSStyleSheet,
        requestAnimationFrame: (callback) => setTimeout(callback, 0),
    });
    globalThis.fetch = async (url, init) => {
        requests.push({ url, headers: init.headers, body: JSON.parse(init.body) });
        return sse(responses.shift());
    };
    // query diversa a ogni test: modulo nuovo, che si monta sulla pagina nuova
    const module = await import(`${bundle.href}?t=${Math.random()}`);
    return { dom, requests, module, root: dom.window.document.getElementById('pp-operator-assistant') };
}

const until = async (check) => {
    for (let i = 0; i < 100; i += 1) {
        if (check()) return;
        await new Promise((r) => setTimeout(r, 10));
    }
    assert.fail('condizione non raggiunta');
};

const send = (dom, text) => {
    const document = dom.window.document;
    document.getElementById('pp-oa-input').value = text;
    document.getElementById('pp-oa-form').dispatchEvent(new dom.window.Event('submit', { cancelable: true }));
};

test('un turno invia thread, CSRF e messaggio e mostra la risposta in Markdown', async () => {
    const { dom, requests } = await setup([textTurn('r1', 'Ci sono **2** conversazioni in attesa.')]);
    send(dom, 'Chi aspetta?');

    const doc = dom.window.document;
    await until(() => doc.querySelector('.pp-oa-assistant strong'));
    assert.equal(requests[0].url, 'https://app.test/run');
    assert.equal(requests[0].body.threadId, 'operator-7-t1');
    assert.equal(requests[0].headers['X-CSRF-TOKEN'], 'tok');
    assert.equal(requests[0].body.messages.at(-1).content, 'Chi aspetta?');
    assert.equal(doc.querySelector('.pp-oa-user').textContent, 'Chi aspetta?');
    assert.equal(doc.querySelector('.pp-oa-assistant strong').textContent, '2');
    await until(() => !doc.getElementById('pp-oa-input').disabled);
});

test('un\'azione da confermare mostra la card e la decisione riprende il run', async () => {
    const { dom, requests } = await setup([approvalTurn, textTurn('r3', 'Risposta inviata.')]);
    send(dom, 'Rispondi alla 4 che siamo aperti');

    const doc = dom.window.document;
    await until(() => doc.querySelector('.pp-oa-approval'));
    const card = doc.querySelector('.pp-oa-approval');
    assert.match(card.textContent, /reply_to_conversation/);
    assert.match(card.textContent, /Siamo aperti/);
    assert.ok(doc.getElementById('pp-oa-input').disabled, 'in attesa della decisione non si scrive');

    [...card.querySelectorAll('button')].find((b) => b.textContent === 'Approva').click();
    await until(() => requests.length === 2 && doc.querySelectorAll('.pp-oa-assistant').length >= 1);
    assert.equal(doc.querySelector('.pp-oa-approval'), null);
    const resume = requests[1].body.resume;
    assert.equal(resume.length, 1);
    assert.equal(resume[0].interruptId, 'call_1');
    assert.deepEqual(resume[0].payload, { approved: true });
    await until(() => [...doc.querySelectorAll('.pp-oa-assistant')].some((b) => b.textContent.includes('Risposta inviata')));
});

test('un\'interfaccia render_surface resta prima del testo che arriva mentre il renderer si carica', async () => {
    const surface = JSON.stringify({ a2ui: [{ version: '1.0', createSurface: { surfaceId: 'generated-t', catalogId: 'urn:laraxot:a2ui:panel', components: [
        { id: 'root', component: 'Column', children: ['k'] },
        { id: 'k', component: 'Stat', label: 'Domande', value: 42 },
    ] } }] });
    const { dom } = await setup([[
        { type: 'RUN_STARTED', threadId: 'operator-7-t1', runId: 'r4' },
        { type: 'TOOL_CALL_START', toolCallId: 's1', toolCallName: 'render_surface' },
        { type: 'TOOL_CALL_END', toolCallId: 's1' },
        { type: 'TOOL_CALL_RESULT', messageId: 't1', toolCallId: 's1', content: surface, role: 'tool' },
        { type: 'TEXT_MESSAGE_START', messageId: 'a4', role: 'assistant' },
        { type: 'TEXT_MESSAGE_CONTENT', messageId: 'a4', delta: 'Settimana tranquilla.' },
        { type: 'TEXT_MESSAGE_END', messageId: 'a4' },
        { type: 'RUN_FINISHED', threadId: 'operator-7-t1', runId: 'r4' },
    ]]);
    send(dom, 'Report');

    const doc = dom.window.document;
    await until(() => doc.querySelector('.pp-oa-assistant')?.textContent.includes('tranquilla'));
    const slot = doc.querySelector('.pp-oa-surface');
    assert.ok(slot, 'posto della superficie riservato');
    assert.ok(slot.compareDocumentPosition(doc.querySelector('.pp-oa-assistant')) & dom.window.Node.DOCUMENT_POSITION_FOLLOWING, 'la superficie precede il testo');
    await until(() => slot.querySelector('a2ui-surface'));
    assert.equal(slot.querySelector('a2ui-surface').surface.id, 'generated-t');
});
