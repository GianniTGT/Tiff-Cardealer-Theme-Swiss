# Tiff Cardealer Suisse — von der Desktop-App zur Schweizer Cloud

**Stand: 12. September 2026. Einschätzung und Architekturvorschlag. Nichts davon ist gebaut.**

Dieses Dokument ist auf **Deutsch**, und das ist eine Entscheidung, keine Nachlässigkeit:
der Pilotkunde liest Deutsch, der Treuhänder liest Deutsch, und die Oberfläche des
Schweizer Produkts wird Deutsch sein. **CLAUDE.md §3 gilt hier nicht.** Diese Regel
(«alle Produktausgabe auf Englisch») war eine Entscheidung *für Alaska*, weil dort die
Kunden Englisch sprechen. Für die Schweiz gilt sie umgekehrt — und die i18n-Schicht ist
genau dafür gebaut worden (`DICTIONARIES` in `src/i18n/index.js`, eine Zeile pro Sprache).

Alles Gemessene in diesem Dokument stammt aus dem Code dieses Repos, gelesen am
12. September 2026 auf `1.4.0`. Alles Rechtliche stammt aus Wissen, nicht aus einer
Quelle, die ich in dieser Session öffnen konnte — §6 sagt, welche Sätze ein Mensch
gegenprüfen muss, bevor die erste Rechnung rausgeht.

---

## 0. Die kurze Antwort

**Ja — aber es ist keine Portierung, sondern ein zweites Produkt, das die Fachlogik erbt.**

Der Unterschied ist nicht Wortklauberei, er entscheidet über den Aufwand. Wer «wir stellen
TCM in die Cloud» sagt, plant einen Umbau von etwas Bestehendem und rechnet mit vier
Wochen. Wer «wir bauen ein Schweizer Produkt und nehmen die Fachlogik mit» sagt, plant das
Richtige — und stellt dann fest, dass überraschend viel mitkommt.

**Was tatsächlich mitkommt, gemessen und nicht geschätzt:**

| | |
|---|---|
| **35 von 55 Modulen in `lib/`** importieren *gar nichts* | technisch überall lauffähig — **wie viel davon wirklich nützlich ist, steht in §1.3** |
| **105 Aufrufe in `src/api.js`** hängen an *einer* Funktion `call()` | die Naht zwischen Oberfläche und Daten existiert bereits |
| **106 IPC-Kanäle** gehen durch *ein* `handle()`, **98 davon hinter `guard()`** | die Naht, die ein Netzwerk braucht, ist dort, wo die Naht schon ist |
| Die restlichen acht sind **absichtlich** ungeschützt — Login, Ersteinrichtung, Passwort-Wiederherstellung, Absturzprotokoll | jede davon hat in CLAUDE.md eine eigene Begründung, und die gilt auf einem Server genauso |
| Der Renderer hat **null** Datenbankzugriff | die Bildschirme müssen beim Umbau nicht angefasst werden |

Diese Disziplin — «dieses Modul importiert nichts» — steht in CLAUDE.md an zwei Dutzend
Stellen als Begründung für einen Mehraufwand, der sich damals nicht ausgezahlt hat.
**Jetzt zahlt er sich aus.** Das ist der eigentliche Grund, warum diese Idee überhaupt
schlank machbar ist.

**Was nicht mitkommt:** SQLCipher, DPAPI/`safeStorage`, `electron/`, der Installer, das
Backup-Modul, die LAN-Telefonbrücke, der Etikettendruck über `webContents.print()`, und
`scripts/status.mjs` in seiner heutigen Form.

**Und `MULTI-USER.md` §8 hat genau das hier schon einmal vertagt** — «Ausserhalb des Büros.
Bewusst vertagt. Das wird Route 2.» Route 2 kommt jetzt, aber aus der besseren Richtung:
nicht als Migration eines laufenden Betriebs mit sieben Jahren Daten, sondern als
Neukunde, bei dem man sauber anfangen darf.

**Drei Risiken, in dieser Reihenfolge:**

1. **Die Steuer.** Occasionshandel in der Schweiz wird seit 2018 anders besteuert, als die
   Aufgabenstellung annimmt (§4.3). Falsch gebaut heisst: jede Rechnung ist falsch, und das
   merkt man bei der MWST-Abrechnung, nicht beim Testen.
2. **Die QR-Rechnung.** Eine falsche Referenz heisst Geld, das nicht ankommt oder nicht
   zugeordnet wird. Das ist das eine Modul, bei dem «funktioniert bei mir» nichts wert ist.
3. **Das zweite Produkt.** Die Handwerker-Software ist strategisch richtig und
   terminlich das grösste Risiko. §3 sagt, wie man sie vorbereitet, ohne sie zu bauen.

---

## 1. Was aus dem bestehenden Code wird

### Die Naht ist schon da

`src/api.js` ist heute ein Wrapper um `window.das[name](payload)` und liefert
`{ ok, data }`. Ein Server, der auf `POST /api/<name>` dasselbe `{ ok, data }` antwortet,
macht aus dieser Datei einen `fetch`-Aufruf statt eines IPC-Aufrufs — **und kein einziger
Bildschirm merkt etwas davon.** `MULTI-USER.md` §7 formuliert die Probe dafür schon:
*«Die Bildschirme sollten sich überhaupt nicht ändern. Wenn doch, war die Naht am falschen
Ort.»*

Dass das keine Hoffnung ist, sondern eine Eigenschaft: `errorText()`, `ApiError`,
`SESSION_EXPIRED`, die Fehlercodes statt Sätze aus `lib/` — dieses ganze Vokabular ist
transportunabhängig, weil es nie etwas anderes war.

### Was bleibt, was umgeschrieben wird, was wegfällt

