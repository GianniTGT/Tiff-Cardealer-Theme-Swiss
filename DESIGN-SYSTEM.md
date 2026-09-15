# Design System — Tiff Cardealer Theme

**Repo:** `GianniTGT/Tiff-Cardealer-Theme-Swiss`
**Gehört in:** Claude Project Instructions (zusammen mit `CLAUDE.md` des Manager-Repos)
**Stand:** abgeleitet aus Theme-Version 1.2.0, Ordner `das-v4`, Prefix `das_`

---

## 0. Was dieses Dokument ist

Das Theme existiert seit Monaten und läuft live auf `downtownautosale.com`. Die Gestaltung ist **gewachsen und begründet**, nicht frei wählbar. Dieses Dokument hält fest, was gilt, damit eine spätere Änderung keine frühere Entscheidung aus Unwissenheit rückgängig macht.

**Quelle der Wahrheit ist `style.css`, nicht dieses Dokument.** Weicht der Code ab, gewinnt der Code – und dieses Dokument wird korrigiert. Die Kommentare in `style.css` sind Teil der Spezifikation, nicht Beiwerk: dort steht jeweils, *warum* etwas so ist.

---

## 1. Projektfakten

| | |
|---|---|
| Stack | WordPress-Theme, PHP 8.0+, keine Plugin-Abhängigkeit |
| Custom Post Types | `car`, Team; Taxonomie `carproducer` (hierarchisch: Marke → Modell) |
| Prefix | `das_` (~200 Aufrufstellen), Textdomain `das-v4` |
| Eigene REST-Routen | `das/v1/leads`, `das/v1/vehicle-photos` |
| CSS-Umfang | 1105 Zeilen in einer Datei, 23 nummerierte Abschnitte |
| JS-Umfang | 362 Zeilen in `assets/js/site.js`, kein Framework |
| Primärer Bildschirm | **Mobile.** Über 80 % der Besucher. Desktop ist die Ableitung. |

Der Rename auf `tiff-cardealer` / `tcd_` ist offen und ist **ein eigener Job**, kein Nebeneffekt einer Designänderung.

---

## 2. Arbeitsregeln, die das Projekt schon bezahlt hat

Diese drei gelten vor jeder gestalterischen Überlegung:

1. **Backup im selben Aufruf wie der Edit.** Nicht davor, nicht danach. Novamira ist ein WordPress-Plugin – ein Fatal im Theme nimmt das Werkzeug mit, das den Fehler beheben würde. Am 18. August haben acht Byte Müll in `inc/cars.php` die ganze Seite lahmgelegt, inklusive wp-admin.
2. **Auf der gerenderten Seite prüfen, nie an der Quelle.** Der Quelltext sagt, was er sagen soll. Nur die abgerufene Seite beweist, was der Kunde sieht. Opcache hat schon eine Stunde lang alten Bytecode über einer korrekten Datei ausgeliefert.
3. **Nicht raten, messen.** Die Sandbox erreicht die Live-Site nicht über HTTPS (`000`, Netzwerkrichtlinie). Drei Rateversuche an einer Drittanbieter-URL haben je einen Abend gekostet.

---

## 3. Tokens

Alle Werte stehen in `:root` in `style.css`, Abschnitt 1. **Keine losen Hex-Werte in neuem Code.** Wer eine Farbe braucht, die es nicht gibt, ergänzt sie als Token mit Namen – oder verwendet die vorhandene.

### Farben

```css
--night:#103071;  --night-2:#15418D;  --night-3:#1A4EA8;
--brand:#08318B;  --brand-hover:#0B3EAF;  --brand-wash:#E9EEFA;
--snow:#F7F9FC;   --white:#F9FAFC;    --white-pure:#FFFFFF;
--sky:#8FB3FF;    --aurora:#AFC6F2;   --mist:#C2CEE6;
--steel:#47536E;
--line:#E4E9F4;   --line-strong:#D3DBEC;
--accent:#FFB020; --danger:#B3261E;   --ok:#1B5E33;
```

**Rollen**

