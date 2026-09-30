# BIT Automobile — WordPress-Theme

Webseite für **BIT Automobile** (ImmoBit AG, Herzwilstrasse 262, 3173 Oberwangen b. Bern).
Gestaltung 1:1 aus dem Entwurf `design/BIT_Automobile_Webseite_offline.html`:
dunkel, Archivo / Archivo Narrow (lokal, kein Google Fonts), ein Radius für alles (16px),
blaue Knöpfe, die Aare-Linie unter jedem Seitenkopf.

## Seiten

| Adresse | Vorlage | Inhalt |
|---|---|---|
| `/` | `front-page.php` | Hero, 3 neueste Fahrzeuge, Ankauf, Showroom mit Adresse/Telefon/Zeiten, «Der Platz» (echte Anzahl, oberes Foto wischt beim Scrollen weg), Dienstleistungen — jedes Foto nur einmal |
| `/fahrzeuge/` | `archive-fahrzeug.php` | Alle Fahrzeuge, Sofort-Suche, Marken-Chips, Regler Preis/Kilometer/Jahrgang |
| `/fahrzeuge/<name>/` | `single-fahrzeug.php` | Galerie, Preis, Anruf-Kasten, Daten, Beschreibung, 3 ähnliche (kein Leasing — BIT bietet keins an) |
| `/marke/<marke>/` | `taxonomy-marke.php` | Wie `/fahrzeuge/`, nur eine Marke |
| `/dienstleistungen/` | `page-dienstleistungen.php` | Vier Karten, jede führt auf ihre Seite; Band «Auto verkaufen, ohne Inserat» |
| `/dienstleistungen/<name>/` | `page-dienstleistung.php` | An- und Verkauf, Fahrzeugaufbereitung, Carrosserie und Werkstatt, Fahrzeugbewertung — Texte von der bisherigen bit-automobile.ch, im Admin bearbeitbar |
| `/kontakt/` | `page-kontakt.php` | Visitenkarte, Formular (speichert + E-Mail), Fakten |
| `/ueber-uns/` | `page-ueber-uns.php` | Schild seit 2022, vier Auswahl-Kriterien |
| `/impressum/` | `page-impressum.php` | Text im Admin bearbeitbar + Registerdaten |
| `/datenschutz/` | `page-datenschutz.php` | Text im Admin bearbeitbar (revDSG), «Daten bleiben in der Schweiz» |
| `/wp-login.php` | `inc/login.php` | Login im BIT-Design |

Beim Aktivieren legt das Theme alle Seiten, Menüs, Startseite, Datenschutz-Seite,
Permalinks und Zeitzone selbst an (`inc/seed.php`). Bestehende Seiten werden nie überschrieben.

## Fotos klein halten (`inc/images.php`)

Gilt für jedes hochgeladene Bild (Admin, Mediathek, AutoScout24-Import):
Fotos über 2000 px werden auf 2000 px (lange Seite) verkleinert, das Handy-Original danach
gelöscht, Bilder als WebP gespeichert (wenn der Server es kann, sonst JPEG), die Formate
1536/2048 px nicht mehr erzeugt, Kamera-Daten (GPS) entfernt. Gemessen mit einem
Handyfoto 4032×3024 (6,2 MB): vorher 10 Dateien / 8,3 MB auf dem Server, nachher
7 Dateien / 0,8 MB (WebP) bzw. 1,0 MB (JPEG). Die Übersicht im Admin zeigt, wie viel
Platz die Bilder belegen.

## Bewegung

Klein und überall gleich (`assets/css/site.css`, Abschnitt «Bewegung»; `assets/js/site.js`):
Seitenwechsel blendet weich über (View Transitions, Kopf bleibt stehen) · beim Klick läuft
eine Aare-Linie oben über die Seite · die Aare-Linie unter dem Titelbild geht von Rand zu Rand, ein heller Schimmer fliesst darüber · Blöcke steigen beim Scrollen 14px auf, gestaffelt ·
vor jedem Kicker zeichnet sich eine kurze Linie · Unterstrich wächst bei Links ·
«Der Platz»: Zahl und Linie zählen mit, das obere Foto wischt schräg weg (wie im Entwurf) ·
Maus: das «bit» aus dem Logo (22px, BIT-Blau, kleiner als der Zeiger) zieht hinter dem Zeiger
her, dazu der weisse Pfeil aus dem Entwurf. Mit «Bewegung reduzieren» ist alles aus.

## Symmetrie

Knöpfe 48px (klein 44, Kopf 40), Felder 48px, Radius 16px. Knöpfe einer Gruppe sind gleich
breit, auf dem Handy volle Breite untereinander. Karten-Raster zentrieren die letzte Reihe.
Der Browser-Test misst das auf jeder Seite nach.

## Für Sabit (Admin)

- **Übersicht:** Kacheln «Fahrzeug erfassen», «Fahrzeuge», «Anfragen», «Seiten».
- **Fahrzeug erfassen:** Titel, Hauptbild, weitere Bilder, alle Daten in einem Kasten
  (Preis, Jahrgang, 1. Inverkehrsetzung, km, PS, Getriebe, Antrieb, Treibstoff, Verbrauch,
  Türen/Plätze, Farbe, Stammnummer optional, Ab MFK, Garantie, Markierung «Neu», Status).
  Status «Verkauft» nimmt es von der Liste, die Seite bleibt erreichbar.