| | Heute | In der Cloud |
|---|---|---|
| **Fachlogik** `economics`, `balance`, `installment-plan`, `timeline`, `calendar`, `statistics`, `dashboard`, `recon`, `customers`, `vehicle-search`, `roles`, `valuation`, `body-types`, `sign-in-id` | 35 Module ohne Imports | **unverändert** — sie laufen auf dem Server statt im Hauptprozess |
| **Oberfläche** 29 Komponenten, Tailwind, i18n | React 18 + Vite | **bleibt**, plus Router und `de-CH` als erste Sprache |
| **Dokumente** `pdfkit`, `layout.js` | nur Hauptprozess | **bleibt** — läuft schon serverseitig, nur eben auf einem anderen Server |
| **Handler** 106 Kanäle, `guard()`, `handle()` | `ipcMain.handle` | HTTP-Routen, gleicher `guard()`, gleiche Rollen. Mechanisch, nicht konzeptionell |
| **Datenbank** | SQLite/SQLCipher, `REAL` für Geld | **PostgreSQL, Ganzzahl-Rappen** (§4.1) |
| **Schlüssel** `das.key`, DPAPI | pro Windows-Konto | Verschlüsselung auf Disk-Ebene + Secrets im Hoster-Tresor |
| **Sitzung** | im Hauptprozess | Server-Session + `httpOnly`-Cookie. Rolle weiter aus der DB gelesen, nie aus dem Token (§19) |
| **Fotos** | Datei neben der DB | S3-kompatibler Objektspeicher in der Schweiz |
| **Backup** `lib/backup.js` | Kopie + Prüfung + Rotation | Managed Postgres + Dump ausserhalb des Servers. **Die Argumentation aus §10 bleibt: ein Backup auf der Maschine, die es sichert, ist keins** |
| **Telefonbrücke** `vin-server.js`, Pairing | LAN, HTTP, 6-stelliger Code | **entfällt — und wird dabei besser**, siehe unten |
| **Etikettendruck** | `webContents.print()` | Browser-Druckdialog / PDF. `lib/code39.js` und `lib/vehicle-label.js` bleiben |
| **WordPress-Sync** `lib/sync/*` | REST an das eigene Theme | Struktur bleibt, Ziel wird ein Schweizer Portal (§4.7) |
| **vPIC-VIN-Decode** | NHTSA | **funktioniert für die Schweiz nicht** (§4.5) |

### 1.3 Was ausdrücklich *nicht* mitkommt — und die ehrliche Zahl

**Nachtrag vom 12. September, nach Giannis Hinweis: «TCM muss nicht zwingend wie die
Version für Kunde DAS sein.»** Das ist richtig, und es korrigiert eine Zahl weiter oben.
«35 von 55 Modulen importieren nichts» ist wahr und beschreibt **Lauffähigkeit**, nicht
**Nützlichkeit**. Ein Modul, das die vPIC-Karosserienamen auf die sieben Schlüssel eines
WordPress-Themes abbildet, läuft auf einem Schweizer Server tadellos und hilft dort
niemandem.

Also nachgemessen, in Zeilen, am 12. September:

| | Zeilen | |
|---|---|---|
| **Neutraler Kern** — `economics`, `timeline`, `calendar`, `statistics`, `dashboard`, `recon`, `balance`, `installment-plan`, `vehicle-search`, `customers` | **≈ 2'200** | zieht **wortwörtlich** um |
| **Alaska/WordPress** — die drei Dokumente, `sync/*`, `buyer-fields`, `listing-fields`, `valuation`, `body-types`, `vehicle-fields` | **≈ 2'800** | **Muster bleibt, Inhalt wird ersetzt** |
| **Existiert nur, weil es keinen Server gab** — `backup`, `recovery-code`, `data-root`, `machine-key`, `firewall`, `vin-bridge`, `vin-server`, `keystore`, `migrate-userdata`, `session` | **≈ 2'150** | **fällt ersatzlos weg** |

**Die dritte Zeile ist die eigentliche Nachricht.** Rund 2'150 Zeilen — plus die Kapitel in
CLAUDE.md, die sie erklären — existieren ausschliesslich, weil die Daten auf *einem*
Windows-Rechner liegen mussten:

- das Backup-Modul samt Rotation, Prüfung und Vor-Restore-Kopie
- der Datenbankschlüssel auf Papier, und der **vom Hersteller signierte
  Wiederherstellungscode** (§23), der gebaut wurde, weil es kein «Passwort vergessen» per
  E-Mail geben konnte
- DPAPI, `das.key`, der unlesbare Schlüssel und der Dialog «Neu anfangen oder Beenden»
- die geteilte Datenablage unter `C:\Users\Public`, `canJoinShared()`, die Umzugslogik
  zwischen `userData`-Ordnern (§6, 1.1.0 bis 1.2.1)
- die **Telefonbrücke** mit Pairing-Code, eigenem HTTP-Server und Firewall-Helfer (§18)

**In der Cloud gibt es nichts davon.** Passwort vergessen ist eine E-Mail. Backup ist eine
Einstellung beim Hoster. Zwei Leute an zwei Geräten ist der Normalfall statt eines
Kapitels. Das Handy meldet sich einfach an.

Was daraus folgt und die Planung verändert: **das Schweizer Produkt ist in genau den
Bereichen kleiner, in denen TCM kompliziert ist.** Der Kern, der die Arbeit macht, ist
gut 2'000 Zeilen — und den gibt es schon.

### 1.4 Was neu entschieden gehört statt kopiert

Weil das Produkt nicht wie die DAS-Version sein muss, sind das offene Fragen und keine
Vorgaben:

| | in TCM | für die Schweiz |
|---|---|---|
| **Rollen** | `manager` / `sales` / `admin`, und die Grenze dazwischen ist Tifs persönliche Entscheidung (§6) | Eine Garage hat Inhaber, Verkauf, Werkstatt, Buchhaltung. **Nicht übernehmen — fragen** |
| **Die Geld-Sichtbarkeit** | §6, drei Kapitel Begründung, alles um Tifs Wunsch herum gebaut | Ein Betrieb mit drei Leuten will das vielleicht gar nicht. Was bleibt, ist die *Disziplin* (eine Regel an einer Stelle), nicht die Regel |
| **Local-first (§6)** | die tragende Säule des ganzen Entwurfs | **Kehrt sich um.** Bei SaaS liegen die Daten auf dem Server, das ist der Zweck. Was übrig bleibt: Whitelist für alles, was nach draussen geht |
| **Die Bildschirme** | Dashboard, Inventory, Website, Advertising — geschnitten auf «beschädigte Autos kaufen, reparieren, auf die eigene WordPress-Seite stellen» | Ein Schweizer Occasionshändler arbeitet anders. **Erst zuschauen, dann bauen** |
| **Der Name** | Tiff Cardealer Manager | muss er nicht heissen |

### Der eine Gewinn, der nicht offensichtlich ist

CLAUDE.md §18 begründet über zwei Absätze, warum der Live-Kamera-Scanner unmöglich war:
`getUserMedia()` ist ausserhalb eines *secure context* gesperrt, und
`http://192.168.1.27:8317` ist keiner. Deshalb der Umweg über «Foto machen, Foto
hochladen», deshalb das Pairing, deshalb die Firewall-Regel, deshalb der ganze §18.

**In der Cloud läuft alles über HTTPS. Der Live-Scanner geht.** Handy aufmachen,
einloggen, Kamera auf die Stammnummer halten, fertig — kein Pairing, kein Code, keine
Firewall, kein WLAN im Büro. Genau das, was du am Flutter-Beispiel gut fandest (§18, «ky
kod lexon menjëherë dhe shpejt»), und es kostet in der Cloud nichts extra. Das ist keine
Nebensache: die Fahrzeugaufnahme ist der Arbeitsschritt, den ein Händler am häufigsten
macht.

### Und eine Frage, die sich von selbst erledigt

CLAUDE.md §5 wägt drei Wege ab, ein Abonnement durchzusetzen: Rechnung und Vertrauen,
signierte Offline-Lizenz, periodische Online-Aktivierung — mit der Empfehlung «Rechnung
und Vertrauen jetzt, signierte Lizenz beim dritten Kunden». **Bei SaaS stellt sich die
Frage nicht mehr.** Der Server *ist* die Lizenz: wer nicht zahlt, dessen Mandant wird
deaktiviert. Kein Signieren, kein Schlüssel, kein Uhr-zurückstellen.

Nettes Detail: das Abo lässt sich mit dem eigenen QR-Rechnungsmodul fakturieren. Das ist
gleichzeitig der ehrlichste Test dafür, dass es funktioniert.

---

## 2. Tech-Stack

Das Leitmotiv: **so nah wie möglich an dem, was schon da ist.** Jede Technologie, die eine
Umschreibung der Fachlogik erzwingt, ist die falsche — egal wie gut sie sonst wäre.

| Schicht | Empfehlung | Warum, und was dagegen sprach |
|---|---|---|
| **Frontend** | React 18 + Vite 5 + Tailwind 3 — **unverändert**, plus React Router | 29 Komponenten sind fertig und getestet. Next.js/SSR wäre für eine Anwendung hinter einem Login reiner Aufwand |
| **Sprache** | JavaScript, wie heute | TypeScript wäre bei Geld und Mandanten verlockend — aber es würde 56 `lib/`-Module und 600 Tests anfassen. **Die echte Absicherung gegen Mandanten-Fehler ist RLS in Postgres, nicht der Compiler** (§3). TS später, modulweise, wenn ein zweiter Entwickler dazukommt |
| **Backend** | **Node 22 + Fastify** | Gleiche Sprache wie `lib/` → die Fachlogik zieht *wortwörtlich* um. Fastify bringt JSON-Schema-Validierung mit, was genau die Eingangs-Whitelist ist, die `lib/sync/incoming-lead.mjs` heute von Hand macht. **PHP/Laravel wäre der teuerste Weg**: es würde jede Zeile Fachlogik neu schreiben und jeden Test wegwerfen |
| **Datenbank** | **PostgreSQL 16+**, `pg`-Pool, `node-pg-migrate`, **Row Level Security** | Kein ORM zu Beginn: der bestehende Code ist handgeschriebenes SQL, und das bleibt lesbar und debugbar. RLS ist der Grund, warum kein ORM nötig ist (§3) |
| **Geld** | **Ganzzahl in Rappen**, Sätze als `numeric` | Heute `purchase_price REAL` — Fliesskomma. Bei 8.1 % MWST und Rundung auf 5 Rappen ist das ein echter Fehler, kein theoretischer |
| **Auth** | Server-Session in der DB, `httpOnly`+`Secure`+`SameSite=Lax`-Cookie, `scrypt` beibehalten | Kein JWT: ein deaktivierter Benutzer muss *sofort* draussen sein, und §19 hat diese Regel schon («die Rolle wird beim Wiederherstellen aus der DB gelesen, nicht aus der Datei»). `scrypt` ist gebaut, getestet und gut genug; Argon2id ist ein späteres Upgrade, kein Startproblem |
| **Dateien** | S3-kompatibler Objektspeicher, Schweizer Region | Fotos gehören nicht in die Datenbank — §9 hat das schon entschieden und begründet |
| **PDF** | `pdfkit` — bleibt | Läuft heute schon serverseitig. `lib/documents/layout.js` zieht mit |
| **QR-Rechnung** | **`swissqrbill`** (npm) | Nicht selbst bauen (§4.4) |
| **E-Mail** | Transaktional über einen Anbieter mit EU/CH-Rechenzentrum | Und die Lehre aus §12: `wp_mail`-artiges «hat true zurückgegeben» ist kein Zustellnachweis. Zustellprotokoll von Anfang an |
| **Deployment** | Docker Compose auf einem Server, Caddy als Reverse Proxy mit automatischem TLS | Keine Microservices. Bei einem bis zehn Mandanten ist ein einzelner gut gesicherter Server die richtige Antwort, und Caddy nimmt einem Let's Encrypt vollständig ab |
| **CI** | GitHub Actions, das bestehende Muster | `npm run test:boundary` als Gate bleibt genau so, nur heisst es jetzt zusätzlich Mandantentrennung |