| Token | Rolle |
|---|---|
| `--night` | Dunkelflächen: Hero, Topbar, Footer, `.dark`, `.fin-result`, `.page-hero` |
| `--brand` | Preis, primärer Button, aktiver Chip, Links im Fliesstext, Icons |
| `--snow` | Seitenhintergrund (`body`) |
| `--steel` | Sekundärtext auf Hell. Fliesstext in `.entry`, `.sec-head p`, `.card-desc p` |
| `--mist` | Sekundärtext auf Dunkel |
| `--sky` / `--aurora` | Hervorhebung auf Dunkel (`.hero h1 em`, `.eyebrow` in `.dark`) |
| `--line` / `--line-strong` | Rahmen ruhig / Rahmen aktiv, Inputs, Chips |
| `--accent` | **Nur** das `Featured`-Badge. Die einzige warme Farbe im ganzen Theme. |

### Legacy-Tokens — in neuem Code nicht verwenden

```css
--radius:16px;        /* == --r-card */
--amber:#8FB3FF;      /* trägt den Namen der alten Markenfarbe, ist längst Blau */
--amber-dark:#08318B; /* == --brand */
--shadow-lg:...       /* == --sh-3 */
```

Ebenso die Button-Aliasse `.btn-dark` und `.btn-amber` – sie existieren nur, damit `das_car_card()` und älteres Markup weiterlaufen. **Nicht entfernen** (Live-Markup hängt daran), aber **nicht neu verwenden**.

### Radius, Schatten, Rhythmus

```css
--r-btn:12px;  --r-input:12px;  --r-card:16px;  --r-pill:999px;
--sh-1:0 1px 2px rgba(16,48,113,.05);    /* Karten in Ruhe */
--sh-2:0 6px 20px rgba(16,48,113,.07);   /* Karte im Hover, helle Buttons */
--sh-3:0 20px 48px rgba(16,48,113,.13);  /* Overlays, Dropdowns, .about-visual */
--sp-section:52px;  /* ≥940px: 104px */
--maxw:1180px;  --tap:48px;
```

Die Schatten sind blau eingefärbt (`rgba(16,48,113,…)`), nicht grau. Das ist Absicht und hält die Seite farbig geschlossen.

`--sp-section` ist mobil bewusst kleiner: 72px Desktop-Rhythmus auf 390px Breite ergeben bei sieben Abschnitten über 1000px Leere.

**Breakpoints im Einsatz:** 480 · 520 · 560 · 620 · 640 · 720 · 880 · **940** (Hauptumbruch: Navigation, Hero-Grid, Fahrzeug-Grid) · 941.

---

## 4. Typografie

Zwei Familien mit klarer Aufgabenteilung:

```css
--display:'Barlow Condensed','Arial Narrow',sans-serif;  /* h1–h3, Preise, Zahlen */
--body:'Barlow','Segoe UI',sans-serif;                   /* alles andere */
```

**`--display` ist die Identität des Themes:** Barlow Condensed, Gewicht 800, `text-transform:uppercase`, `line-height:1.04`, `letter-spacing:.4px`. Das ist der Autohaus-Ton – Nummernschild, Datenblatt, Werkstattschild. Er gilt für Überschriften, den Preis (`.price`, `.sc-price`), die Statistikzahlen (`.stat b`), den Finanzierungsbetrag (`.fr-amount`) und den Footer-Slogan.

**Buttons sind ausdrücklich ausgenommen:** `.btn` setzt `font-family:var(--body)`, `text-transform:none`, `letter-spacing:0`. Buttontexte stehen in normaler Satzschreibung.

| Element | Grösse |
|---|---|
| `.hero h1` | `clamp(42px,6.6vw,77px)` / ≤940px: `clamp(31px,7.4vw,44px)` |
| `h2` | `clamp(28px,4vw,42px)` |
| `.car-head h1` | `clamp(27px,4.4vw,44px)` |
| `.fr-amount` | `clamp(48px,9vw,60px)` |
| `.card h3` | 19.5px · `.price` 22px · `.car-head .price` 32px |
| Body | 16px / 1.6 |
| Eyebrow, Kicker, Labels | 12.5px, 600, `letter-spacing:2.4px`, uppercase |

Die getrackten Versal-Labels (`.eyebrow`, `.kicker`, `.sc-label`, `footer h4`) sind hier **kein Vorlagenreflex, sondern die Marke**. `.eyebrow` trägt zusätzlich einen 22px-Strich via `::before`. Beibehalten.

