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
  auf dieser Adresse wirkt. Eine Test-Mail wurde am 30.09. verschickt.
  → **Tifeki: bitte im Postfach info@ nachsehen, auch im Spam.**
- Zuletzt geändert (Commit «Startseite: kein Info-Balken …»):
  - Maus-«bit» 22px in Blau
  - Kein Info-Balken unter dem Titelbild, die Fakten stehen beim Showroom
  - Aare-Linie von Rand zu Rand
  - «Der Platz»: Das Foto wischt beim Scrollen weg
- Geprüft: lokal 202 Browser-Prüfungen und 45 PHP-Tests, alle bestanden. Auf der Testseite:
  15 Seiten, 17 Bilder und das Formular in Ordnung.

## Nächste Aufgaben (vom Nutzer freigegeben)

1. **Fahrzeugkarten beleben:** Beim Darüberfahren zoomt das Foto leicht, der Preis leuchtet
   blau auf. Klein und ruhig wie die übrigen Animationen, aus bei «Bewegung reduzieren».
2. **Zahlen, die hochzählen:** z. B. auf «Über uns»: seit 2022 · Anzahl Fahrzeuge (echte Zahl) ·
   4 Dienstleistungen. Nur Zahlen, die stimmen, nichts erfinden.
3. **Echte Fotos einbauen**, sobald Sabit sie liefert: Platz, Werkstatt, Team. Jedes Foto
   nur einmal pro Seite.

Danach jeweils:
- Lokal testen (`tests/e2e.js`, `tests/run-tests.php`)
- Über den Connector auf die Testseite spielen, mit Prüfsumme vor und nach dem Ersetzen
- Auf der Testseite prüfen und committen

## Fragen an Sabit (fragt Tifeki)

| Frage | Worum es geht |
|---|---|
| **Kostet die Fahrzeugbewertung etwas?** | Auf der alten bit-automobile.ch stand «gegen eine kleine Gebühr», und so steht es jetzt auch auf der Seite *Dienstleistungen → Fahrzeugbewertung*. Ist das noch so? Wenn ja, wie viel (z. B. «CHF 50, wird beim Verkauf angerechnet»)? Wenn nein, schreiben wir «kostenlos». Das ist ein gutes Verkaufsargument. |
| **AutoScout24-Zugang** | Für den automatischen Abgleich der Inserate braucht es von AutoScout24 für die **DMS-API**: *Seller-ID*, *Client-ID*, *Client-Secret*, am besten zuerst für die Testumgebung (Preproduktion). Das Secret nicht per Chat schicken, sondern selbst unter *Werkzeuge → AutoScout24* eintragen. |
| **MWST-Nummer** | Nur eintragen, wenn Sabit sie bestätigt. Bis dahin bleibt sie leer. |
| **Fotos** | Platz, Werkstatt, Sabit oder Team, Schild. Handyfotos reichen, die Seite verkleinert sie selbst. |

## Vor dem Livegang auf bit-automobile.ch (später)

- `wp-content/mu-plugins/bit-testseite.php` löschen, damit die Mails wieder an Sabit gehen
- Demo-Fahrzeuge löschen (*Werkzeuge → BIT Demo-Daten*)
- Suchmaschinen erlauben
- Umzug von Wix/Hoststar mit Zonenexport, MX nicht vergessen (siehe `BIT-AUTOMOBILE-DOSSIER.md`)

## Nicht weiterverfolgen

- ride2balkan.com und hoponeurope.com von netcup wegziehen: **nicht wichtig**, laut Nutzer
  vergessen.