### Hosting — und was ich hier nicht prüfen konnte

**Diese Session hat keinen Netzzugang zu Hoster-Websites**, also ist das Folgende Wissen
und kein Blick in einen aktuellen Produktkatalog. Bitte gegenprüfen, bevor etwas bestellt
wird.

- **Hosttech** kennst du, du hast dort Plesk-Erfahrung, und für Phase 1 reicht ein VPS
  vollkommen: Docker, Postgres, Caddy, Backups nach ausserhalb. Das ist die
  Empfehlung für den Piloten — ein Server, den du selbst verstehst.
- Sobald es mehrere zahlende Mandanten sind, wird **Managed Postgres** die Sache wert:
  **Exoscale** (Zonen in Genf und Zürich, DBaaS und S3-kompatibler Objektspeicher),
  **cloudscale.ch** (Schweizer VMs und Objektspeicher), **Infomaniak** (Schweizer Public
  Cloud, Swiss Backup). Alle drei mit Datenhaltung in der Schweiz, was für den Verkauf
  an Schweizer KMU tatsächlich ein Argument ist und nicht nur ein Häkchen.
- **Datenhaltung in der Schweiz als Verkaufsargument nur dann, wenn es auch stimmt** —
  inklusive Backups, Objektspeicher und E-Mail-Versand. Ein Zustelldienst in Virginia
  macht die Aussage auf der Website falsch.

---

## 3. Mandantenfähigkeit und Wiederverwendung

### Die Entscheidung: ein Schema, `tenant_id` überall, durchgesetzt von Postgres

| Modell | Dafür | Dagegen | |
|---|---|---|---|
| **Gemeinsames Schema + `tenant_id` + RLS** | eine Migration für alle, Mandant kostet nichts, Datenbank bleibt eine | ein vergessenes `WHERE` wäre fatal — **genau das verhindert RLS** | **empfohlen** |
| Schema pro Mandant | starke Trennung, einzelner Restore einfach | ab ~50 Mandanten werden Migrationen zur Qual | nein |
| Datenbank pro Mandant | stärkste Trennung, «lösch alles von Kunde X» trivial | Betrieb, Verbindungen, Kosten | nein, nicht am Anfang |

**Der Grund für RLS ist derselbe, aus dem dieses Projekt `canSeeCompanyTotals()` gebaut
hat statt `role !== 'sales'` in sechs Bildschirme zu tippen** (CLAUDE.md §6): eine Regel
gehört an *eine* Stelle, und zwar an die, an der man sie nicht umgehen kann. Bei der
Website ist das der Hauptprozess. Bei der Cloud ist das die Datenbank.

Konkret:

```sql
ALTER TABLE vehicles ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON vehicles
  USING (tenant_id = current_setting('app.tenant_id')::uuid);
```

Die Anwendung verbindet sich als **Rolle ohne `BYPASSRLS`**, und jede Anfrage läuft durch
*eine* Funktion:

```js
withTenant(tenantId, async (tx) => { … })   // BEGIN; SET LOCAL app.tenant_id = …; …
```

**Die ehrliche Schwachstelle dabei, ausgesprochen statt versteckt:** RLS schützt nur, wenn
`app.tenant_id` wirklich gesetzt ist. Also braucht es genau einen Test, der über den
Quelltext läuft und behauptet: *es gibt keinen Datenbankzugriff ausserhalb von
`withTenant()`*. Das ist dieselbe Sorte Test wie `test/payload-privacy.test.mjs` heute,
und derselbe Grund.

**Von Tag eins einbauen, auch bei einem einzigen Mandanten.** `tenant_id` nachträglich in
40 Tabellen einzuziehen, ist keine Migration, das ist ein Umbau bei laufendem Betrieb.

### Kern und Vertikale

Die zweite Software ist der Grund, warum die Aufteilung *jetzt* stimmen muss. Die Regel
ist eine Erweiterung von §5 («kein Händlername im Code»): **im Kern kommt kein Vokabular
der Branche vor.** Kein `vehicle` in einem Kernmodul, keine `Baustelle`. Ein Kunde ist
eine `party`, ein Auftrag ein `job`.

| **Kern** (beide Produkte) | **Autohandel** | **Handwerk / Sanitär** |
|---|---|---|
| Mandanten, Benutzer, Rollen, Sitzungen, Einladungen | Fahrzeuge, VIN, Stammnummer, MFK | Objekte, Baustellen, Anlagen |
| Adressen/Kontakte (CRM), Firmen und Personen | Occasion-Zustand, Fotos, Aufbereitung | Rapporte, Zeiterfassung, Regie vs. Pauschal |
| Belegkette Offerte → Auftrag → Lieferschein → **Rechnung** → Mahnung → Gutschrift | Ankauf, Margen-/Vorsteuerlogik (§4.3) | Material, Lager, Lieferantenpreise |
| Nummernkreise pro Mandant und Jahr | Portalexport (AutoScout24 …) | Serviceverträge, Wartungsintervalle |
| MWST-Maschine, CHF, Rundung, **QR-Rechnung** | Fahrzeugbewertung | Notfalldienst-Planung |
| Zahlungen, **camt.054-Abgleich**, Mahnwesen | Kaufvertrag, Garantie (OR 210) | Akontorechnungen, Ausmass, SIA 118 |
| Dateien, Fotos, Belegarchiv 10 Jahre | | Bauhandwerkerpfandrecht (ZGB 837 — **4 Monate**, gehört als Frist ins System) |
| Auswertungen, Dashboard, Kalender, Audit-Log | | |
| Branding pro Mandant, i18n de/fr/it | | |