VIN wird in Monospace gesetzt (`.spec-vin .spec-v`) und belegt die volle Zeilenbreite – 17 Zeichen passen in keine halbe Zelle.

---

## 5. Komponenten

Klassennamen sind verbindlich. Neue Komponenten fügen sich in die bestehende Nummerierung von `style.css` ein.

### Buttons — eine Komponente, vier Skins
`.btn` + `.btn-brand` (primär) · `.btn-light` (auf Dunkel) · `.btn-ghost` (auf Foto/Dunkel) · `.btn-quiet` (sekundär auf Hell). Mindesthöhe `--tap`, Padding 13/22, Radius `--r-btn`, Gewicht 600, optionales SVG 18×18 mit `fill:currentColor`.

**Spezifitätsfalle, schon einmal aufgetreten:** `nav.menu a` (0-1-2) schlägt `.btn-dark` (0-1-0), der Call-Button rendert dann navy auf navy und sieht leer aus. Deshalb existiert `nav.menu a.btn` (0-2-2). Wer im Header Buttons anfasst, prüft das erneut.

### Topbar + Header
`.topbar` auf `--night` mit Telefon und Öffnungszeiten – der wichtigste Konversionskanal, auf jeder Seite sichtbar. `.hours-note` wird unter 720px ausgeblendet.

`header.site` ist **`position:static`, nicht sticky.** Grund steht im Code: sticky verdeckte auf dem Handy die Hero-CTAs. Nicht wieder auf sticky stellen.

Navigation ab ≤940px als aufklappbares Panel (`.hamb`, `nav.menu.open`). Submenüs sind mobil **immer offen und eingerückt** – nichts zu entdecken, nichts zweimal zu tippen. Desktop: Hover/Focus-Dropdown mit Caret.

### Hero
`.hero` auf `--night` mit zwei radialen `--sky`-Verläufen. Grid `.82fr 1.18fr`, ab ≤940px einspaltig.

**Die DOM-Reihenfolge ist die Leserichtung, und sie wird nicht per `order` umgestellt.** Früher standen Showcase und Suche über der Copy; ein Handy landete dann auf einem Foto und einem Vierfeld-Formular und traf den einen Satz, den das Geschäft zu sagen hat, erst an dritter Stelle. Reihenfolge: **wer wir sind → ein Fahrzeug → das Werkzeug, eines zu finden.**

Mobil wird `.sc-stage` randlos gezogen (`margin:0 -20px`, `border-radius:0`, 4/3) und `.sc-label` ausgeblendet: das Foto führt, die Copy folgt.

`.badge` sitzt absolut oben links auf dem Bild, `--accent` mit Text `#3D2600`; `.badge-sold` auf `--danger`.

### Fahrzeugkarte `.card` — die zentrale Komponente
Aufbau: `.card-img` (16/10, `object-fit:cover`, Fallback-SVG bei fehlendem Foto) → `.card-body` mit `.card-top` (h3 + `.price`) → `.card-specs` → Button `.btn` mit `margin-top:auto`.

Grid: `repeat(auto-fill,minmax(268px,1fr))`, Gap 20px.

Zwei Regeln aus den Code-Kommentaren:

- **`.card-specs` ist Flex mit Umbruch, kein festes Zweispaltengrid.** Eine Karte, die drei Dinge über ein Fahrzeug weiss, soll dort keine Lücke zeichnen, wo das vierte stünde.
- **Ein Spec ohne Wert fällt raus, er druckt keinen Gedankenstrich.** Eine leere Zeile sagt dem Käufer nur, dass wir unser eigenes Auto nicht kennen.

Die Captions sind entfernt, die Icons tragen sie: „Engine: 4 Cylinder" musste gelesen werden, ein Getriebe-Icon neben „Automatic" nicht – und vier Labels auf zwanzig Karten sind zwanzigmal dieselben vier Wörter.

Hover: `translateY(-3px)` + `--sh-2`. Das ist die einzige Karten-Bewegung im Theme.

### Filter-Chips
`.chip` als Pille, `.chip.active` füllt mit `--brand`. Im Archiv sind es Links (`a.chip`) mit Trefferzahl `.chip-n`. Leerer Zustand: `.grid-empty` mit gestricheltem Rahmen.

