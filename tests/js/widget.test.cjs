// Test funzionale di public/widget.js in un DOM simulato (fetch finto).
// Esecuzione: npm i --no-save jsdom@24 && node tests/js/widget.test.cjs  (dalla cartella del modulo)
const { JSDOM } = require('jsdom');
const fs = require('fs');
const src = fs.readFileSync(require('path').join(__dirname, '../../public/widget.js'), 'utf8');
const calls = [];
const responses = {
  '/config': { name: 'Ceramiche Rossi', greeting: 'Ciao!', ai_notice: 'Risponde un\'AI', placeholder: 'Scrivi', send: 'Invia', open: 'Apri', close: 'Chiudi', error: 'Errore', max_length: 1000 },
  '/messages': { conversation_token: 'T'.repeat(48), answer: 'Spedizione 5 euro <b>x</b>', handed_off: false, status: 'open' },
};
const dom = new JSDOM('<!doctype html><html lang="it"><head></head><body></body></html>', { runScripts: 'outside-only', url: 'https://www.negozio.it/' });
const w = dom.window;
w.fetch = (url, opts) => { calls.push([url, opts && opts.method || 'GET', opts && opts.body]); const k = Object.keys(responses).find(p => url.includes(p)); return Promise.resolve({ ok: true, json: () => Promise.resolve(responses[k]) }); };
const s = w.document.createElement('script'); s.dataset.key = 'pp_' + 'a'.repeat(40); s.dataset.lang = 'it';
Object.defineProperty(w.document, 'currentScript', { get: () => ({ src: 'https://app.passaparola.test/assets/passaparola/widget.js', dataset: s.dataset }) });
if (!w.HTMLFormElement.prototype.requestSubmit) w.HTMLFormElement.prototype.requestSubmit = function () { this.dispatchEvent(new w.Event('submit', { cancelable: true })); };
w.eval(src);
const tick = () => new Promise(r => setTimeout(r, 20));
(async () => {
  await tick();
  const $ = q => w.document.querySelector(q);
  const ok = (c, m) => console.log((c ? 'OK   ' : 'FAIL ') + m);
  ok($('.pp-title').textContent === 'Ceramiche Rossi', 'titolo dal config');
  ok($('.pp-notice').textContent.includes('AI'), 'avviso AI visibile');
  ok(calls[0][0] === 'https://app.passaparola.test/api/passaparola/widgets/pp_' + 'a'.repeat(40) + '/config?lang=it', 'URL config corretto');
  ok($('.pp-panel').hidden === true, 'pannello chiuso all\'avvio');
  $('.pp-toggle').click(); await tick();
  ok($('.pp-panel').hidden === false && $('.pp-toggle').getAttribute('aria-expanded') === 'true', 'apertura e aria-expanded');
  $('.pp-input').value = 'Quanto costa la spedizione?';
  $('.pp-form').dispatchEvent(new w.Event('submit', { cancelable: true })); await tick();
  const post = calls.find(c => c[1] === 'POST');
  ok(post && JSON.parse(post[2]).message === 'Quanto costa la spedizione?' && JSON.parse(post[2]).conversation_token === null, 'POST con messaggio e token nullo');
  const msgs = [...w.document.querySelectorAll('.pp-msg')].map(e => e.className + '|' + e.textContent);
  ok(msgs.some(m => m.startsWith('pp-msg pp-visitor|Quanto')), 'messaggio visitatore mostrato');
  ok(w.document.querySelectorAll('.pp-msg b').length === 0 && msgs.some(m => m.includes('<b>x</b>')), 'risposta mostrata come testo, senza HTML');
  ok(w.localStorage.getItem('passaparola:pp_' + 'a'.repeat(40)) === 'T'.repeat(48), 'token salvato');
  w.document.querySelector('.pp-root').dispatchEvent(new w.KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
  ok($('.pp-panel').hidden === true, 'Esc chiude il pannello');
  process.exit(0);
})();