Anteilsschätzung: **etwa 60 % Kern, 40 % Vertikale.** Der Kern ist der Teil, der Geld und
Zeit kostet, und er wird genau einmal gebaut.

### Struktur

```
tiff-suite/                     ein Repo, npm workspaces
  packages/
    core-db/        Migrationen, withTenant(), RLS-Policies
    core-auth/      Sitzungen, Rollen, Einladungen, Audit
    core-parties/   Kontakte, Firmen, Personen
    core-billing/   MWST, CHF, Belege, Nummernkreise, QR-Rechnung, camt
    core-ui/        React-Basis: Tabellen, Drawer, Formulare, Icons, i18n
    core-docs/      pdfkit-Layout (aus lib/documents/layout.js)
  apps/
    cardealer-ch/   Fahrzeuge, Ankauf, Portale
    handwerk-ch/    später
```

**Zwei Datenbanken, ein Codebestand.** Die Mandanten der beiden Produkte sind verschiedene
Firmen, die Schemata entwickeln sich auseinander, und die Produkte sollen getrennt
verkauft und betrieben werden können. Eine gemeinsame Datenbank mit einer Spalte
`product` spart heute eine Stunde und kostet später einen Monat.

**Und die Alaska-App bleibt, wo sie ist.** Sie läuft, sie ist bezahlt, ihr Kunde ist
zufrieden. Sie in die Schweizer Cloud zu ziehen wäre Arbeit ohne Gegenwert und würde §6
(local-first) für jemanden brechen, der nie danach gefragt hat.

---

## 4. Was die Schweiz anders braucht

### 4.1 Geld

**Beträge als Ganzzahl in Rappen.** `REAL` in der heutigen Tabelle ist für einen
Occasionspreis in Dollar gerade noch tragbar; für eine MWST-pflichtige Rechnung mit
Positionen, Rabatten und Rundung ist es das nicht. `12.10` ist im Binärsystem nicht
darstellbar, und der Fehler zeigt sich als eine Rappen-Differenz in der Quartalsabrechnung,
die niemand mehr findet.

- Beträge: `bigint`, Einheit Rappen
- Sätze und Prozente: `numeric(6,3)`
- **5-Rappen-Rundung nur bei Barzahlung.** Eine Rechnung darf auf den Rappen genau lauten;
  gerundet wird der Barbetrag beim Kassieren. Wer die Rechnung rundet, hat die Buchhaltung
  gegen sich.
- Formatierung `Intl.NumberFormat('de-CH')` → `12'500.00`, Datum `TT.MM.JJJJ`

### 4.2 MWST

**Sätze seit 1. Januar 2024: Normalsatz 8.1 %, reduziert 2.6 %, Beherbergung 3.8 %.**
Fahrzeughandel ist Normalsatz.

**Und genau deshalb dürfen die Sätze keine Konstante sein.** Bis Ende 2023 waren es
7.7 / 2.5 / 3.7 — der Beweis, dass sie sich ändern, ist zwei Jahre alt. Also eine Tabelle
`tax_rates` mit `valid_from` / `valid_to`, und jede Belegposition referenziert einen Satz,
statt eine Zahl zu speichern. Eine Rechnung von 2023 muss 2027 noch 7.7 % zeigen, wenn man
sie erneut druckt.

**Zwei Abrechnungsmethoden, und die Unterscheidung gehört auf Mandantenebene:**

| | |
|---|---|
| **Effektiv** | Umsatzsteuer minus Vorsteuer. Abrechnung vierteljährlich |
| **Saldosteuersatz** | Fakturierung wie üblich, an die ESTV geht ein Branchen-Pauschalsatz. Zulässig bis rund CHF 5 Mio. Umsatz und CHF 108'000 Steuerschuld pro Jahr. Abrechnung halbjährlich |

Für die *Rechnung* ändert sich nichts, für die *Auswertung* alles. Ein Garagist mit
Saldosatz, dem die Software effektiv rechnet, bekommt eine falsche Abrechnung.

Weiter: die **UID** gehört auf jede Rechnung, Format `CHE-123.456.789 MWST` — mit
Prüfziffernvalidierung beim Erfassen, denn eine falsche UID auf hundert Rechnungen ist
hundert Korrekturen. MWST-Pflicht ab CHF 100'000 Jahresumsatz.

### 4.3 Occasionen — hier weicht die Realität von der Aufgabenstellung ab

**Du hast «Margenbesteuerung bei Occasionen» geschrieben. Das ist der deutsche und der
alte Schweizer Stand.** In der Schweiz wurde die Margenbesteuerung für gebrauchte
bewegliche Gegenstände **per 1. Januar 2018 abgeschafft** und durch den **fiktiven
Vorsteuerabzug** ersetzt (MWSTG Art. 28a). Margenbesteuerung (Art. 24a) gibt es nur noch
für **Sammlerstücke** — im Autobereich also Oldtimer und Liebhaberfahrzeuge.

So funktioniert es heute:

> Kauf von einer Privatperson: **CHF 10'000**, keine MWST auf dem Beleg.
> Fiktive Vorsteuer: 10'000 × 8.1 / 108.1 = **CHF 749.31**
> Verkauf: **CHF 14'000** inkl. MWST → Umsatzsteuer 14'000 × 8.1 / 108.1 = **CHF 1'049.03**
> An die ESTV: **CHF 299.72** — also 8.1/108.1 der Marge von CHF 4'000.

Wirtschaftlich dasselbe Ergebnis wie eine Margenbesteuerung, **buchhalterisch und auf der
Rechnung aber etwas völlig anderes:**

| | fiktiver Vorsteuerabzug (Normalfall) | Margenbesteuerung (Sammlerstück) |
|---|---|---|
| Rechnung an den Kunden | **normale Rechnung mit ausgewiesener MWST** | **MWST darf nicht ausgewiesen werden** |
| Abzug | im Zeitpunkt des *Einkaufs* | keiner |
| Ausschluss | bei Export, bei ausgewiesener Vorsteuer, bei Sammlerstücken | |