### Fahrzeugdetail
`.car-grid` = `1.25fr .75fr`, ab ≤940px einspaltig.

`.car-stage.is-slider` ist ein **Scroll-Container mit `scroll-snap-type:x mandatory`** – das Wischen ist Browserverhalten und braucht kein JavaScript. Das Script bewegt nur Pfeile, Zähler und Thumbnails mit. Pfeile `.car-nav` sind unter `@media (hover:none)` ausgeblendet: ein Touchgerät wischt, Pfeile würden nur das Foto verdecken.

`.spec-list`: Icon auf `--brand-wash`-Kreis, darüber Label in 10.5px Versalien, darunter Wert in 15.5px/600. **Keine Rahmen um die einzelnen Specs.** Vierzehn umrandete Kästchen in einer 414px-Spalte haben um jede Tatsache einen Rahmen gezogen und die Tatsache selbst auf 15px gesetzt – das Icon konkurrierte mit dem eigenen Rahmen.

`.car-aside` ist **nicht sticky**: seit das Anfrageformular in dieser Spalte steht, ist sie die höhere der beiden, und eine sticky Box höher als das Fenster versteckt ihren eigenen Absenden-Button.

### Finanzierungsrechner
`.fin-grid` zweispaltig, `.fin-result` auf `--night` mit `.fr-amount` in Display-Schrift. Ab 881px ist `.fin-result` sticky (`top:24px`). Live-Berechnung, kein „Berechnen"-Button. `.fin-note` trägt den Unverbindlichkeitshinweis.

### Formulare
`.cform` / `.fin-card`. Labels **über** dem Feld, 13.5px, 600, Versalien, `--steel`. Felder `min-height:var(--tap)`, `1.5px solid var(--line-strong)`, Fokus setzt `border-color:var(--brand)`. Honeypot `.das-hp` gehört in jedes Formular. Meldungen als `.das-notice-ok` / `.das-notice-err`.

### Footer
`.foot-grid` = `1.4fr 1fr 1fr 1fr`, bricht auf 2 und dann 1 Spalte. Öffnungszeiten als echte `table.hours` mit `tr.today`-Hervorhebung. `.socials` als 42px-Quadrate.

### Bewegung
Genau zwei Muster: `.rv` → `.rv.in` (Scroll-Reveal, 16px hoch, 0.5s) und `@keyframes fadeSwap` (0.4s) beim Bildwechsel im Showcase. `prefers-reduced-motion` schaltet global alles ab – das steht bereits in Abschnitt 2 von `style.css` und bleibt.

Seit 15.09.2026 liegt darauf die **Motion-Ebene** (`style.css` §24). Sie fügt *kein* drittes
Muster hinzu: alles darin ist `.rv` oder `fadeSwap`, versehen mit einer Verzögerung, einer
Richtung oder einem Transform. Eine Kurve für alles, `cubic-bezier(.22,.68,.24,1)` über 0.62s.
Jede Regel ist per Klasse opt-in – ein Template, das keine Klasse setzt, rendert exakt wie
vorher. `prefers-reduced-motion` schaltet weiterhin alles ab.

| Klasse | Was sie tut |
|---|---|
| `.rv-left` / `.rv-right` / `.rv-scale` | dieselbe Einblendung, mit Richtung |
| `.rv-stagger` | Kinder kommen 70ms versetzt, gedeckelt bei 8 Stufen |
| `.hero-in` / `.hero-in-right` | Hero spielt beim Paint einmal, Verzögerung folgt der DOM-Reihenfolge |
| `.hero-drift` | die Radial-Washes driften 14px über 28s |
| `.roadline-rolling` | die Fahrbahnmarkierung rollt einen Strich plus Lücke (54px) pro 1.6s |
| `.mk-lane` / `.mk-track` | die Markenspur unter dem Hero (§25), 34s nahtlos, Pause beim Hover |
| `.num-tick` | Statistik-Zahlen zählen einmal hoch, wenn die Kachel ins Bild kommt |
| `.motion-calm` / `.motion-still` | zwei Stufen, die von der Ebene abziehen – gesetzt über `body_class` |

