# Stand und nächste Schritte

Übergabe für den nächsten Chat. Stand: 30.09.2026.
Im neuen Chat genügt: **«Lies STAND.md und mach weiter.»**

---

## Fertig

**Hosting (hosttech, Reseller Start, Plesk `357.hostserv.eu`)**
- `tiff-software-solutions.com` ist komplett von netcup zu hosttech umgezogen:
  - DNS-Zone im hosttech Kundencenter (DNS Editor), Nameserver ns1/ns2/ns3.hostserv.eu
  - Webseite (Astro), Postfach info@ mit SPF, DKIM und DMARC `p=none`
  - Let's-Encrypt-Zertifikat für Domain, www, webmail und mail, HTTPS-Weiterleitung an
- Die alten Mails liegen noch im netcup-Postfach info@, bis netcup gekündigt ist.

**BIT-Testseite `https://bit.tiff-software-solutions.com`**
- WordPress 7.1, PHP 8.4, Sprache Deutsch (Schweiz). Für Suchmaschinen gesperrt, das bleibt so.
- Theme `bit-automobile` aktiv, mit 11 Demo-Fahrzeugen. Die Permalinks sind gespeichert.
- Claude ist über den Connector **«Novamira - BIT Automobile»** verbunden
  (Anleitung: `CLAUDE-MIT-WORDPRESS-VERBINDEN.md`).
- **Kontaktformular:** Die Mails gehen auf der Testseite an **info@tiff-software-solutions.com**,
  nicht an Sabit. Dafür sorgt das mu-plugin `wp-content/mu-plugins/bit-testseite.php`, das nur
  auf dieser Adresse wirkt. Die Test-Mail vom 30.09. ist angekommen.
- Zuletzt geändert (Commit «Startseite: kein Info-Balken …»):
  - Maus-«bit» 22px in Blau
  - Kein Info-Balken unter dem Titelbild, die Fakten stehen beim Showroom
  - Aare-Linie von Rand zu Rand
  - «Der Platz»: Das Foto wischt beim Scrollen weg
- Geprüft: lokal 202 Browser-Prüfungen und 45 PHP-Tests, alle bestanden. Auf der Testseite:
  15 Seiten, 17 Bilder und das Formular in Ordnung.

## Nächste Aufgaben (vom Nutzer freigegeben)

1. ~~**Fahrzeugkarten beleben:**~~ (im Code erledigt) Beim Darüberfahren zoomt das Foto leicht, der Preis leuchtet
   blau auf. Klein und ruhig wie die übrigen Animationen, aus bei «Bewegung reduzieren».
2. ~~**Zahlen, die hochzählen:**~~ (im Code erledigt) z. B. auf «Über uns»: seit 2022 · Anzahl Fahrzeuge (echte Zahl) ·
   4 Dienstleistungen. Nur Zahlen, die stimmen, nichts erfinden.
3. **Echte Fotos einbauen**, sobald Sabit sie liefert: Platz, Werkstatt, Team. Jedes Foto
   nur einmal pro Seite.

Danach jeweils:
- Lokal testen (`tests/e2e.js`, `tests/run-tests.php`)
- Über den Connector auf die Testseite spielen, mit Prüfsumme vor und nach dem Ersetzen
- Auf der Testseite prüfen und committen

## Auf der Testseite (30.09.)