Das Datenmodell braucht deshalb **pro Fahrzeug**, nicht pro Mandant:

```
vat_scheme            'standard' | 'notional_input_tax' | 'margin'
purchase_from         'private' | 'dealer' | 'auction' | 'trade_in'
purchase_vat_amount   Rappen, 0 bei Privatkauf
notional_input_tax    Rappen, berechnet und gespeichert (Beweislage!)
```

Zwei Dinge, die daraus folgen und leicht vergessen gehen: der **Ankaufsvertrag mit der
Privatperson ist der Beleg** für den fiktiven Vorsteuerabzug und muss zehn Jahre archiviert
sein — die Software soll ihn also selbst erzeugen und ablegen. Und wenn ein so
eingekauftes Fahrzeug am Ende **exportiert** wird, muss der Abzug korrigiert werden; das
ist ein Fall, den das System kennen sollte, statt ihn stillschweigend falsch zu lassen.

> **Muss ein Treuhänder bestätigen, bevor die erste Rechnung rausgeht.** Ich lese
> MWSTG Art. 28a und Art. 24a sowie die MWST-Info der ESTV zum Vorsteuerabzug so — aber
> ich habe in dieser Session keine Quelle öffnen können, und das ist genau die Sorte
> Aussage, bei der dieses Projekt sonst «ein Anwalt, nicht Claude» sagt (§17).

### 4.4 QR-Rechnung

Seit **30. September 2022** der einzige gültige Einzahlungsschein; roter und oranger
Einzahlungsschein sind abgeschafft.

**Nicht selbst bauen. `swissqrbill` verwenden.** Der Grund ist nicht Faulheit, sondern
dass die Spezifikation von SIX viele Details hat, die man einzeln richtig machen muss und
bei denen man den Fehler erst merkt, wenn eine Bank ablehnt:

- Swiss QR Code **46 × 46 mm**, Fehlerkorrektur **M**, Schweizerkreuz 7 × 7 mm in der Mitte
- Nutzlast: Header `SPC`, Version `0200`, UTF-8, feste Zeilenstruktur, Abschluss `EPD`
- Zahlteil **148 × 105 mm** plus Empfangsschein **62 × 105 mm** = **210 × 105 mm** am
  unteren Rand der A4-Rechnung, mit Perforationslinien und Schere
- Schriftgrössen und Fettungen sind vorgeschrieben, nicht Geschmackssache

**Die Referenz ist die Entscheidung, die getroffen werden muss, und zwar mit der Bank:**

| Konto | Referenztyp | Prüfziffer |
|---|---|---|
| **QR-IBAN** (IID 30000–31999) | **QRR** — 27 Stellen | Modulo 10 rekursiv, wie die alte ESR-Referenz |
| normale IBAN | **SCOR** — `RF` + ISO 11649 | Modulo 97 |
| normale IBAN | **NON** — keine Referenz | — |

**Empfehlung: QR-IBAN + QRR.** Nur damit kommt die Referenz über den Zahlungseingang
zurück, und nur dann lässt sich eine Zahlung automatisch der Rechnung zuordnen. Der Pilot
sollte die QR-IBAN bei seiner Bank bestellen, bevor Phase 1 fertig ist — das dauert Tage,
nicht Minuten.

**Und der Teil, der aus einer Rechnungssoftware ein Werkzeug macht: `camt.054`.** Die Bank
liefert die Zahlungseingänge als ISO-20022-XML mit der QR-Referenz. Einlesen → Rechnung
gefunden → bezahlt → Mahnlauf weiss Bescheid. Für einen Betrieb mit einer Buchhalterin an
zwei Halbtagen ist das der Punkt, an dem sich das Abo bezahlt macht. Der Import gehört in
Phase 2, aber die Referenz muss von Anfang an richtig sein, sonst gibt es später nichts
abzugleichen.

Optional und stark: **Swico-Syntax** in der unstrukturierten Mitteilung
(`//S1/10/<Rechnungsnr>/11/<JJMMTT>/30/<UID>/32/<MWST-Satz>/40/<Konditionen>`) — damit
kann die Buchhaltungssoftware des Empfängers die Rechnung automatisch verbuchen. Kostet
wenig und wirkt professionell.

**eBill** (Rechnungen direkt ins E-Banking) später — dafür braucht es einen Netzwerkpartner
und einen Vertrag, das ist kein Codeproblem.

### 4.5 Fahrzeugdaten

Die heutige Tabelle ist amerikanisch: `miles`, `title_status`, `odometer_status`, `plate`
für Alaska, `warranty_kind` für den FTC Buyers Guide. Nichts davon ist falsch — es gehört
nur in die andere Vertikale.

| Feld | Schweiz | Hinweis |
|---|---|---|
| **Stammnummer** | `123.456.789` | **Als Text speichern, nicht als Zahl** — führende Nullen und Punkte gehören dazu. Steht im Fahrzeugausweis |
| **Typengenehmigung** | z. B. `1AB123` | Typenschein; hängt an den technischen Daten |
| **VIN** | 17 Zeichen | bleibt wie heute, inkl. Prüfziffer |
| **Erstinverkehrsetzung** | Datum | **ersetzt `year` als Leitdatum.** Modelljahr ist in der Schweiz zweitrangig |
| **MFK** | letzte Prüfung + gültig bis | plus `sold_with_mfk` — «ab MFK» ist eine **Vertragszusage**, keine Beschreibung |
| **Kilometer** | `km`, nicht `miles` | |
| **Leistung** | **kW** primär, PS abgeleitet | |
| **Hubraum** | ccm | |
| Leergewicht, Gesamtgewicht, Plätze, Kontrollschild, Treibstoff, Getriebe, Antrieb | | |
| **Energieetikette** | A–G | für Neuwagen Pflicht in der Werbung; bei Occasionen nicht — trotzdem als Feld sinnvoll |