### Refined-Look – opt-in (`style.css` §26)
Auf Wunsch des Inhabers, 15.09.2026 („Apple Look“). Alles hängt an `.look-refined` auf `<body>`,
also bleibt der eigene Look des Themes unberührt und beide sind durch eine Klasse vergleichbar.
Geändert wird **nur die Oberflächenbehandlung** – kein Token-Wert, keine Typo-Stufe, keine
Layout-Regel. Kapsel-Controls (`--r-cap` 980px), Füllung mit Oberlicht statt Flachfarbe,
kürzere weiche Schatten, `scale(.97)` beim Drücken statt 1px Weg, und `--edge #5AC8FA` als
einzige neue Farbe (bewegte Linie + aktiver Showcase-Punkt).

**Hier – und nur hier – benutzt das Produkt `backdrop-filter`.** Header, Hero-Suche, Submenüs und
der Cookie-Hinweis werden zu Milchglas. Genau deshalb ist die Ebene opt-in; ausserhalb von
`.look-refined` gilt „kein Blur“ unverändert weiter.

### Die drei Schalter
`functions.php` setzt die drei Entscheidungen der Design-Session als Default und hängt jede an
einen eigenen Filter – eine Zeile im Child-Theme stellt sie zurück, ohne CSS anzufassen:

| Filter | Default | Alternativen |
|---|---|---|
| `das_look` | `refined` | `theme` (flach, wie vorher) |
| `das_motion_level` | `calm` | `alive` (alles aus §24), `still` (nur der Hover-Lift) |
| `das_shelf` | `roomy` (330px / 30px) | `regular` (268/20, die alten Werte), `dense` (236/14) |

---

## 6. Fahrzeugdaten

### Die 16 öffentlichen Felder
Definiert in `das_car_meta_keys()` (`inc/cars.php`):

`car_vin` · `car_price` · `car_price_plus` · `car_year` · `car_mileage` · `car_body` · `car_fuel_type` · `car_transmission` · `car_engine_size` · `car_engine_additional` · `car_exterior_color` · `car_interrior_color` · `car_doors_count` · `car_owner_number` · `car_is_featured` · `car_is_sold`

> `car_interrior_color` ist so geschrieben, mit doppeltem r. Das ist der Schlüssel in der Datenbank der Live-Site. **Nicht stillschweigend korrigieren** – eine Umbenennung ist eine Datenmigration, keine Tippfehlerkorrektur.

Marke und Modell kommen **nicht** aus Meta, sondern aus der Hierarchie von `carproducer`: Elternterm = Marke, Kindterm = Modell. Der Grund steht im Code: `fields => names` liefert alphabetisch, und ein Audi A3 meldete daraufhin „A3" als Marke – wonach auch jeder Markenfilter gruppiert hätte.

### Die Privacy-Boundary ist unantastbar
`inc/privacy-boundary.php` blockt jeden Meta-Schlüssel, der nach Finanzdaten aussieht (`purchase`, `profit`, `margin`, `cost`, `markup`, `invoice`, `auction`, `reconditioning`, `floorplan` …), auf **jedem** Weg – REST, Admin, Plugin, Direktaufruf – und protokolliert den Versuch.

**Eine Änderung, die diese Wand verbreitert, ist eine Änderung, die die Einkaufspreise eines Händlers ausleitet.** Wer ein neues Feld braucht, ergänzt es in `das_car_meta_keys()` und prüft, dass der Name nicht in ein Verbotsmuster fällt.

### Formatierung — hier sitzt die Schweiz-Aufgabe
`das_get_car()` formatiert heute fest auf den US-Markt:

```php
'price_txt' => $price ? '$' . number_format_i18n( $price ) : 'Call for price',
'miles_txt' => $mileage ? number_format_i18n( $mileage ) . ' mi' : '—',
```

Für eine Schweizer Niederlassung sind Währungssymbol, Tausendertrennung (`24'900`) und Einheit (`km`) **keine Textänderung an dreissig Stellen**, sondern gehören in eine Formatierungsschicht mit einer Option. Solange das nicht gebaut ist: nicht an einzelnen Templates herumflicken.

