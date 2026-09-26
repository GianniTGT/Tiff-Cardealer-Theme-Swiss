# BIT Automobile — WordPress-Theme

Webseite für **BIT Automobile** (ImmoBit AG, Herzwilstrasse 262, 3173 Oberwangen b. Bern).
Gestaltung 1:1 aus dem Entwurf `design/BIT_Automobile_Webseite_offline.html`:
dunkel, Archivo / Archivo Narrow (lokal, kein Google Fonts), ein Radius für alles (16px),
blaue Knöpfe, die Aare-Linie unter jedem Seitenkopf.

## Seiten

| Adresse | Vorlage | Inhalt |
|---|---|---|
| `/` | `front-page.php` | Hero, Fakten, 3 neueste Fahrzeuge, Ankauf, Showroom, «Der Platz» (echte Anzahl), Dienstleistungen |
| `/fahrzeuge/` | `archive-fahrzeug.php` | Alle Fahrzeuge, Sofort-Suche, Marken-Chips, Regler Preis/Kilometer/Jahrgang |
| `/fahrzeuge/<name>/` | `single-fahrzeug.php` | Galerie, Preis, Anruf-Kasten, Daten, Leasing-Rechner, 3 ähnliche |
| `/marke/<marke>/` | `taxonomy-marke.php` | Wie `/fahrzeuge/`, nur eine Marke |
| `/dienstleistungen/` | `page-dienstleistungen.php` | Fünf Dienstleistungen, Band «Auto verkaufen, ohne Inserat» |
| `/kontakt/` | `page-kontakt.php` | Visitenkarte, Formular (speichert + E-Mail), Fakten |
| `/ueber-uns/` | `page-ueber-uns.php` | Schild seit 2022, vier Auswahl-Kriterien |
| `/impressum/` | `page-impressum.php` | Text im Admin bearbeitbar + Registerdaten |
| `/datenschutz/` | `page-datenschutz.php` | Text im Admin bearbeitbar (revDSG), «Daten bleiben in der Schweiz» |
| `/wp-login.php` | `inc/login.php` | Login im BIT-Design |

Beim Aktivieren legt das Theme alle Seiten, Menüs, Startseite, Datenschutz-Seite,
Permalinks und Zeitzone selbst an (`inc/seed.php`). Bestehende Seiten werden nie überschrieben.

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

**Stand:** vorbereitet und mit **erfundenen Daten** getestet (`tests/fixtures/as24-demo.json`,
11 Beispielfahrzeuge mit den echten Showroom-Fotos). Vor dem ersten echten Abgleich:
Pfad der Inseratsliste und Feldnamen gegen eine echte Antwort prüfen.

Demo-Fahrzeuge sind auf der Seite mit «Demo · Testdaten» beschriftet und werden vor dem
Livegang gelöscht: *Werkzeuge → BIT Demo-Daten* oder `wp bit demo remove`.
Ein echter Abgleich fasst Demo-Fahrzeuge nie an und umgekehrt.

## Befehle (WP-CLI)

```
wp bit setup                               # Seiten, Menüs, Startseite
wp bit demo load | remove                  # Demo-Fahrzeuge
wp bit as24 import --file=x.json [--dry-run] [--demo]
wp bit as24 sync                           # echter Abgleich (braucht Zugangsdaten)
```

## Tests

```
wp eval-file wp-content/themes/bit-automobile/tests/run-tests.php     # 30 Tests: CHF, Telefon, Leasing, AS24-Zuordnung, Import
BASE=http://localhost:8080 BIT_USER=… BIT_PASS=… node tests/e2e.js     # 117 Prüfungen im Browser + Bildschirmfotos
```

Der Browser-Test prüft jede Seite (HTTP-Status, keine PHP-Meldung, aktiver Menüpunkt,
korrigiertes Logo, Radius 16px überall, alle Bilder laden, kein Entwurf-Hinweis, kein
seitliches Scrollen auf dem Handy), dazu Suche/Filter, Galerie, Leasing-Rechner,
Kontaktformular bis in die Anfragen-Liste, und den Login.

## Auf hosttech installieren

1. WordPress installieren (Sprache Deutsch (Schweiz)).
2. Ordner `bit-automobile` als ZIP unter *Design → Themes → Theme hochladen*, aktivieren.
3. Benutzer für Sabit anlegen, Rolle **Redakteur**.
4. E-Mail-Versand prüfen (Formular) — ggf. SMTP über das hosttech-Postfach.
5. Demo-Fahrzeuge löschen, echte Fahrzeuge erfassen oder AutoScout24 verbinden.
6. MWST-Nr. erst eintragen, wenn bestätigt.