- **Anfragen:** jedes Formular landet hier und als E-Mail an info@bit-automobile.ch;
  nach 12 Monaten automatisch gelöscht (steht so im Datenschutz).
- Rolle für Sabit: **Redakteur** (kann Fahrzeuge, Seiten, Anfragen — nicht Theme/Plugins).
- Firmendaten (Telefon, Zeiten, MWST-Nr.): *Design → Customizer → BIT Automobile – Firmendaten*.
  Die Rechtstexte holen die Werte per Kurzcode (`[bit key="uid"]`, `[bit_tel]`, `[bit_mail]`).

## AutoScout24

`inc/autoscout24.php` holt Sabits Inserate über die AutoScout24-API (DMS API) und legt sie
als Fahrzeuge an; Anker ist die AutoScout24-ID, was dort verschwindet, wird «verkauft».
Einstellungen unter *Werkzeuge → AutoScout24* (Seller-ID, Client-ID, Secret — nur in der
Datenbank, nie im Code). Standard ist die **Preproduktion**.

**Stand:** vorbereitet und mit **erfundenen Daten** getestet (`demo/as24-demo.json`,
11 Beispielfahrzeuge mit den echten Showroom-Fotos). Vor dem ersten echten Abgleich:
Pfad der Inseratsliste und Feldnamen gegen eine echte Antwort prüfen.

**Probelauf** (*Werkzeuge → AutoScout24 → «Probelauf (nichts speichern)»*): liest Sabits
Inserate, speichert nichts, zeigt pro Inserat «würde anlegen: Marke Modell – CHF – km – Jahr –
Bilder» und meldet fehlende Felder (Hinweis auf falsche Feldnamen). Die unveränderte Antwort
von AutoScout24 lässt sich als JSON herunterladen (liegt in `uploads/bit-as24/`, von aussen
gesperrt, ohne Token, die letzten 5 bleiben). Erst danach «Jetzt abgleichen».
Auf der Kommandozeile: `wp bit as24 sync --dry-run`.

**Kosten:** Der Code liest nur (Anmeldung + Inseratsliste). Er kann bei AutoScout24 nichts
inserieren, aktivieren oder buchen.

Demo-Fahrzeuge sind auf der Seite mit «Demo · Testdaten» beschriftet und werden vor dem
Livegang gelöscht: *Werkzeuge → BIT Demo-Daten* oder `wp bit demo remove`.
Ein echter Abgleich fasst Demo-Fahrzeuge nie an und umgekehrt.

## Befehle (WP-CLI)

```
wp bit setup                               # Seiten, Menüs, Startseite
wp bit demo load | remove                  # Demo-Fahrzeuge
wp bit as24 import --file=x.json [--dry-run] [--demo]
wp bit as24 sync [--dry-run]               # echter Abgleich / Probelauf (braucht Zugangsdaten)
```

## Tests

```
wp eval-file wp-content/themes/bit-automobile/tests/run-tests.php     # 45 Tests: CHF, Telefon, MWST, AS24-Zuordnung, Import, Probelauf, Foto-Verkleinerung
BASE=http://localhost:8080 BIT_USER=… BIT_PASS=… node tests/e2e.js     # 195 Prüfungen im Browser + Bildschirmfotos
ADMIN_USER=… ADMIN_PASS=… AS24_MOCK=1 node tests/e2e.js                # + 7 Prüfungen Probelauf im Admin (mit Attrappe)
```

Vor jedem erneuten Lauf lokal zurücksetzen: `wp transient delete --all` (Spam-Sperre 5/Stunde)
und `wp option delete bit_as24_settings` (sonst ist der Probelauf-Knopf schon freigeschaltet).

Der Browser-Test prüft jede Seite (HTTP-Status, keine PHP-Meldung, aktiver Menüpunkt,
korrigiertes Logo, Radius 16px überall, gleiche Masse, kein Foto doppelt, alle Bilder laden,
kein Entwurf-Hinweis, kein seitliches Scrollen auf dem Handy), dazu Suche/Filter, Galerie,
Dienstleistungs-Karten → eigene Seite, Animationen und Maus-«bit»,
Kontaktformular bis in die Anfragen-Liste, und den Login.

**AutoScout24-Attrappe** (`tests/as24-mock.php`, `tests/as24-mock-mu-plugin.php`): antwortet
lokal wie der echte Server, mit den erfundenen Daten; es geht nichts ins Internet. Nur für
Tests – **nie** als mu-plugin auf den echten Server kopieren.

## Auf hosttech installieren

1. WordPress installieren (Sprache Deutsch (Schweiz)).
2. Ordner `bit-automobile` **ohne** `tests/` als ZIP unter *Design → Themes → Theme hochladen*, aktivieren.
3. Benutzer für Sabit anlegen, Rolle **Redakteur**.
4. E-Mail-Versand prüfen (Formular) — ggf. SMTP über das hosttech-Postfach.
5. Demo-Fahrzeuge löschen, echte Fahrzeuge erfassen oder AutoScout24 verbinden.
6. MWST-Nr. `CHE-345.577.846 MWST` ist voreingestellt (UID-Register: MWST aktiv seit 01.01.2023).
