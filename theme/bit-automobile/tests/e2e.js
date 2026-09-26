/* End-to-End-Test der BIT-Webseite mit Playwright.
 *   BASE=http://localhost:8080 OUT=./screenshots BIT_USER=… BIT_PASS=… node tests/e2e.js
 * Prueft jede Seite (Status, keine PHP-Fehler, Kopf/Fuss, aktiver Menuepunkt),
 * die Sofort-Suche, Galerie, Leasing-Rechner, Kontaktformular und Login,
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

  /* ---------- Fahrzeug: Galerie und Leasing ---------- */
  await page.goto(BASE + '/fahrzeuge/mercedes-benz-amg-gt-c/', { waitUntil: 'networkidle' });
  const before = await page.locator('[data-main]').getAttribute('src');
  await page.locator('.thumbs button').nth(1).click();
  const after = await page.locator('[data-main]').getAttribute('src');
  t('Galerie: Bild wechselt', before !== after);
  t('Detail: Preis CHF 129’900', (await page.locator('.detail .price').innerText()) === 'CHF 129’900');
  const rate1 = await page.locator('[data-out="rate"]').innerText();
  t('Leasing: 48 Mt, 10% = CHF 2’913', rate1 === 'CHF 2’913', rate1);
  await page.locator('[data-in="months"]').fill('60');
  await page.locator('[data-in="down"]').fill('20');
  const rate2 = await page.locator('[data-out="rate"]').innerText();
  t('Leasing: 60 Mt, 20% = CHF 2’156', rate2 === 'CHF 2’156', rate2);
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

  await browser.close();
  console.log('\n' + ok + ' OK, ' + fail + ' FEHLER');
  process.exit(fail ? 1 : 0);
})().catch((e) => { console.error(e); process.exit(2); });
