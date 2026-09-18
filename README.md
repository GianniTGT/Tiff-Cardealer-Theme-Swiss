# BIT Automobile — Webseite und Infrastruktur

Alles zum Schweizer Projekt: **BIT Automobile** (Handelsname der *ImmoBit AG*,
Oberwangen b. Bern, Kontakt Sabit Kadriu). Die geplante Webseite, ihre Infrastruktur —
Domain, DNS, Mail-Hosting — und die kaufmännische Seite dazu.

**Dieses Repository enthält seit dem 18. September 2026 nur noch dieses eine Thema.** Was
vorher hier lag, gehörte woanders hin; der Abschnitt weiter unten sagt genau, was
verschwunden ist und wo es jetzt steht.

---

## Was hier liegt

| Datei | Was es ist |
|---|---|
| `BIT-AUTOMOBILE-DOSSIER.md` | **Die Hauptdatei.** Firma, Domain, DNS, Mail-Hosting, Logo, die Fragen an Sabit, die fehlenden Dateien und das Zugangsproblem mit dem früheren Entwickler |
| `dossier/` | Dasselbe Dossier gestaltet — HTML mit abhakbarer Fragenliste, und als PDF zum Verschicken |
| `BETRIEB-UND-HOSTING.md` | Das Betriebs- und Hostingmodell: was der Betrieb einer solchen Seite kostet, was verrechnet wird, wer wofür haftet |
| `OFFERTE-VORLAGE.md` | Die Vorlage, aus der die Offerte entsteht |
| `offerte/` | Die gerenderte Offerte für BIT Automobile |

---

## Die zwei Befunde, auf die es ankommt

Beide sind gemessen, nicht erzählt — die Herkunft steht im Dossier an jeder Zeile.

**Die Zone liegt bei Wix, die Mail bei Hoststar.** `bit-automobile.ch` zeigt auf
`ns12/ns13.wixdns.net`, der MX aber auf `mail.bit-automobile.ch` → `168.119.41.56` →
`lx21.hoststar.hosting`. Wer die Webseite von Wix wegholt und den MX dabei vergisst, nimmt
Sabit die Geschäftspost mit. **Zonenexport vor jedem Umzug.**

**Google sieht keines seiner Fahrzeuge.** `/fahrzeuge` liefert im Server-HTML kein einziges
Auto — kein AutoScout24, kein Widget, kein iframe. Wer „Volvo XC60 Occasion Bern" sucht,
findet ihn nicht, weil es nichts zu indexieren gibt. Das ist das Verkaufsargument, und es ist
nachprüfbar.

---

## Was *nicht* hier liegt

**Der Gestaltungsentwurf für BIT Automobile ist in keinem Repository.** Er entstand in einer
Claude-Design-Sitzung und existiert nur dort. Solange das so ist, beschreibt dieses
Repository das Projekt, es enthält es nicht. Sobald der Entwurf als Dateien vorliegt, gehört
er hierher — dann bekommt dieses README einen Abschnitt mehr.