- **Fahrzeugkarten:** Foto-Zoom und Anheben gab es schon; neu leuchtet der Preis beim Darüberfahren blau auf (nur ohne «Bewegung reduzieren»).
- **Zahlen auf «Über uns»:** Am Standort seit (Gründungsjahr) · Fahrzeuge auf dem Platz (echte Anzahl, fehlt bei 0) · 4 Dienstleistungen. Die Fahrzeug- und Dienstleistungs-Zahlen zählen hoch, die Jahreszahl nicht. Ohne JavaScript steht die echte Zahl direkt da.
- Über den Connector eingespielt, Prüfsummen stimmen mit dem Repo überein. `/ueber-uns/` antwortet mit 200, ohne PHP-Fehler; die Zahlen 2022 · 11 · 4 stehen im HTML (11 sind die Demo-Fahrzeuge).
- **Browser-Prüfung 30.09.** (`tests/e2e.js` gegen die Testseite, Chromium): Preis wird beim Darüberfahren blau (weiß → #4A8FD1); auf «Über uns» zählen Fahrzeuge und Dienstleistungen hoch (0 → 11, → 4), das Jahr bleibt 2022, keine JS-Fehler; bei «Bewegung reduzieren» stehen die Zahlen sofort da. Seite «Über uns» besteht alle e2e-Prüfungen.
- **e2e gesamt auf der Testseite:** 1. Lauf 140 OK / 21 Fehler, 2. Lauf 178 OK / 12 Fehler, jeweils andere. Ursache: Der Server (hosttech) antwortet unter Last mit zufälligen 415-Fehlern auf Bilder, CSS und JS, dann fehlen Kopf/Fuss oder Filter. Einzelabrufe (65 von 65) sind immer 200. Nicht vom Theme. Vor dem Livegang klären (Hosting-Schutz gegen schnelle Abrufe?).
- **Tipp für e2e aus der Cloud:** Chromium vertraut dem Proxy-Zertifikat nicht. Start mit `--ignore-certificate-errors-spki-list=<SPKI-Hash von /root/.ccr/agent-proxy-ca.crt>`, ohne die Prüfung abzuschalten. Außerdem in der Umgebung «Network access» auf Full oder die Domain erlauben (gilt nur für neue Sitzungen).

## Änderungswünsche von Tifeki (30.09., abends) – erledigt und auf der Testseite

- **Start, «Kommen Sie vorbei»:** Der zweite Telefon-Knopf ist weg, «Route anzeigen» steht mittig. Telefon steht nur noch einmal im Kasten.
- **Start, «Der Platz»:** Zuerst kommt das Showroom-Foto (Herzwilstrasse 262), beim Wischen erscheint das bisherige Foto (AMG). Im Block «Kommen Sie vorbei» steht dafür `amg-detail.jpg`, damit kein Foto doppelt vorkommt (`platz-herzwilstrasse.jpg` nutzt eine Demo-Karte).
- **Kontakt:** Die zweite Faktenbox neben dem Formular ist weg (Visitenkarte oben genügt), das Formular hat die volle Breite.
- **Impressum:** Rechtsform, UID, MWST, Eintrag, Sitz, Statuten stehen nur noch im Kasten «Handelsregister», nicht mehr im Text. Der Text hat noch «Verantwortlich», «Zweck», «Kontakt», «Haftung», «Urheberrecht». Die Seite wurde auf der Testseite auch in der Datenbank angepasst (`inc/seed.php` gilt nur für neue Installationen).
- **Fuss:** «Diese Seite wurde gestaltet von Tiff Software Solutions», verlinkt auf https://tiff-software-solutions.com.
- **Ursache der zufälligen Fehler auf der Testseite:** hosttech hat einen Bot-Schutz («One moment, please…»). Bei schnellen Abrufen kommt eine Warteseite oder 415 statt CSS, Schriften und Bildern. Echte Besucher sind selten betroffen, automatische Tests schon. Vor dem Livegang bei hosttech nachfragen.

## Erledigt am 30.09.

- **Test-Mail** der Testseite ist bei info@tiff-software-solutions.com angekommen: Das Formular und der Mailversand über hosttech funktionieren.
- **Fahrzeugbewertung:** Der Text bleibt «gegen eine kleine Gebühr» (Entscheid Tifeki).
- **MWST-Nr.:** `CHE-345.577.846 MWST`. Laut UID-Register ist ImmoBit AG seit 01.01.2023 im MWST-Register aktiv (abgefragt 30.09.2026). Die Nummer ist im Theme voreingestellt und steht im Impressum. Achtung: Die MWST-Nummer im Dossier (CHE-492.626.439) gehört der **Arca-IT AG**, nicht BIT.

## Fragen an Sabit (fragt Tifeki)

| Frage | Worum es geht |
|---|---|
| **AutoScout24-Zugang** | Für den automatischen Abgleich der Inserate braucht es von AutoScout24 für die **DMS-API**: *Seller-ID*, *Client-ID*, *Client-Secret*, am besten zuerst für die Testumgebung (Preproduktion). Das Secret nicht per Chat schicken, sondern selbst unter *Werkzeuge → AutoScout24* eintragen. |
| **Fotos** | Platz, Werkstatt, Sabit oder Team, Schild. Handyfotos reichen, die Seite verkleinert sie selbst. |

## Vor dem Livegang auf bit-automobile.ch (später)

- `wp-content/mu-plugins/bit-testseite.php` löschen, damit die Mails wieder an Sabit gehen
- Demo-Fahrzeuge löschen (*Werkzeuge → BIT Demo-Daten*)
- Suchmaschinen erlauben
- Umzug von Wix/Hoststar mit Zonenexport, MX nicht vergessen (siehe `BIT-AUTOMOBILE-DOSSIER.md`)

## Nicht weiterverfolgen

- ride2balkan.com und hoponeurope.com von netcup wegziehen: **nicht wichtig**, laut Nutzer
  vergessen.
