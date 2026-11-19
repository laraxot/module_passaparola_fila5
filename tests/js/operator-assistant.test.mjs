// Test del client AG-UI dell'assistente operatore (bundle public/js/operator-assistant.js) con jsdom
// e un server AG-UI finto che risponde in SSE. Esecuzione: NODE_PATH=<node_modules con jsdom> node --test tests/js/
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';

const require = createRequire(import.meta.url);
const { JSDOM } = require('jsdom');
const bundle = fileURLToPath(new URL('../../public/js/operator-assistant.js', import.meta.url));

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
    Object.assign(globalThis, { window: dom.window, document: dom.window.document, HTMLElement: dom.window.HTMLElement });
    globalThis.fetch = async (url, init) => {
        requests.push({ url, headers: init.headers, body: JSON.parse(init.body) });
        return sse(responses.shift());
    };
    const source = readFileSync(bundle, 'utf8');
    const module = await import(`data:text/javascript;base64,${Buffer.from(source).toString('base64')}#${Math.random()}`);
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
