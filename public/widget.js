/*
 * Passaparola — widget chat incorporabile.
 *
 * Uso nel sito dell'attività:
 *   <script src="https://<server>/assets/passaparola/widget.js" data-key="pp_..." data-lang="it" defer></script>
 *
 * Nessuna dipendenza. Il token della conversazione resta nel browser del visitatore (localStorage),
 * così ritrova lo storico e le risposte dell'operatore. Nessun cookie.
 */
(function () {
    'use strict';

    var script = document.currentScript;
    if (!script || !script.dataset.key) {
        return;
    }

    var key = script.dataset.key;
    var lang = script.dataset.lang || (document.documentElement.lang || 'it').slice(0, 2);
    var apiBase = new URL(script.src).origin + '/api/passaparola/widgets/' + encodeURIComponent(key);
    var storageKey = 'passaparola:' + key;
    var pollMs = 15000;

    function readToken() {
        try { return window.localStorage.getItem(storageKey); } catch (e) { return null; }
    }

    function writeToken(token) {
        try { window.localStorage.setItem(storageKey, token); } catch (e) { /* storage non disponibile */ }
    }

    function request(path, options) {
        return fetch(apiBase + path, Object.assign({ headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' } }, options || {}))
            .then(function (response) {
                if (!response.ok) { throw new Error('HTTP ' + response.status); }
                return response.json();
            });
    }

    var css = '' +
        '.pp-root{position:fixed;right:16px;bottom:16px;z-index:2147483000;font:15px/1.4 system-ui,sans-serif;color:#0f172a}' +
        '.pp-toggle{width:56px;height:56px;border-radius:50%;border:0;background:#0f766e;color:#fff;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.25);font-size:24px}' +
        '.pp-toggle:focus-visible,.pp-send:focus-visible,.pp-close:focus-visible{outline:3px solid #f59e0b;outline-offset:2px}' +
        '.pp-panel{position:absolute;right:0;bottom:68px;width:min(360px,calc(100vw - 32px));height:min(520px,calc(100vh - 100px));background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.25);display:flex;flex-direction:column;overflow:hidden}' +
        '.pp-panel[hidden]{display:none}' +
        '.pp-head{background:#0f766e;color:#fff;padding:12px 14px;display:flex;justify-content:space-between;align-items:center;font-weight:600}' +
        '.pp-close{background:transparent;border:0;color:#fff;font-size:20px;cursor:pointer}' +
        '.pp-notice{font-size:12px;color:#475569;background:#f1f5f9;padding:8px 14px}' +
        '.pp-log{flex:1;overflow-y:auto;padding:12px 14px;display:flex;flex-direction:column;gap:8px}' +
        '.pp-msg{max-width:85%;padding:8px 11px;border-radius:10px;white-space:pre-wrap;word-wrap:break-word}' +
        '.pp-visitor{align-self:flex-end;background:#0f766e;color:#fff}' +
        '.pp-assistant{align-self:flex-start;background:#f1f5f9}' +
        '.pp-operator{align-self:flex-start;background:#fef3c7}' +
        '.pp-form{display:flex;gap:8px;padding:10px;border-top:1px solid #e2e8f0}' +
        '.pp-input{flex:1;border:1px solid #cbd5e1;border-radius:8px;padding:8px;font:inherit;resize:none}' +
        '.pp-send{border:0;border-radius:8px;background:#0f766e;color:#fff;padding:0 14px;cursor:pointer;font:inherit}' +
        '.pp-send[disabled]{opacity:.6;cursor:wait}';

    var style = document.createElement('style');
    style.textContent = css;
    document.head.appendChild(style);

    var root = document.createElement('div');
    root.className = 'pp-root';
    root.innerHTML =
        '<div class="pp-panel" role="dialog" aria-modal="false" hidden>' +
            '<div class="pp-head"><span class="pp-title"></span><button type="button" class="pp-close">&times;</button></div>' +
            '<p class="pp-notice"></p>' +
            '<div class="pp-log" role="log" aria-live="polite"></div>' +
            '<form class="pp-form"><textarea class="pp-input" rows="2" required></textarea><button type="submit" class="pp-send"></button></form>' +
        '</div>' +
        '<button type="button" class="pp-toggle" aria-expanded="false">&#128172;</button>';
    document.body.appendChild(root);

    var panel = root.querySelector('.pp-panel');
    var toggle = root.querySelector('.pp-toggle');
    var closeBtn = root.querySelector('.pp-close');
    var log = root.querySelector('.pp-log');
    var form = root.querySelector('.pp-form');
    var input = root.querySelector('.pp-input');
    var send = root.querySelector('.pp-send');
    var config = null;
    var pollTimer = null;

    function addMessage(role, text) {
        var el = document.createElement('div');
        el.className = 'pp-msg pp-' + role;
        el.textContent = text; // mai innerHTML: il testo arriva da visitatori e dal modello
        log.appendChild(el);
        log.scrollTop = log.scrollHeight;
    }

    function renderHistory(data) {
        log.textContent = '';
        addMessage('assistant', config.greeting);
        data.messages.forEach(function (m) { addMessage(m.role, m.text); });
    }

    function loadHistory() {
        var token = readToken();
        if (!token) { return Promise.resolve(); }
        return request('/conversations/' + encodeURIComponent(token) + '/messages')
            .then(renderHistory)
            .catch(function () { /* conversazione chiusa o scaduta: si riparte */ });
    }

    function open() {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        input.focus();
        loadHistory();
        pollTimer = window.setInterval(loadHistory, pollMs); // risposte dell'operatore
    }

    function close() {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        window.clearInterval(pollTimer);
        toggle.focus();
    }

    toggle.addEventListener('click', function () { panel.hidden ? open() : close(); });
    closeBtn.addEventListener('click', close);
    root.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) { close(); } });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = input.value.trim();
        if (!text) { return; }
        addMessage('visitor', text);
        input.value = '';
        send.disabled = true;
        request('/messages', { method: 'POST', body: JSON.stringify({ message: text, conversation_token: readToken() }) })
            .then(function (data) {
                writeToken(data.conversation_token);
                addMessage('assistant', data.answer);
            })
            .catch(function () { addMessage('assistant', config.error); })
            .finally(function () { send.disabled = false; input.focus(); });
    });

    request('/config?lang=' + encodeURIComponent(lang)).then(function (data) {
        config = data;
        root.querySelector('.pp-title').textContent = data.name;
        root.querySelector('.pp-notice').textContent = data.ai_notice;
        panel.setAttribute('aria-label', data.name);
        toggle.setAttribute('aria-label', data.open);
        closeBtn.setAttribute('aria-label', data.close);
        input.setAttribute('placeholder', data.placeholder);
        input.setAttribute('aria-label', data.placeholder);
        input.setAttribute('maxlength', String(data.max_length));
        send.textContent = data.send;
        addMessage('assistant', data.greeting);
    }).catch(function () {
        root.remove(); // widget spento o sito non ammesso: niente pulsante rotto sulla pagina
    });
})();