**Die MFK-Intervalle gehören in eine Tabelle, nicht in den Code.** Für Personenwagen gilt
nach meinem Stand seit dem 1. Februar 2024 der Rhythmus **5 Jahre / 3 Jahre / dann alle
2 Jahre** (vorher 4/3/2). Das ist genau die Art Zahl, die sich ändert und die ein Mensch
beim Strassenverkehrsamt gegenprüfen sollte — und die Konsequenz für den Bau ist dieselbe
wie bei den MWST-Sätzen: konfigurierbar pro Fahrzeugkategorie, mit Gültigkeitsdatum.

**Der VIN-Decode über NHTSA vPIC funktioniert für die Schweiz nicht, und das ist bereits
bewiesen.** CLAUDE.md §23 hält den Fall fest: Gianni gab `JMZDM6WMX00519346` ein, und es
kam nichts zurück — `JMZ` ist Mazdas Export-WMI, vPIC ist die *US-Bundesdatenbank*, und
ein nicht für den US-Markt gebautes Fahrzeug steht schlicht nicht drin. In der Schweiz
sind praktisch alle Fahrzeuge so. Die Optionen:

1. **Eurotax / Autovista** — der Schweizer Standard, kostenpflichtig, liefert
   Identifikation *und* Bewertung in einem. Damit füllt sich auch der `market_source`-Platz,
   an dem heute iSeeCars steht
2. **Typenschein-Nummer** als Schlüssel statt VIN, gegen die ASTRA-Typengenehmigungsdaten
3. **Manuelle Erfassung** mit guten Vorschlägen — für den Piloten völlig ausreichend

Realistisch: Phase 1 manuell, Eurotax verhandeln, sobald es mehr als ein Kunde ist.

### 4.6 Recht und Verträge

- **Gewährleistung, OR Art. 197 ff.** Regelfrist zwei Jahre. Bei gebrauchten Sachen kann
  sie auf ein Jahr verkürzt werden; **im Verkauf an Konsumenten ist ein Jahr die
  Untergrenze und ein vollständiger Ausschluss unwirksam** (OR 210 Abs. 4). Zwischen
  Händlern ist «ab Platz, unter Ausschluss jeglicher Gewährleistung» zulässig.
  → Das ist das Schweizer Gegenstück zu `warranty_kind` und dem FTC Buyers Guide (§17):
  ein Feld, das **nicht raten darf**, weil es entscheidet, ob der Käufer Ansprüche hat.
  Dieselbe Regel, anderes Rechtssystem.
- **Kaufvertrag** nach AGVS/UPSA-Muster, von einem Schweizer Anwalt geprüft.
- **Aufbewahrung 10 Jahre**, OR 958f und GeBüV. Rechnungen als PDF unveränderbar
  archivieren, mit Hash und Audit-Eintrag. Das Audit-Log gibt es schon.
- **revDSG** (in Kraft seit 1. September 2023): Bearbeitungsverzeichnis,
  Datenschutzerklärung, Auftragsbearbeitungsvertrag mit jedem Unterauftragnehmer (Hoster,
  Objektspeicher, Mailversand), Meldepflicht an den EDÖB bei Verletzungen.
  **Als SaaS-Anbieter bist du Auftragsbearbeiter für deine Kunden** — der AVV ist Teil des
  Verkaufs, nicht der Technik.

### 4.7 Portale

Der WordPress-Sync (`lib/sync/*`) hat in der Schweiz ein direktes Gegenstück:
**AutoScout24.ch**, dazu Comparis, tutti.ch, Ricardo, anibis. Ein Fahrzeug einmal erfassen
und überall veröffentlichen ist im Occasionshandel *das* Verkaufsargument.

Die Struktur passt: Whitelist der öffentlichen Felder, ein Client pro Ziel,
Veröffentlichungsstatus am Fahrzeug — das steht alles schon in `lib/sync/payload.mjs`, nur
mit anderem Ziel. **Der Zugang ist aber kommerziell, nicht technisch:** die Portale
arbeiten mit zugelassenen Schnittstellenpartnern und Verträgen. Das ist eine Recherche für
einen Menschen, und sie sollte früh beginnen, weil sie lange dauert.

---

## 5. Fahrplan

Angesetzt auf **einen Entwickler mit Claude, abends und am Wochenende**. Die
Wochenangaben sind Arbeitsaufwand, nicht Kalenderzeit — mit einem Vollzeitjob daneben
rechne kalendarisch etwa mit dem Doppelten bis Dreifachen.

### Phase 0 — Fundament (1–2 Wochen)

| | Fertig, wenn |
|---|---|
| Hosting bestellt, Domain, TLS, Postgres, Objektspeicher | ein Deploy vom Laptop aus durchläuft |
| Monorepo, Workspaces, Migrationen | `npm run migrate` baut die DB von null auf |
| Mandanten, Benutzer, Rollen, Sitzungen, **RLS + `withTenant()`** | ein Test beweist, dass Mandant A Mandant B nicht sieht |
| CI mit dem bestehenden Gate-Muster | Boundary- und Mandantentests laufen vor jedem Deploy |
| **Backup und ein echter Restore** | eine Datenbank wurde aus dem Backup wiederhergestellt, nicht nur gesichert |

Der letzte Punkt gehört bewusst in Phase 0 und nicht ans Ende. Ein Backup, das nie
zurückgespielt wurde, ist eine Vermutung.

### Phase 1 — Der Pilot kann damit arbeiten (4–6 Wochen)

Leitsatz: **Fahrzeug rein, Rechnung raus.** Nichts, was nicht auf dieser Linie liegt.

- Login, Mandant, Benutzer und Rollen (Inhaber / Verkauf / Werkstatt)
- Fahrzeuge mit den CH-Feldern aus §4.5, Fotos, Status, Aufbereitungskosten
- Kunden und Lieferanten (`parties`)
- **Ankaufsvertrag** (Privatkauf, Beleg für den fiktiven Vorsteuerabzug)
- **Kaufvertrag** nach AGVS-Muster
- **Rechnung mit QR-Zahlteil**, MWST-Maschine, `vat_scheme` pro Fahrzeug
- Deutsche Oberfläche, `de-CH`-Formate
- Übernahme der bestehenden Fachlogik: `economics`, `balance`, `timeline`, `dashboard`