Ebenfalls US-gebunden und noch offen: `das_body_types()` liefert `Pickup Truck` und `Sedan`, `das_fuel_types()` kennt `gas` und `petrol` nebeneinander. Für CH fehlen Kombi/Limousine-Benennung, MFK-Status und Jahrgang als `MM.JJJJ` statt reinem Jahr.

**Die Bestandsrichtung dreht sich ausserdem um.** Alaska ist *App → Website*. Ein Schweizer Händler listet zuerst auf AutoScout24, die Website wird *von dort* gespeist. `das/v1/vehicle-photos` und die ganze Push-Richtung sind nicht das, was ein Schweizer Standort braucht. Der Mechanismus ist **nicht gemessen** – nicht raten.

---

## 7. Sprache

Sachlich, kurz, in der Stimme eines Verkäufers, der den Wagen kennt. Keine Ausrufezeichen, keine Superlative ohne Beleg.

Eine Aktion heisst überall gleich: Der Button `View details` führt zur Detailseite, nicht zu „Mehr erfahren". Buttontexte sind Verb plus Objekt und in Satzschreibung – die Versalien bleiben den Überschriften vorbehalten.

Leerzustände sind Aufforderungen, keine Mitteilungen. `.grid-empty` sagt, was zu tun ist.

Fehlermeldungen benennen Problem und Lösung. Keine Entschuldigungen.

---

## 8. Qualitätsboden

Er steht bereits im Code und wird nicht unterschritten:

- `:focus-visible` mit `3px solid var(--brand)`, Offset 2px, auf **jedem** Bedienelement. Nie `outline:none` ohne Ersatz.
- `.skip-link` und `.screen-reader-text` bleiben.
- Touch-Ziele `--tap` = 48px. Submenü-Einträge 44px.
- `prefers-reduced-motion` global respektiert.
- Bilder mit `width`/`height` und `loading="lazy"` – ausser dem Hero.
- Seitenverhältnisse per CSS reserviert (`aspect-ratio`), damit nachladende Fotos das Layout nicht springen lassen.
- Keine Plugin-Abhängigkeit. Das Theme bringt seine Post Types selbst mit.

---

## 9. Verboten

- Header wieder sticky machen
- `order`-Umstellungen im Hero-Grid
- `.car-aside` oder `.fin-result` mobil sticky
- Neue lose Hex-Werte statt Tokens
- `--amber`, `--amber-dark`, `--radius`, `--shadow-lg`, `.btn-amber`, `.btn-dark` in neuem Code
- `--accent` für irgendetwas ausser dem Featured-Badge
- Gedankenstrich als Platzhalter in `.card-specs`
- Rahmen um einzelne `.spec-list`-Einträge
- Pfeile in `.car-nav` auf Touchgeräten einblenden
- `car_interrior_color` umbenennen
- Die Verbotsmuster in `inc/privacy-boundary.php` lockern
- Zusätzliche Scroll-Animationen neben `.rv` – §24 hält die Regel ein, statt sie zu brechen:
  es gibt dort keine neue Vokabel, nur Verzögerung und Richtung von `.rv`
- `backdrop-filter` ausserhalb von `.look-refined`
- Herstellerlogos nachzeichnen. Die Markenspur setzt Wortmarken in Barlow Condensed;
  lizenzierte SVGs kommen über den Filter `das_make_logos` herein oder gar nicht
- Eine Einblendung auf ein Bedienelement legen, das ein Käufer braucht –
  `.car-aside` bekommt deshalb bewusst kein `.rv`
- Ein zweiter CSS- oder JS-Build. Eine `style.css`, eine `site.js`.

---

## 10. Checkliste vor jeder Änderung

- [ ] Backup im selben Aufruf wie der Edit geschrieben
- [ ] Auf 390px geprüft, bevor auf Desktop geprüft wurde
- [ ] Nur Tokens verwendet, kein Punkt aus Abschnitt 9 verletzt
- [ ] CSS-Spezifität geprüft, falls Header, Buttons oder `.card` betroffen
- [ ] Fokusring auf allen neuen Bedienelementen sichtbar
- [ ] Auf der **gerenderten** Seite verifiziert, nicht an der Quelle
- [ ] Bei neuen Meta-Feldern: gegen `das_forbidden_meta_patterns()` geprüft
- [ ] Fehlende Angaben als Platzhalter markiert, nichts erfunden
