/* End-to-End-Test der BIT-Webseite mit Playwright.
 *   BASE=http://localhost:8080 OUT=./screenshots BIT_USER=… BIT_PASS=… node tests/e2e.js
 * Prueft jede Seite (Status, keine PHP-Fehler, Kopf/Fuss, aktiver Menuepunkt),
 * die Sofort-Suche, Galerie, Dienstleistungs-Seiten, Kontaktformular, Login,
 * gleiche Masse (Knoepfe, Felder), keine doppelten Fotos und die Animationen,
 * und macht Bildschirmfotos (Desktop 1440 und Handy 390). */
const path = require('path');
const fs = require('fs');
const pw = require(process.env.PW || 'playwright');

const BASE = (process.env.BASE || 'http://localhost:8080').replace(/\/$/, '');
const OUT = process.env.OUT || path.join(__dirname, '..', 'screenshots');
const USER = process.env.BIT_USER;
const PASS = process.env.BIT_PASS;
fs.mkdirSync(OUT, { recursive: true });

let ok = 0, fail = 0;
const t = (name, cond, detail) => {
  if (cond) { ok++; console.log('OK      ' + name); }
  else { fail++; console.log('FEHLER  ' + name + (detail ? ' — ' + detail : '')); }
};

const PAGES = [
  ['01-start', '/', 'Start'],
  ['02-fahrzeuge', '/fahrzeuge/', 'Fahrzeuge'],
  ['03-fahrzeug-detail', '/fahrzeuge/mercedes-benz-amg-gt-c/', 'Fahrzeuge'],
  ['04-dienstleistungen', '/dienstleistungen/', 'Dienstleistungen'],
  ['04a-an-und-verkauf', '/dienstleistungen/an-und-verkauf/', 'Dienstleistungen'],
  ['04b-fahrzeugaufbereitung', '/dienstleistungen/fahrzeugaufbereitung/', 'Dienstleistungen'],
  ['04c-carrosserie-und-werkstatt', '/dienstleistungen/carrosserie-und-werkstatt/', 'Dienstleistungen'],
  ['04d-fahrzeugbewertung', '/dienstleistungen/fahrzeugbewertung/', 'Dienstleistungen'],
  ['05-kontakt', '/kontakt/', 'Kontakt'],
  ['06-ueber-uns', '/ueber-uns/', null],
  ['07-impressum', '/impressum/', null],
  ['08-datenschutz', '/datenschutz/', null],
  ['09-marke-bmw', '/marke/bmw/', 'Fahrzeuge'],
  ['10-404', '/gibt-es-nicht/', null],
];