**Das WordPress-Theme ist nicht hier.** Es liegt in
[`Tiff-Cardealer-Manager`](https://github.com/GianniTGT/Tiff-Cardealer-Manager) im Ordner
`theme/`, und das ist die Quelle. Es ist ohnehin nicht das, was der Name dieses Repositories
vermuten liess: der Code war der Theme von **Downtown Auto Sales, Anchorage, Alaska** —
Prefix `das_`, Textdomain `das-v4`, `downtownautosale.com` in der `style.css`, das Logo des
Händlers einkompiliert. An diesem Theme ist nichts schweizerisch ausser dem
Repository-Namen.

---

## Der Aufräumschnitt vom 18. September 2026

Entfernt, weil es nicht zu BIT Automobile gehört:

| Was | Wohin | Nachweis |
|---|---|---|
| Die 34 Theme-Dateien (`style.css`, `functions.php`, `inc/`, `assets/`, alle Templates) und `DESIGN-SYSTEM.md` | `Tiff-Cardealer-Manager` → `theme/` | **34 von 35 Dateien byte-identisch** (Blob-Hashes verglichen, 18. September 2026). Die einzige Abweichung ist `README.md`: die Kopie dort trägt einen zusätzlichen Kasten, der erklärt, dass sie ab dem 15. September die Quelle ist |
| `business/SCHWEIZ-SAAS.md` | Historie von `Tiff-Cardealer-Manager` (Commit `78aa21c`) und die Historie dieses Repos | Anderes Produkt: ein Architekturvorschlag für ein künftiges Schweizer Mehrmandanten-Cloudprodukt. Nichts davon ist gebaut, und BIT kommt darin kein einziges Mal vor |
| `business/offerte/offerte-aino.html` | Nur die Historie dieses Repos | Andere Kundin |
| **AINO Haustechnik GmbH** aus `BETRIEB-UND-HOSTING.md` und `OFFERTE-VORLAGE.md` | Nur die Historie dieses Repos | Dieselbe andere Kundin, nur eingewachsen statt in eigenen Dateien — siehe unten |

Der Ordner `business/` ist dabei verschwunden: er trennte die Geschäftsseite vom Theme, und
das Theme ist weg. Sein Inhalt liegt jetzt auf der Wurzel.

### Wie AINO aus `BETRIEB-UND-HOSTING.md` herausgelöst wurde

Diese Datei war der schwierige Teil: sie handelte von *zwei* ersten Kunden, und der zweite
stand nicht in eigenen Dateien, sondern in acht Abschnitten mitten in der Argumentation.
Deshalb wurde sie gelesen und umgeschrieben, nicht durchsucht und ersetzt — **18 einzeln
geprüfte Änderungen**, jede an genau einer Stelle:

- **§9.6 gehörte ganz AINO** und ist entfernt; §9 heisst jetzt „Der erste Kunde".
- **Tabellenzeilen** in §9.4, §11.4 und §13.3 sind weg; die Absätze daneben sind auf einen
  Kunden umformuliert, nicht abgeschnitten.
- **Die Rechnungen in §14.4 und §15.3 wurden neu gerechnet**, nach derselben Formel wie
  vorher: aus `2'000 + 1'176 − 310 ≈ 2'866` für zwei Kunden wird `1'000 + 588 − 310 ≈ 1'278`
  für einen. Die Infrastruktur ist ein fester Block und wurde deshalb *nicht* halbiert.

**Zwei Dinge blieben absichtlich stehen**, und ein Kasten oben in der Datei sagt warum:
Giannis **wörtliche Zitate** in §13 und §14 nennen AINO — ein Zitat umzuschreiben, damit es
besser ins Dossier passt, wäre eine Fälschung. Und die Passagen über **Alaska** sind kein
fremdes Thema: dass TCM dort läuft, ist genau der Grund, warum die Schweizer Fassung eine
Portierung ist und kein Neubau. Das ist ein Argument über Sabit.

**Gelöscht heisst nicht weg.** Alles steht weiter in der Git-Historie. Eine Datei kommt mit
einem Befehl zurück:

```
git show 956909d:business/SCHWEIZ-SAAS.md > SCHWEIZ-SAAS.md
```

`956909d` ist der letzte Commit vor dem Schnitt.

---

## Offen

- **Registrar von `bit-automobile.ch`** — von aussen nicht sichtbar (WHOIS-Port 43 läuft in
  einen Timeout, RDAP antwortet 403). Liegt die Domain bei der Agentur, gehört sie **vor**
  jedem Umzug auf die ImmoBit AG übertragen.
- **MX und NS von `immobit.ch`** — die zweite Domain löst auf dieselben Wix-IPs auf, ihre
  Mail-Einträge sind noch nicht gemessen.
- **Die zwei kommerziellen AutoScout24-Fragen** — wie `client_id`/`client_secret` zu bekommen
  sind, und was die VIN-Abfrage kostet. Die technische Seite steht in
  `Tiff-Cardealer-Manager` → `AUTOSCOUT24-API.md`.