**Ende von Phase 1 ist erreicht, wenn der Pilot ein Fahrzeug eingekauft, aufbereitet,
verkauft und eine bezahlte QR-Rechnung im System hat — mit echtem Geld, nicht mit
Testdaten.**

### Phase 2 — Der Geldkreislauf schliesst sich (3–4 Wochen)

- Zahlungen, **camt.054-Import**, automatischer Abgleich über die QR-Referenz
- Mahnwesen (1./2./3. Mahnung, Verzugszins 5 % nach OR 104)
- **MWST-Auswertung** für effektiv *und* Saldo
- Auswertungen und Kalender (Portierung von `statistics.js`, `calendar.js`)
- Belegarchiv mit Hash, 10 Jahre

### Phase 3 — Aus einem Kunden werden mehrere (4–6 Wochen)

- **Portalexport** AutoScout24 und Co.
- **Mobile PWA mit Live-Kamera** für VIN und Stammnummer (§1 — geht jetzt endlich)
- Self-Service-Onboarding, Abo-Verwaltung, Rechnungsstellung an die eigenen Kunden
- Französisch als zweite Sprache, sobald ein Kunde in der Romandie auftaucht

### Phase 4 — Handwerk

Erst starten, **wenn der Pilot drei Monate stabil produktiv läuft.** Bis dahin ist die
Vorbereitung genau eine Sache: den Kern sauber halten und kein Autohandels-Vokabular
hineinlassen. Wenn Phase 1 bis 3 richtig gebaut sind, ist die Handwerker-Software
überwiegend eine neue Vertikale auf demselben Fundament — Objekte statt Fahrzeuge,
Rapporte statt Aufbereitung, und derselbe Rechnungs- und Zahlungsapparat.

### Was ein Mensch tun muss, parallel und ab sofort

| | Warum es früh anfangen muss |
|---|---|
| **Treuhänder**: `vat_scheme`, Saldo oder effektiv, fiktiver Vorsteuerabzug bestätigen | blockiert die Rechnungslogik, und Rückbau ist teuer |
| **Bank**: QR-IBAN bestellen, camt.054-Bezug klären | dauert Tage bis Wochen |
| **Anwalt**: Kaufvertrag, Gewährleistung, AGB, AVV, Datenschutzerklärung | dasselbe Muster wie §17 in Alaska |
| **Pilotkunde**: was liegt heute wo? Excel, Papier, bestehende Software? | entscheidet, ob es einen Import braucht |
| **AutoScout24**: Schnittstellenbedingungen erfragen | kommerziell, dauert |
| **Hoster**: Angebote vergleichen, Datenhaltung CH schriftlich | steht später auf deiner Website |

---

## 6. Was ich nicht prüfen konnte

Damit niemand später eine Vermutung für eine Messung hält:

- **Alles Rechtliche und Steuerliche** in §4 stammt aus Wissen, nicht aus einer Quelle,
  die ich in dieser Session öffnen konnte. Besonders zu prüfen: der Wegfall der
  Margenbesteuerung per 2018 und die Mechanik von MWSTG Art. 28a (§4.3), die
  Saldosteuersatz-Grenzen (§4.2), die MFK-Intervalle 5/3/2 (§4.5).
- **Hoster-Produktkataloge** ändern sich. Exoscale, cloudscale und Infomaniak nenne ich
  aus Kenntnis, nicht aus einem heutigen Preisblatt.
- **Die QR-Spezifikation** habe ich aus Kenntnis der SIX Implementation Guidelines
  wiedergegeben. Die Bibliothek `swissqrbill` nimmt einem die Details ab — genau deshalb
  ist sie die Empfehlung.
- **Die AutoScout24-Schnittstelle** kenne ich nicht im Detail; dass sie über
  Vertragspartner läuft, ist Branchenkenntnis und muss bestätigt werden.

Alles Gemessene — Modulzahlen, Handlerzahlen, Spaltentypen, die Naht in `src/api.js` —
stammt aus dem Code dieses Repos und ist am 12. September 2026 nachgeprüft.

---

## 7. Die drei Entscheidungen vor der ersten Zeile Code

1. **Hosting und Region.** Ein Server bei Hosttech für den Piloten, oder gleich Managed
   Postgres bei einem Schweizer Anbieter? Beides vertretbar; die Entscheidung bestimmt die
   Deploy-Kette und lässt sich später nur mit Aufwand drehen.
2. **Mandantenmodell.** Meine Empfehlung ist eindeutig: gemeinsames Schema, `tenant_id`,
   RLS. Wenn das festgelegt ist, ist es festgelegt — nachträglich ändern heisst Umbau.
3. **Die Steuerlogik, bestätigt vom Treuhänder.** Nicht «wir bauen mal und fragen dann».
   Der `vat_scheme`-Enum aus §4.3 ist eine Ja/Nein-Frage an einen Fachmann und danach eine
   Stunde Arbeit — in der falschen Reihenfolge ist es eine Woche.

## 8. Was bewusst nicht gemacht wird

- **Die Alaska-App wird nicht migriert.** Sie läuft. §6 bleibt für sie gültig.
- **Keine Microservices.** Ein Deployable pro Produkt.
- **Keine Fremdwährung in Phase 1.** CHF. EUR erst, wenn ein Kunde importiert.
- **Kein eBill in Phase 1.** Braucht einen Netzwerkpartner, nicht Code.
- **Die Handwerker-Software wird nicht vorgebaut.** Sie wird nur nicht verbaut — indem der
  Kern kein Vokabular des Autohandels enthält. Das ist der ganze Unterschied, und er
  kostet nichts.