(async () => {
  const browser = await pw.chromium.launch({
    executablePath: process.env.CHROME || '/opt/pw-browsers/chromium-1194/chrome-linux/chrome',
    args: ['--no-sandbox'],
  });

  /* ---------- Jede Seite, Desktop ---------- */
  const desk = await browser.newContext({ viewport: { width: 1440, height: 900 }, reducedMotion: 'reduce' });
  const page = await desk.newPage();
  const consoleErrors = [];
  page.on('pageerror', (e) => consoleErrors.push(e.message));
  page.on('console', (m) => { if (m.type() === 'error') consoleErrors.push(m.text()); });

  for (const [name, url, current] of PAGES) {
    const errCount = consoleErrors.length;
    const res = await page.goto(BASE + url, { waitUntil: 'networkidle' });
    const status = res.status();
    t(name + ': HTTP ' + (name === '10-404' ? '404' : '200'), status === (name === '10-404' ? 404 : 200), 'war ' + status);
    const html = await page.content();
    t(name + ': keine PHP-Meldung', !/<b>(Warning|Notice|Fatal error|Deprecated)<\/b>/i.test(html));
    t(name + ': Kopf und Fuss', await page.locator('header.bit-sitehead').count() === 1 && await page.locator('footer.sitefoot').count() === 1);
    t(name + ': kein Entwurf-Hinweis', !/Entwurf|erfunden f(ü|u)r diesen/i.test(await page.locator('body').innerText()));
    t(name + ': nichts von DAS', !/Alaska|Downtown/i.test(html) && !/\bDAS\b/.test(html));
    if (current) {
      const cur = await page.locator('.bit-sitehead nav a[aria-current="page"]').allInnerTexts();
      t(name + ': Menüpunkt «' + current + '» aktiv', cur.length === 1 && cur[0].trim() === current, cur.join(','));
    }
    const logoOk = await page.evaluate(() => {
      const el = document.querySelector('.bit-sitehead .bit-logo');
      return !!el && getComputedStyle(el).getPropertyValue('--logo-src').includes('logo-alpha.png');
    });
    t(name + ': Logo (korrigiert) geladen', logoOk);
    const radius = await page.evaluate(() => [...document.querySelectorAll('.bit-btn,.bit-veh,.bit-facts,.banner,.panel,.bit-field .ctl,.vcard')]
      .map((e) => getComputedStyle(e).borderTopLeftRadius).filter((r) => r !== '16px'));
    t(name + ': Radius überall 16px', radius.length === 0, radius.slice(0, 5).join(','));
    // Symmetrie: Knoepfe 48/44 px (Kopf 40), Felder 48 px, Knopfgruppen gleich breit.
    const sym = await page.evaluate(() => {
      const bad = [];
      document.querySelectorAll('.bit-btn').forEach((b) => {
        if (!b.offsetParent) return;
        const h = Math.round(b.getBoundingClientRect().height);
        const ok = b.closest('.bit-sitehead') ? h === 40 : (h === 48 || (b.classList.contains('bit-btn--sm') && h === 44));
        if (!ok) bad.push('Knopf ' + b.textContent.trim() + ' ' + h + 'px');
      });
      document.querySelectorAll('.bit-field input.ctl, .bit-search input').forEach((f) => {
        const h = Math.round(f.getBoundingClientRect().height);
        if (f.offsetParent && h !== 48) bad.push('Feld ' + (f.name || f.placeholder) + ' ' + h + 'px');
      });
      document.querySelectorAll('.actions').forEach((g) => {
        const w = [...g.querySelectorAll('.bit-btn')].filter((b) => b.offsetParent).map((b) => Math.round(b.getBoundingClientRect().width));
        if (w.length > 1 && Math.max(...w) - Math.min(...w) > 1) bad.push('Gruppe ungleich ' + w.join('/'));
      });
      return bad;
    });
    t(name + ': gleiche Masse (Knöpfe, Felder, Gruppen)', sym.length === 0, sym.slice(0, 4).join(' | '));
    // Kein Foto doppelt auf derselben Seite (ausser Fahrzeugliste/-detail mit Demo-Bildern).
    if (!/^0[239]-|^09/.test(name)) {
      const dup = await page.evaluate(() => {
        const key = (src) => src.split('/').pop().replace(/\.(jpe?g|png|webp)$/i, '').replace(/-\d+x\d+$/, '');
        const seen = {}; const d = [];
        [...document.querySelectorAll('main img')].filter((i) => !i.closest('.thumbs') && !/logo/.test(i.src)).forEach((i) => {
          const k = key(i.currentSrc || i.src); if (seen[k]) d.push(k); seen[k] = 1;
        });
        return d;
      });
      t(name + ': kein Foto doppelt', dup.length === 0, dup.join(', '));
    }
    // Bis ans Ende scrollen, damit Lazy-Bilder laden.
    await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 700) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 40)); } window.scrollTo(0, 0); });
    await page.waitForTimeout(300);
    const broken = await page.evaluate(() => [...document.images].filter((i) => i.complete && i.naturalWidth === 0).map((i) => i.src));
    t(name + ': alle Bilder laden', broken.length === 0, broken.join(' '));
    // Nur fuers Bildschirmfoto: den Scroll-Abschnitt «Der Platz» flach legen (sonst leerer Streifen).
    await page.addStyleTag({ content: '.platz{height:auto!important}.platz .stick{position:relative!important}' });
    await page.screenshot({ path: path.join(OUT, name + '.jpg'), fullPage: true, type: 'jpeg', quality: 78 });
    // Die 404-Seite meldet ihren eigenen Status als Konsolenfehler – das ist gewollt.
    if (name === '10-404') consoleErrors.splice(errCount);
  }
  t('keine JavaScript-Fehler', consoleErrors.length === 0, consoleErrors.join(' | '));

  /* ---------- Startseite: Zahlen stimmen ---------- */
  await page.goto(BASE + '/', { waitUntil: 'networkidle' });
  const cards = await page.locator('.grid-veh .bit-veh').count();
  t('Start: drei Fahrzeuge', cards === 3, String(cards));
  const platzBtn = await page.locator('.platz .bit-btn').innerText();
  t('Start: «Alle 11 ansehen» (echte Zahl)', /Alle 11 ansehen/.test(platzBtn), platzBtn);
  t('Start: vier Dienstleistungen', (await page.locator('.svc').count()) === 4);
  await page.locator('.svc').first().click({ position: { x: 40, y: 40 } });
  await page.waitForURL(/dienstleistungen\/an-und-verkauf\/$/);
  t('Karte führt auf eigene Seite', /dienstleistungen\/an-und-verkauf\/$/.test(page.url()), page.url());
  t('Dienstleistung: Text aus Admin', /Eintausch/.test(await page.locator('.prose').innerText()));
  t('Dienstleistung: drei weitere Karten', (await page.locator('.svc').count()) === 3);
  await page.goto(BASE + '/', { waitUntil: 'networkidle' });
  t('Start: Telefon im Kopf', (await page.locator('.bit-sitehead a.tel').getAttribute('href')) === 'tel:+41315520002');

  /* ---------- Fahrzeuge: Sofort-Suche ---------- */
  await page.goto(BASE + '/fahrzeuge/', { waitUntil: 'networkidle' });
  const label = () => page.locator('[data-count-label]').innerText();
  t('Liste: 11 Fahrzeuge', /11 Fahrzeuge auf dem Platz/.test(await label()), await label());
  await page.getByRole('button', { name: 'BMW' }).click();
  t('Chip BMW: 2 von 11', (await label()) === '2 von 11 Fahrzeugen', await label());
  await page.getByRole('button', { name: 'Alle', exact: true }).click();
  await page.fill('[data-q]', 'pdk');
  t('Suche «pdk»: 2 Porsche', (await label()) === '2 von 11 Fahrzeugen', await label());
  await page.fill('[data-q]', '');
  await page.locator('[data-range="price"]').fill('40000');
  const priceLabel = await page.locator('[data-out="price"]').innerText();
  t('Regler Preis bis CHF 40’000', priceLabel === 'CHF 40’000', priceLabel);
  t('Preis ≤ 40’000: 5 Treffer', (await label()) === '5 von 11 Fahrzeugen', await label());
  await page.locator('[data-range="price"]').fill('20000');
  t('Nichts passt → Hinweis', await page.locator('[data-empty]').isVisible());
  await page.screenshot({ path: path.join(OUT, '11-fahrzeuge-kein-treffer.jpg'), type: 'jpeg', quality: 78 });
  await page.locator('[data-empty] [data-reset]').click();
  t('Zurücksetzen: wieder 11', /11 Fahrzeuge auf dem Platz/.test(await label()), await label());
  await page.getByRole('button', { name: 'Porsche' }).click();
  await page.evaluate(() => window.scrollTo(0, 380));
  await page.screenshot({ path: path.join(OUT, '12-fahrzeuge-filter-porsche.jpg'), type: 'jpeg', quality: 78 });

  /* ---------- Fahrzeug: Galerie ---------- */
  await page.goto(BASE + '/fahrzeuge/mercedes-benz-amg-gt-c/', { waitUntil: 'networkidle' });
  const before = await page.locator('[data-main]').getAttribute('src');
  await page.locator('.thumbs button').nth(1).click();
  const after = await page.locator('[data-main]').getAttribute('src');
  t('Galerie: Bild wechselt', before !== after);
  t('Detail: Preis CHF 129’900', (await page.locator('.detail .price').innerText()) === 'CHF 129’900');
  t('Detail: kein Leasing-Rechner', (await page.locator('[data-leasing], .leasing').count()) === 0 && !/Leasing/.test(await page.locator('main').innerText()));
  t('Detail: Daten-Tabelle', (await page.locator('.bit-kv > div').count()) >= 8);
  t('Detail: drei ähnliche', (await page.locator('.grid-veh .bit-veh').count()) === 3);
  const ld = await page.locator('script[type="application/ld+json"]').innerText();
  t('Detail: schema.org Car', /"@type":"Car"/.test(ld) && /"priceCurrency":"CHF"/.test(ld));

  /* ---------- Kontaktformular ---------- */
  await page.goto(BASE + '/fahrzeuge/porsche-911-turbo/', { waitUntil: 'networkidle' });
  const href = await page.getByRole('link', { name: 'Termin anfragen' }).getAttribute('href');
  t('Termin anfragen: Link mit Fahrzeug', /kontakt\/\?fz=\d+#formular$/.test(href), href);
  await page.goto(href, { waitUntil: 'networkidle' });
  const pre = await page.locator('textarea[name="msg"]').inputValue();
  t('Termin anfragen: Fahrzeug vorausgefüllt', /Porsche 911 Turbo/.test(pre), pre);
  await page.fill('input[name="name"]', 'Test Person (E2E)');
  await page.fill('input[name="tel"]', '079 000 00 00');
  await page.fill('input[name="mail"]', 'test@example.invalid');
  await page.check('input[name="ok"]');
  await page.waitForTimeout(3200); // Mindestzeit gegen Spam
  await page.getByRole('button', { name: 'Nachricht senden' }).click();
  await page.waitForURL(/gesendet=/);
  const note = await page.locator('.form-note').innerText();
  t('Formular: Danke-Meldung', /Danke/.test(note), note);
  await page.locator('#formular').scrollIntoViewIfNeeded();
  await page.screenshot({ path: path.join(OUT, '13-kontakt-gesendet.jpg'), type: 'jpeg', quality: 78 });

  /* ---------- Bewegung (ohne «Bewegung reduzieren») ---------- */
  const ac = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const ap = await ac.newPage();
  await ap.goto(BASE + '/', { waitUntil: 'networkidle' });
  t('Animation: eingeschaltet', await ap.evaluate(() => document.documentElement.classList.contains('js-anim')));
  await ap.waitForTimeout(900);
  t('Animation: Hero steigt auf', await ap.evaluate(() => document.querySelector('.hero h1').classList.contains('is-in')));
  t('Animation: Kicker-Linie zeichnet sich', await ap.evaluate(() => getComputedStyle(document.querySelector('.hero .bit-kicker, .sec-head .bit-kicker'), '::before').width === '18px'));
  t('Animation: Seitenwechsel (View Transition)', await ap.evaluate(() => [...document.styleSheets].some((sh) => { try { return [...sh.cssRules].some((r) => r.cssText.startsWith('@view-transition')); } catch (e) { return false; } })));
  await ap.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 500) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 60)); } });
  await ap.waitForTimeout(900);
  const notIn = await ap.evaluate(() => [...document.querySelectorAll('[data-rv]')].filter((e) => e.offsetParent && !e.classList.contains('is-in')).length);
  t('Animation: alle Blöcke nach dem Scrollen sichtbar', notIn === 0, notIn + ' nicht eingeblendet');
  await ap.evaluate(() => window.scrollTo(0, 0));
  await ap.waitForTimeout(400);
  for (let i = 0; i < 24; i++) { await ap.mouse.move(500 + i * 18, 300 + i * 6); await ap.waitForTimeout(16); }
  await ap.waitForTimeout(250);
  const follow = await ap.evaluate(() => { const m = document.querySelector('.bit-follow'); return m ? { on: m.classList.contains('on'), t: m.style.transform, src: m.src } : null; });
  t('Maus: «bit» folgt dem Zeiger', !!follow && follow.on && /bit-mark\.png/.test(follow.src) && follow.t.includes('translate3d'), JSON.stringify(follow));
  await ap.screenshot({ path: path.join(OUT, '25-animation-maus-bit.jpg'), type: 'jpeg', quality: 80, clip: { x: 300, y: 150, width: 900, height: 420 } });
  // Den Seitenwechsel fuer den Test anhalten (Listener nach dem der Seite), dann die Linie pruefen.
  await ap.evaluate(() => document.addEventListener('click', (e) => { if (e.target.closest('a')) e.preventDefault(); }));
  await ap.locator('.bit-sitehead nav a', { hasText: 'Fahrzeuge' }).click();
  await ap.waitForTimeout(450);
  t('Klick: Linie läuft oben über die Seite', await ap.evaluate(() => document.querySelector('.navline').classList.contains('go')));
  await ap.screenshot({ path: path.join(OUT, '26-animation-linie-klick.jpg'), type: 'jpeg', quality: 80, clip: { x: 0, y: 0, width: 1440, height: 120 } });
  await ac.close();

  /* ---------- Handy ---------- */
  const mob = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true, reducedMotion: 'reduce' });
  const mp = await mob.newPage();
  for (const [name, url] of [['m1-start', '/'], ['m2-fahrzeuge', '/fahrzeuge/'], ['m3-fahrzeug-detail', '/fahrzeuge/porsche-911-turbo/'], ['m4-kontakt', '/kontakt/'], ['m5-impressum', '/impressum/']]) {
    await mp.goto(BASE + url, { waitUntil: 'networkidle' });
    const overflow = await mp.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    t(name + ': kein seitliches Scrollen', overflow <= 0, overflow + 'px');
    await mp.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 30)); } window.scrollTo(0, 0); });
    await mp.waitForTimeout(300);
    await mp.addStyleTag({ content: '.platz{height:auto!important}.platz .stick{position:relative!important}' });
    await mp.screenshot({ path: path.join(OUT, name + '.jpg'), fullPage: true, type: 'jpeg', quality: 70 });
  }

  /* ---------- Login fuer Sabit ---------- */
  const lc = await browser.newContext({ viewport: { width: 1280, height: 860 } });
  const lp = await lc.newPage();
  await lp.goto(BASE + '/wp-login.php', { waitUntil: 'networkidle' });
  await lp.screenshot({ path: path.join(OUT, '20-login.jpg'), type: 'jpeg', quality: 80 });
  if (USER && PASS) {
    await lp.fill('#user_login', USER);
    await lp.fill('#user_pass', PASS);
    await Promise.all([lp.waitForNavigation(), lp.click('#wp-submit')]);
    t('Login: Übersicht erreicht', /wp-admin\/?$/.test(lp.url()) || /wp-admin\/index\.php/.test(lp.url()), lp.url());
    t('Login: BIT-Kacheln da', (await lp.locator('#bit_welcome .bit-tiles a').count()) >= 4);
    t('Login: Beiträge ausgeblendet', (await lp.locator('#menu-posts').count()) === 0);
    await lp.screenshot({ path: path.join(OUT, '21-admin-uebersicht.jpg'), type: 'jpeg', quality: 80 });
    await lp.goto(BASE + '/wp-admin/edit.php?post_type=fahrzeug', { waitUntil: 'networkidle' });
    await lp.screenshot({ path: path.join(OUT, '22-admin-fahrzeuge.jpg'), type: 'jpeg', quality: 80 });
    await lp.goto(BASE + '/wp-admin/edit.php?post_type=bit_anfrage', { waitUntil: 'networkidle' });
    t('Admin: Anfrage aus dem Test gespeichert', /Test Person \(E2E\)/.test(await lp.locator('#the-list').innerText()));
    await lp.screenshot({ path: path.join(OUT, '23-admin-anfragen.jpg'), type: 'jpeg', quality: 80 });
    const first = await lp.locator('#the-list a.row-title').first().getAttribute('href');
    await lp.goto(BASE + '/wp-admin/edit.php?post_type=fahrzeug', { waitUntil: 'networkidle' });
    const edit = await lp.locator('#the-list a.row-title').first().getAttribute('href');
    await lp.goto(edit, { waitUntil: 'load' });
    await lp.waitForSelector('#bit_vehicle_data');
    t('Admin: Fahrzeugmaske mit Feldern', (await lp.locator('#bit_vehicle_data input[name="bit_price"]').count()) === 1);
    await lp.screenshot({ path: path.join(OUT, '24-admin-fahrzeug-bearbeiten.jpg'), fullPage: true, type: 'jpeg', quality: 78 });
    void first;
  }

  /* ---------- AutoScout24-Probelauf im Admin (nur mit lokaler Attrappe) ---------- */
  if (process.env.ADMIN_USER && process.env.ADMIN_PASS && process.env.AS24_MOCK === '1') {
    const xc = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const xp = await xc.newPage();
    await xp.goto(BASE + '/wp-login.php', { waitUntil: 'networkidle' });
    await xp.fill('#user_login', process.env.ADMIN_USER);
    await xp.fill('#user_pass', process.env.ADMIN_PASS);
    await Promise.all([xp.waitForNavigation(), xp.click('#wp-submit')]);
    await xp.goto(BASE + '/wp-admin/tools.php?page=bit-as24', { waitUntil: 'networkidle' });
    t('AS24: Probelauf-Knopf gesperrt ohne Zugangsdaten', await xp.locator('input[value="Probelauf (nichts speichern)"]').isDisabled());
    await xp.fill('#as24_seller', '12345');
    await xp.fill('#as24_client', 'test');
    await xp.fill('#as24_secret', 'test-secret');
    await Promise.all([xp.waitForNavigation(), xp.click('input[value="Speichern"]')]);
    t('AS24: Secret nicht im Formular sichtbar', (await xp.locator('#as24_secret').inputValue()) === '');
    const before = await xp.evaluate(async (b) => (await (await fetch(b + '/wp-json/wp/v2/fahrzeug?per_page=100')).json()).length, BASE);
    await Promise.all([xp.waitForNavigation(), xp.click('input[value="Probelauf (nichts speichern)"]')]);
    const summary = await xp.locator('#probelauf + p').innerText();
    t('AS24: Probelauf liest 11, würde 11 anlegen', /11 Inserate gelesen: würde anlegen 11/.test(summary), summary);
    t('AS24: Liste mit Preis und km', /würde anlegen: Mercedes-Benz AMG GT C – CHF 129’900 – 31’500 km/.test(await xp.locator('.bit-probe').innerText()));
    const after = await xp.evaluate(async (b) => (await (await fetch(b + '/wp-json/wp/v2/fahrzeug?per_page=100')).json()).length, BASE);
    t('AS24: Probelauf hat nichts gespeichert', before === after, before + ' → ' + after);
    const dl = await Promise.all([xp.waitForEvent('download'), xp.click('text=Rohdaten herunterladen (JSON)')]);
    const rawTxt = fs.readFileSync(await dl[0].path(), 'utf8');
    t('AS24: Rohdaten herunterladbar', /probe-demo-1001/.test(rawTxt) && !/mock-token/.test(rawTxt));
    const direct = await xp.request.get(BASE + '/wp-content/uploads/bit-as24/' + dl[0].suggestedFilename());
    t('AS24: Rohdaten von aussen gesperrt', direct.status() === 403, String(direct.status()));
    await xp.locator('#probelauf').scrollIntoViewIfNeeded();
    await xp.evaluate(() => window.scrollBy(0, -260));
    await xp.screenshot({ path: path.join(OUT, '27-admin-autoscout24-probelauf.jpg'), type: 'jpeg', quality: 80 });
    await xc.close();
  }

  await browser.close();
  console.log('\n' + ok + ' OK, ' + fail + ' FEHLER');
  process.exit(fail ? 1 : 0);
})().catch((e) => { console.error(e); process.exit(2); });
