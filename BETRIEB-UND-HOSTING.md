# Wer betreibt was — Websites, SaaS, und ob Gianni ein Hoster wird

**Stand: 15. September 2026.** Entscheidungsdokument. Nichts davon ist bestellt.

Gegenstück zu `KLIENT-I-RI.md`: dort steht, wie ein Kunde mit der **lokalen** Installation
ans Laufen kommt. Hier steht, wie es aussieht, wenn nichts mehr lokal ist — und wer dann
wofür geradesteht.

Anlass ist Giannis eigene Lage, in seinen Worten: ein Kunde mit Domain und ohne eigenes
Hosting bei ihm, der eine Website will und ausserdem der Schweizer Pilot für TCM Cloud ist.
Und die Frage dahinter: *wenn ich für mich sowieso Cloud beschaffe, kann ich das den Kunden
auch gleich anbieten?*

> **Aufgeräumt am 18. September 2026.** Diese Datei handelte ursprünglich von *zwei* ersten
> Kunden; der zweite, **AINO Haustechnik GmbH**, ist herausgenommen — samt dem Abschnitt 9.6,
> der ganz ihm gehörte, seinen Tabellenzeilen und den Rechnungen, die zwei Kunden
> zusammenzählten. Die Beträge in §14.4 und §15.3 sind dabei auf einen Kunden neu gerechnet,
> nicht gestrichen.
>
> **Zwei Dinge blieben absichtlich stehen.** Erstens Giannis wörtliche Zitate in §13 und §14
> — *„Ich mache Sabit und AINO jeweils eine Homepage"* ist gesagt worden, und ein Zitat
> umzuschreiben, damit es besser ins Dossier passt, wäre eine Fälschung. Zweitens die
> Passagen über **Alaska**: dort läuft TCM wirklich, und genau das ist der Grund, warum die
> Schweizer Fassung eine Portierung ist und kein Neubau — ein Argument über Sabit, kein
> fremdes Thema.
>
> Aus demselben Aufräumen zeigen die Verweise auf `SCHWEIZ-SAAS.md` (§4.3, §4.6, §2, §3) ins
> Leere: die Datei ist raus, weil sie ein anderes Produkt beschreibt. Sie steht in der
> Historie — `git show 956909d:business/SCHWEIZ-SAAS.md`.

---

## 0. Die Antwort in drei Sätzen

**Das sind drei Geschäfte, nicht eines**, und wer sie vermischt, bezahlt es an der
Supportnummer:

| | Was der Kunde kauft | Was er bekommt | Wo es läuft |
|---|---|---|---|
| **Website** | eine Dienstleistung | eine gepflegte Website | Reseller-Webhosting |
| **Software** (TCM Cloud) | ein Abo | einen Login | **eigener Server, getrennt** |
| **Hosting** | — | **verkaufst du nicht** | — |

**Ja, du kannst deinen Kunden das Hosting mitliefern** — aber nie als *Hosting*, sondern
als unsichtbare Zutat in etwas, das du sowieso verkaufst. Und **Website-Hosting und
SaaS-Server dürfen nicht dieselbe Maschine sein**, aus einem Grund, den dieses Projekt schon
zweimal bezahlt hat (§2).

---

## 1. Warum „Hosting verkaufen" die falsche Verpackung ist

Der Unterschied ist nicht kosmetisch. Er entscheidet, wofür du haftest.

**Verkaufst du Hosting**, verkaufst du Verfügbarkeit: der Kunde hat einen Vertrag über
einen Server, und wenn um 23 Uhr etwas nicht geht, ist das dein Versprechen, das gebrochen
ist — auf fremder Infrastruktur, die du nicht reparieren kannst.

**Verkaufst du Website-Betrieb**, verkaufst du Arbeit: Updates, Backups, SSL, kleine
Änderungen. Der Server ist dann dein Werkzeug, nicht dein Produkt. Geht etwas kaputt,
kümmerst du dich — aber du hast keine Betriebszeit zugesichert, die du nicht kontrollierst.

Gleiche Technik, gleicher Preis, völlig anderes Risiko. **Und für den Kunden ist die
zweite Variante die verständlichere**: er will keinen Server, er will eine Website, die
funktioniert.

### Was in „Website-Betrieb" gehört, und was ausdrücklich nicht

| Drin | Draussen |
|---|---|
| Hosting, SSL, Domain-DNS | **E-Mail** (§3) |
| WordPress- und Plugin-Updates | Texte schreiben, Fotos machen |
| Backup, und ein geprobter Restore | unbegrenzte Änderungswünsche |
| kleine Änderungen, z.B. 30 Min./Monat | SEO-Betreuung, Werbung |

Die rechte Spalte ist der Teil, der sonst stillschweigend mitwächst, bis die Pauschale
nicht mehr passt und niemand mehr weiss, wann das angefangen hat.

---

## 2. Die eine technische Regel: Websites und SaaS nie auf derselben Maschine

**Das ist die wichtigste Zeile in diesem Dokument.**

Eine WordPress-Website ist die meistangegriffene Software im Internet. Der SaaS-Server hält
die **gesamte Finanzgeschichte** mehrerer Schweizer Betriebe — Einkaufspreise, Margen,
Kundendaten, Rechnungen. Diese beiden Dinge gehören nicht in dieselbe Kiste, und zwar aus
vier Gründen, von denen dieses Projekt drei schon erlebt hat:

1. **Ein gekapertes Plugin bei Kunde B steht dann neben den Zahlen von Kunde A.** Das ist
   der ganze Grund. Alles andere ist Komfort.
2. **Geteilte Einstellungen brechen Nachbarn.** `CLAUDE.md` §12 hält es fest: die
   PHP-Version ist bei cPanel eine **kontoweite** Einstellung. Der Wechsel auf 8.4 nahm die
   ganze Website mit — *"Your PHP installation appears to be missing the MySQL extension"* —
   weil die neue Version ihre eigene Extension-Liste hat. Ein Kunde, der PHP 8.4 braucht,
   kippt damit den anderen.
3. **Ein Fehler in einer Datei nimmt alles mit, inklusive des Werkzeugs zur Reparatur.**
   §12, 18. August: acht Bytes Müll am Ende von `inc/cars.php`, und Front-End, wp-admin
   **und** die MCP-Verbindung waren gleichzeitig weg. Es brauchte einen Menschen am
   cPanel-Dateimanager.
4. **Schuldfrage.** Ist die Website langsam oder die App? Auf zwei Maschinen ist das eine
   Frage mit einer Antwort.

**Die Trennung kostet ungefähr 13 Franken im Monat.** Das ist der Preis dafür, dass ein
kompromittiertes Kontaktformular nicht die Buchhaltung eines Autohändlers erreicht.

---

## 3. E-Mail: nicht bei dir. Nirgends.

**Die stärkste Einzelempfehlung in diesem Dokument, und die am besten belegte.**

E-Mail ist dort, wo kleine Agenturen ihre Zeit verlieren: Spam, Quota voll, „meine Mails
kommen nicht an", Handy-Einrichtung, ein Postfach, das seit drei Jahren nicht geleert wurde.
Nichts davon ist bezahlbar, und alles davon kommt zu dir, sobald du das Postfach hältst.

**Und der Grund ist kein Anekdotenvorrat, sondern wie das Protokoll gebaut ist.** SMTP
nimmt eine Mail an und entscheidet **später**, was damit geschieht. Der Absender bekommt
„angenommen" — das Wegwerfen passiert Minuten danach, beim Empfänger, ohne Rückmeldung.

> **Eine Mail kann erfolgreich gesendet *und* nie angekommen sein.** Das ist kein Fehler,
> das ist der Normalfall bei falschem SPF, fehlendem DKIM oder einem Spamfilter. Es gibt
> keinen Bildschirm, auf dem das steht.

Deshalb ist E-Mail die Sorte Arbeit, die niemand sieht und die trotzdem Wochen frisst: es
gibt keine Fehlermeldung, an der man anfangen könnte, sondern nur einen Kunden, der sagt
„es kommt nichts an" — und drei völlig verschiedene Fehler, die alle so klingen (§12.3).

**Der Beleg dafür ist frisch und gehört Sabit selbst:** bei
`bit-automobile.ch` steht das DKIM am falschen Ort und wirkt deshalb gar nicht, ein DMARC
fehlt ganz, und Sabit beschwert sich, dass keine Mails kommen. Gemessen, nicht erinnert —
**§12.**

> **Frühere Fassungen belegten das mit der Downtown-Geschichte** (Gmail, SendGrid-Kreditlimit).
> **Auf Giannis Weisung vom 15. September gestrichen**, und er hat recht: dort hing die Live-Seite
> am SendGrid-**Privatkonto des früheren Entwicklers**, und ein fremdes Gratiskontingent, das
> vollläuft, ist eine Sonderlage und kein Muster, aus dem man für einen Schweizer Kunden etwas
> ableitet. Der Satz oben braucht sie nicht — er steht auf dem Protokoll, nicht auf einem
> Vorfall. Die Streichung wird vermerkt statt still gemacht (`CLAUDE.md` §1).

**Empfehlung: der Kunde kauft sein E-Mail selbst** — Microsoft 365, Google Workspace oder
Infomaniak — **auf seinen eigenen Namen**. Du hilfst beim Einrichten, einmal, gegen
Aufwand. Danach ist es sein Vertrag und sein Support.

Was du dafür brauchst: die **MX-Einträge** zeigen dann woanders hin als die Website. Das ist
normal und genau richtig — und der Fall, den §"Zwei Postfächer" dokumentiert.

---

## 4. Domains gehören dem Kunden. Immer.

Kunde A hat schon eine, Kunde B braucht eine. In beiden Fällen gilt dasselbe:

**Die Domain wird auf den Namen des Kunden registriert, auch wenn du sie bestellst und
bezahlst.** Du wirst technischer Kontakt und bekommst Zugriff.

Der Grund ist nicht Juristerei, sondern Anstand und Selbstschutz: **die Domain ist das
einzige, was ein Betrieb nicht ersetzen kann.** Daran hängt seine E-Mail-Adresse, seine
Visitenkarten, sein Google-Ranking, die Adresse auf dem Firmenauto. Wer sie auf den eigenen
Namen nimmt, sitzt in jeder späteren Meinungsverschiedenheit auf dem Pfand des anderen —
und das weiss der Kunde. Es kostet dich nichts, es richtig zu machen, und es ist das erste,
was ein aufmerksamer Kunde prüft.

**Und du kennst die Gegenprobe schon:** Tifs GoDaddy-Konto. §10 hält fest, wie viel Aufwand
*Delegate Access* gekostet hat, weil das Konto auf **Nexhati Tifeki** läuft und der
Verifizierungscode nach Alaska ging — um 04:20 Uhr dort. Das war richtig so, und genau
deshalb lohnt es sich, die Zugriffe von Anfang an sauber zu setzen statt später zu
reparieren.

**Die zweite Gegenprobe, live erlebt statt erinnert:** Sabit selbst, mit Arca-IT AG
(`BIT-AUTOMOBILE-DOSSIER.md` §4). Dass jemand dort im Hoststar-Panel eine Domain zum Transfer
freigeben konnte, bewies nur Kontozugriff — **die Frage, wer als Halter/Inhaber eingetragen
ist, blieb offen**, weil es beim Registrieren nie sauber geklärt wurde. Genau das darf bei
den eigenen hosttech-Kunden nie passieren.

### Checkliste für jede Domain im hosttech-Reseller-Panel

1. **Beim Registrieren/Anlegen im Panel: Domaininhaber = der Kunde**, mit seinem korrekten
   Firmennamen und seiner eigenen Adresse — nie „Tiff" oder Giannis eigener Name, auch wenn
   das Reseller-Konto technisch dir gehört.
2. **Sofort danach prüfen, nicht erst bei Bedarf:** ein Blick ins WHOIS/RDAP oder in die
   Domain-Detailansicht im Panel, ob der Halter-Eintrag wirklich den Kunden zeigt.
3. **Dem Kunden mitteilen, wo er das selbst nachschauen kann** — das ist der Unterschied zu
   Arca-IT: Transparenz von Anfang an, nicht erst auf Nachfrage.

---

## 5. Was konkret bestellt wird

**Das Reseller-Paket ist am 15. September auf hosttech.ch nachgeprüft** (Screenshot von
Gianni) und stimmt mit der Suche überein — **CHF 12.90/Mt, 100 GB NVMe, 20 Kunden,
20 Domains, unlimitierter Traffic, unlimitierte Anzahl E-Mail.** Die übrigen Preise stammen
weiterhin aus einer Websuche, weil diese Session an die Seite nicht herankommt; vor dem
Bestellen nachprüfen.

> **Vor dem Bestellen §10.2 lesen.** Kunde 1 bezahlt bereits ein Hosting bei Hoststar und
> benutzt es nur für die Mailboxen — gemessen, nicht vermutet. Es kann sein, dass für ihn
> gar nichts gekauft werden muss.

| | Produkt | Preis (laut Suche) | Wofür |
|---|---|---|---|
| **Websites** | hosttech **Reseller Webhosting «start»** | **CHF 12.90/Mt**, 100 GB, **20 Kunden** | beide Kundenwebsites und die nächsten achtzehn |
| **SaaS** | hosttech **vServer** | **ab CHF 12.90/Mt** | TCM Cloud, getrennte Maschine |
| Managed Server | | ab CHF 129.—/Mt | **nicht nötig** — siehe unten |
| Webhosting einzeln | | ab CHF 4.90/Mt | nur falls Reseller nicht passt |

### Warum Reseller und nicht ein Webhosting pro Kunde

Ein Paket mit **20 Kundenplätzen für 12.90** ist billiger als zwei Einzelpakete, und es
gibt dir das, was du wirklich brauchst: **getrennte Zugänge pro Kunde**. Jeder Kunde hat
seinen eigenen Bereich, kann sein eigenes Backup haben, und wenn einer geht, geht sein
Bereich — ohne dass jemand an den Nachbarn muss.

Es ist ausserdem der Unterschied zwischen *„ich hab da noch Platz auf meinem Hosting"* und
einem Betrieb, der ordentlich aussieht.

### Warum kein Managed Server

CHF 129/Monat kauft dir, dass jemand anders Updates und Patches macht. Bei einem
vServer mit Docker machst du das selbst — und bei **einer** Anwendung, die du selbst
geschrieben hast, ist das ein `docker compose pull && up -d` und kein Betriebsjob. Der
Aufpreis lohnt sich erst, wenn Ausfall richtig Geld kostet oder du nicht mehr selbst
verfügbar sein willst.

**Der vServer verträgt zehn bis zwanzig Mandanten dieser Grösse locker.** Das ist die
eigentliche Nachricht für die Kalkulation: **die Infrastruktur ist nicht der teure Teil.**
Bei einem Abo von CHF 80–150 pro Betrieb und Monat bezahlt der erste Kunde die ganze
Serverrechnung, und deine Zeit ist der einzige echte Kostenblock.

### Was nicht gespart wird: Backups ausserhalb

`CLAUDE.md` §10 sagt es schon und die GoDaddy-Sitzung hat es bewiesen: dort lagen **2.48 GB
Installatron-Backups auf genau der Maschine, die sie sichern.** Das hilft gegen einen
schlechten Edit und gegen nichts anderes.

Also: ein Ziel ausserhalb — Swiss Backup bei Infomaniak, ein S3-Bucket, oder auch nur eine
Platte im Büro in Bern. **Und einmal wirklich zurückspielen**, bevor der erste echte Kunde
draufliegt. Ein Backup, das nie zurückgespielt wurde, ist eine Vermutung.

---

## 6. Woran der Kunde bei der Software *nicht* rankommt

Bei der Website bekommt der Kunde einen Zugang. **Beim SaaS nicht — und das ist kein
Geiz, das ist der Sinn der Sache.**

Der Betrieb will keinen Server. Er will `app.<deinedomain>.ch` aufmachen, sich anmelden und
arbeiten. Er hat kein Hosting, er braucht keins, und er soll keins bekommen: Datenbank,
Verschlüsselung, Backup und Updates sind das, wofür er dich bezahlt.

Das ist genau die Umkehrung von `CLAUDE.md` §5, und es ist die gute Nachricht darin: die
drei Wege, ein Abo durchzusetzen — Rechnung und Vertrauen, signierte Lizenz, Online-Aktivierung
— **fallen alle weg.** Der Server *ist* die Lizenz. Wer nicht zahlt, dessen Mandant wird
abgeschaltet. Kein Signieren, kein Schlüssel auf Papier, keine Uhr, die man zurückstellen
kann.

**Die Kehrseite, offen gesagt:** damit hängt der Betrieb an dir. Fällt dein Server aus,
kann der Händler keine Rechnung schreiben. Bei der lokalen Version konnte er das immer.
Das ist ein echter Tausch und er gehört in den Vertrag — inklusive der Frage, **was der
Kunde bekommt, wenn er aufhört**: ein Export seiner Daten, in einem Format, das er öffnen
kann. Das ist eine Seite Papier und der Unterschied zwischen einem Produkt und einer
Geiselnahme.

---

## 7. Die Reihenfolge

1. **Reseller-Webhosting bestellen** (~13.—/Mt). Deckt beide Websites sofort.
2. **Domains ordnen.** Kunde A: hat eine — nur die DNS auf das neue Hosting zeigen, in
   seinem Konto. Kunde B: in **seinem** Namen registrieren.
3. **E-Mail wegdelegieren**, beide Kunden, auf ihren eigenen Namen. Einmal einrichten
   helfen, dann raus.
4. **Websites bauen und aufschalten.** Das geht ohne jede SaaS-Entscheidung.
5. **Den vServer erst bestellen, wenn der Pilot wirklich anfängt.** Nicht vorher —
   ein Server, der ein halbes Jahr auf ein Projekt wartet, ist ein halbes Jahr bezahlte Luft.
6. **Je eine Seite Vertrag**: Website-Betrieb, und SaaS-Abo. Was drin ist, was nicht, was
   bei Kündigung passiert.

**Punkt 4 und 5 sind absichtlich getrennt.** Die Websites bringen Geld und Referenzen,
sobald sie stehen, und sie hängen an keiner einzigen der offenen Fragen aus
`SCHWEIZ-SAAS.md` — nicht am Treuhänder, nicht an der QR-IBAN, nicht an der MWST-Methode.
**Das ist der Teil, der jetzt laufen kann.**

---

## 8. Was hier nicht entschieden wird

- ~~**Der Preis.**~~ **Benannt am 15. September — siehe §9.** Gianni hat CHF 29 gesetzt; was
  dieses Dokument dazu beitragen kann, ist die Rechnung dahinter, nicht die Zahl.
- **Ob es eine GmbH braucht.** Sobald Kundengelder und Kundendaten laufend durch deine Hand
  gehen, ist das eine Frage an einen Treuhänder — dieselbe Person, die ohnehin das
  `vat_scheme` bestätigen muss.
- **revDSG-Papierkram.** Als SaaS-Anbieter bist du Auftragsbearbeiter für deine Kunden; es
  braucht einen AVV und ein Bearbeitungsverzeichnis. Steht in `SCHWEIZ-SAAS.md` §4.6 und
  gehört zum Verkauf, nicht zur Technik.
- **Ob Hosttech der richtige bleibt.** Für den Start ja: du kennst ihn, er ist Schweizer,
  der Support ist deutsch. Wenn es zehn Mandanten sind, lohnt der Vergleich mit Exoscale,
  cloudscale und Infomaniak — wegen Managed Postgres und Objektspeicher, nicht wegen des
  Preises.

---

## 9. Der erste Kunde, und die 29 Franken

**Nachtrag vom 15. September 2026.** Aus „irgendwann ein Kunde" ist eine Firma mit Namen
geworden, und ein Preis steht im Raum.

| | Firma | Was | Stand |
|---|---|---|---|
| **1** | **ImmoBit AG**, Oberwangen b. Bern — Marke **bit-automobile.ch** | Website **+ TCM Swiss** | Website existiert bereits |

**Hier stand bis zum Abend des 15. September, die Firma habe von hier aus nicht geprüft
werden können** — und das war falsch. Der Satz bleibt als Korrektur stehen, weil ein
Dokument, das einen Irrtum still wegnimmt, nichts beibringt (`CLAUDE.md` §1). Für
**bit-automobile.ch** gilt er nicht: der direkte Weg ist gesperrt, der indirekte über den
DAS-Server nicht. **Gemessen in §10.**

### 9.1 Zuerst die Frage, die auf jede Rechnung durchschlägt

**Welche Firma unterschreibt, und welche steht auf den Rechnungen, die die Software
druckt?** Die Firma heisst **ImmoBit AG**, die Website **bit-automobile**. Das sind zwei
Namen, und die Software kennt nur einen davon — den, der auf der Rechnung, dem Kaufvertrag
und der QR-Rechnung steht, mitsamt **UID** und Bankverbindung.

**Diesen Fehler hat das Projekt schon einmal gemacht.** `CLAUDE.md` §15: die Firma heisst
*Downtown Auto Sales LLC* mit «s», die Domain `downtownautosale.com` ohne — und im Code
standen Adressen auf `@downtownautosales.com`, **einer Domain, die dem Betrieb gar nicht
gehörte.** Marke und Rechtsträger sind nie automatisch derselbe String.

Dazu kommt hier eine zweite Ebene: *ImmoBit* klingt nach Immobilien, verkauft werden
Autos. Wenn das zwei Geschäftsfelder in einem Rechtsträger sind, hängt daran, **welche
Firma die Fahrzeuge einkauft** — und damit die ganze MWST-Frage aus `SCHWEIZ-SAAS.md` §4.3
(fiktiver Vorsteuerabzug, `vat_scheme`). Eine Frage an Sabit Kadriu, zwei Minuten, und sie
gehört vor die erste Zeile Code.

### 9.2 Die 29 Franken — die Zahl ist nicht falsch, die Verpackung schon

**Der Marktanker, gemessen statt erinnert** ([bexio Preise 2026](https://magicheidi.ch/bexio-preise)):
bexio kostet 2026 **CHF 35 bis 119 pro Monat** bei Jahreszahlung, **45 bis 129** bei
Monatszahlung — und wurde im März 2026 um rund **20 % erhöht**. Der automatische
**Bankabgleich** ist erst ab *Advanced* (CHF 42) dabei, **Lager, Projektzeiten und
Dokumentenarchiv** erst ab *Optima* (CHF 69).

Jetzt vergleiche, was TCM Swiss können soll: Fahrzeugbestand, Offerte und Rechnung,
QR-Rechnung, MWST inklusive `vat_scheme`, Dokumentenarchiv, camt-Zahlungsabgleich,
Portalexport. **Das ist bexio-Optima-Umfang plus branchenspezifische Dinge, die bexio
überhaupt nicht hat** — MFK-Fristen, Stammnummer, fiktiver Vorsteuerabzug auf
Occasionen.

**Und Branchensoftware ist teurer als Generisches, nicht billiger.** Genau dafür zahlt ein
Betrieb: dass er die Felder nicht selbst erfinden muss.

Bei CHF 29 unterbietest du also das **günstigste** bexio-Paket, während du mehr lieferst.
Das ist kein Argument gegen 29 — es ist ein Argument dagegen, 29 den **Regelpreis** zu
nennen.

**Nachtrag vom 18. September 2026 — eine echte Offerte statt der Websuche.** Gianni hat ein
reales bexio-Angebot gezeigt, keine Preisliste:

```
Betreff: bexio spezial Angebot

Advanced Paket für CHF 504.- exkl. MwSt. mit 30% Rabatt
+ Lohnbuchhaltung CHF 300.- exkl. MwSt.
Total im ersten Jahr: CHF 652.80 exkl. MwSt. (Sie sparen CHF 151.20)

Einmalige Kosten:
1 x Buchhaltung Schulung für CHF 490.-
1 x Buchhaltung Schulung für CHF 490.-
1 x Lohnbuchhaltung Schulung für CHF 490.-

Total CHF 1470.- abzgl. 30%
Total neu: CHF 1029.- einmalig exkl. MwSt. (Sie sparen CHF 441.-)
```

**Das bestätigt den Anker von oben, mit zwei neuen Zahlen.** CHF 504.-/Jahr für *Advanced*
ist exakt die CHF 42/Mt aus der Websuche — der Marktanker stimmt. Zwei Dinge, die dort
fehlten:

1. **Ein Rabatt-Mechanismus.** Die 30 % gelten hier nur auf das Advanced-Paket, nicht auf die
   Lohnbuchhaltung — das ist die Art Kleingedrucktes, die eine reine Preisliste nicht zeigt.
2. **Einmalige Schulungskosten von CHF 1'029.—**, zusätzlich zum Jahrespreis. Das ist die
   bexio-Entsprechung zu dem, was §10.3/§15.3 für die Website vorschlagen — Aufbau/Einführung
   einmalig, Betrieb monatlich getrennt. **Auch bexio trennt Bauen vom Betreiben.**

### 9.3 Die Rechnung, damit die Entscheidung mit offenen Augen fällt

| Kunden | Umsatz/Jahr | Infrastruktur | Netto **vor** deiner Zeit |
|---|---|---|---|
| **2** | 696.— | ~400.— | **~300.—** |
| 10 | 3'480.— | ~500.— | ~3'000.— |
| 30 | 10'440.— | ~900.— | ~9'500.— |
| 100 | 34'800.— | ~2'500.— | ~32'000.— |

**Die Infrastruktur ist nie das Problem — die Zeit ist es.** Bei hundert Kunden zu 29
Franken: wenn jeder nur **20 Minuten Support im Monat** braucht, sind das **33 Stunden
monatlich** für CHF 2'900. Rund 88 Franken pro Stunde reiner Support — **und keine einzige
Stunde mehr für Entwicklung.** Ein Schweizer KMU im ersten Jahr braucht mehr als zwanzig
Minuten.

Das ist der strukturelle Punkt: **bei 29 Franken ist die Supportzeit die Grenze, nicht der
Server.**

### 9.4 Die Empfehlung: Pilotpreis mit benanntem Regelpreis

**Nimm die 29 — aber schreib sie als befristeten Pilotpreis in den Vertrag, mit dem
Regelpreis daneben.**

Der Unterschied ist kein Wortspiel, er entscheidet ein Gespräch in zwei Jahren:

- **Von 29 auf 89 erhöhen** ist eine Preiserhöhung. Die muss man begründen, sie fühlt sich
  für den Kunden wie eine Strafe fürs Treusein an, und sie kann den Kunden kosten. bexios
  eigene Erhöhung im März 2026 hat genau deshalb eine ganze Blogindustrie mit
  *«bexio Alternative»* beschäftigt.
- **Einen befristeten Rabatt auslaufen lassen** ist gar kein Gespräch. Es stand von Anfang
  an im Vertrag, der Kunde hatte zwei Jahre günstig, und er weiss warum: er hat ein
  unfertiges Produkt mitgetestet.

Und es ist ehrlich in beide Richtungen. Der Pilot *ist* weniger wert — er ist unfertig —
und er kostet den Kunden wirklich etwas: seine Rückmeldungen, seine Geduld, seine echten
Daten.

**Ein Vorschlag, den du überschreiben kannst — die Marktkenntnis hast du, nicht ich:**

| | Pilot (befristet) | Regel |
|---|---|---|
| Website-Betrieb | **29.—/Mt** | 39.—/Mt |
| TCM Swiss | **29.—/Mt** | **89.—/Mt** |

Für ImmoBit wären das **58.— im Monat** im Pilot statt 29. Immer noch unter bexio *Basic*
für **zwei** Produkte, und der Weg zu 128.— ist vorgezeichnet statt später erstritten.

### 9.5 Drei Dinge, die am Preis noch offen sind

1. ~~**29 wofür?**~~ **Beantwortet am 15. September: 29 im Monat, alles zusammen.** Das ist
   sehr wenig — allein ein gepflegter Website-Betrieb liegt in der Schweiz normal bei 29 bis
   49. Die Antwort darauf ist nicht, am Betrag zu drehen, sondern das Bauen vom Betreiben zu
   trennen: **§10.3.** Auf der Rechnung bleiben es **zwei Positionen, auch wenn die Summe
   eine ist** — die Website kann enden, ohne dass die Software endet, und der Kunde sieht,
   wofür er zahlt.
2. **Pro Firma oder pro Benutzer?** Undefiniert, und der Unterschied ist gross: eine Garage
   mit drei Leuten gegen eine mit einem. **Empfehlung: pro Firma**, mit einer fairen
   Obergrenze an Benutzern. Für einen kleinen Schweizer Betrieb ist das die
   verständlichere und attraktivere Form — und dein Mandantenmodell (`SCHWEIZ-SAAS.md` §3)
   rechnet ohnehin pro Firma.
3. **Monatlich und jährlich.** Üblich ist **Jahr = zehn Monate**: CHF 290 statt 348. Für
   einen Einzelkämpfer ist Geld im Voraus mehr wert als die zwei Monate Differenz.

---

## 10. Gemessen statt vermutet: was bei bit-automobile.ch wirklich läuft

**Nachtrag vom 15. September 2026.** §9 sagt, die Firma habe von hier aus nicht geprüft
werden können — das galt für den direkten Weg. Der indirekte funktioniert: **der
DAS-Server hat normales ausgehendes Internet** (`CLAUDE.md` §11), also liest er die Seite
und die DNS-Einträge, und diese Session liest nur das Ergebnis. Nichts wurde auf dem
DAS-Server geschrieben.

Anlass war ein Widerspruch. Gianni: *„die Webseite läuft auf WordPress, ist eine
Übernahme."* `CLAUDE.md` §26: *„The site is **Wix**."* Eine Messung gegen eine Erinnerung
entscheidet man nicht durch Auswählen.

### 10.1 Die Website ist Wix. Zweifelsfrei.

```
generator          Wix.com Website Builder
Server             Pepyaka            x-wix-request-id vorhanden
Nameserver         ns12.wixdns.net · ns13.wixdns.net
www                cdn1.wixdns.net
wp-content / wp-json / wp-includes   0 · 0 · 0
/wp-login.php      400, kein WordPress dahinter
Startseite         632'623 Bytes
```

Die **Nameserver** sind das stärkste Stück: die Domain wird bei Wix verwaltet, nicht nur
die Seite dort gehostet. `CLAUDE.md` §26 stimmt, auch in der Byte-Zahl.

**Und das ändert die Arbeit, nicht nur eine Zeile in einem Dokument.** „Übernahme" heisst
hier **nicht**, eine bestehende WordPress-Installation zu übernehmen. **Wix hat keinen
Export.** Die Seite wird neu gebaut, Texte und Bilder kommen von Hand mit. Genau das ist
der Entwurf, der für Sabit schon existiert (`CLAUDE.md` §26) — ein Neubau, kein Umzug.

### 10.2 Aber Gianni hatte halb recht, und die Hälfte ist Geld wert

Der MX-Eintrag zeigt nicht zu Wix:

```
MX      10 mail.bit-automobile.ch
        → 168.119.41.56
        → lx21.hoststar.hosting
SPF     v=spf1 a:bit-automobile.ch mx ~all
DKIM    vorhanden, k=rsa, t=y   (Testmodus)
```

**ImmoBit AG bezahlt bereits ein Hosting — bei Hoststar — und benutzt es nur für die
Mailboxen.** Ein solches Paket kann WordPress; dort steht vermutlich sogar eines
herum. Daher die Erinnerung „er hat WordPress": das Paket gibt es, die Website läuft nur
nicht darauf.

Das verschiebt drei Dinge aus §5 und §3:

| | war geplant | nach der Messung |
|---|---|---|
| **Hosting für Kunde 1** | Reseller-Platz bei hosttech | **erst hineinschauen.** Vielleicht reicht, was er schon bezahlt |
| **E-Mail** | „muss beim Kunden sein" (§3) | **ist schon beim Kunden**, mit SPF und DKIM. Nichts zu verkaufen, nur sauber zu lassen |
| **Wix-Gebühr** | nicht betrachtet | **fällt weg, sobald die neue Seite steht** — ein Verkaufsargument, das nichts kostet |

**Das DKIM steht auf `t=y`, Testmodus.** Das heisst: Empfänger sollen eine fehlgeschlagene
Signatur *nicht* hart bewerten. Für einen Testlauf richtig, im Betrieb ein halb
eingeschalteter Schutz. Beim Übernehmen wegnehmen — ein Zeichen Arbeit, und es gehört zu
dem, was §3 begründet: eine Mail kann erfolgreich gesendet und nie angekommen sein.

**Was dafür gebraucht wird: der Hoststar-Zugang von Sabit.** Nicht der Wix-Quellcode — der
ist lesbar und die Frage ist beantwortet.

### 10.3 Die 29 Franken sind beantwortet: monatlich, alles zusammen

Damit ist §9.5 Punkt 1 entschieden — und zwar in die Richtung, die dort als „sehr wenig"
bezeichnet wurde. **Die Empfehlung ändert sich deswegen nicht, sie verschiebt sich:** nicht
am Betrag drehen, sondern **das Bauen vom Betreiben trennen.**

| | | |
|---|---|---|
| **Aufbau, einmalig** | Website fertig, Inhalte drin, Wix abgelöst | **CHF 1'500–2'500** |
| **Betrieb, monatlich** | Hosting, Pflege, Software-Lizenz | **CHF 29** |

Der Grund ist Arithmetik und nicht Geschmack. Eine WordPress-Seite kostet in der Schweiz
üblicherweise **2'000 bis 5'000 einmalig**. Bei 29 im Monat für alles ist ein Aufbau von
2'000 nach **sechs Jahren** bezahlt — und bis dahin ist die Seite zweimal neu gemacht
worden.

| | pro Jahr |
|---|---|
| 2 Kunden × 29 × 12 | **+ 696.—** |
| Reseller-Webhosting 12.90/Mt | − 155.— |
| vServer 12.90/Mt | − 155.— |
| **bleibt vor Giannis Zeit** | **≈ 390.—** |

Dafür entstehen zwei Websites und eine Software, die es noch nicht gibt.

**Das Versprechen „29 im Monat" bleibt dabei wörtlich stehen** — es wird nur nicht mehr
gebeten, auch die Bauarbeit zu tragen. Ein Händler, der selbst Autos verkauft und keine
Abos, versteht die Trennung sofort: das Fahrzeug kostet einmal, der Service läuft monatlich.

**Wer darauf verzichtet, schreibt „Pilotpreis" daneben** und den Regelpreis in denselben
Vertrag — §9.4, unverändert. Was nicht geht, ist beides: kein Aufbaupreis *und* kein
benannter Regelpreis. Dann ist 29 in zwei Jahren bei zwanzig Kunden der Preis für alle.

---

## 11. Was verkaufst du eigentlich, wenn du kein Hosting verkaufst?

**Nachtrag vom 15. September 2026.** Giannis Einwand, und er trifft eine echte Lücke:
*„Ich verkaufe Webpräsenz inklusive Mail, ich mache das Design und sorge dafür, dass die
Mails ankommen — aber ich bin nicht verantwortlich für das Hosting. Und wenn ich Reseller
bin: bei wem läuft der Vertrag? Was verkaufe ich ihm?"*

§1 sagt „verkauf kein Hosting" und §5 sagt „kauf ein Reseller-Paket". Nebeneinander gelesen
klingt das wie ein Widerspruch. Es ist keiner — aber der Abschnitt hat nie ausgeschrieben,
**wer mit wem welchen Vertrag hat**. Das hier ist der fehlende Teil.

### 11.1 Zwei saubere Modelle, und beide sind erlaubt

| | **A — der Kunde hat das Hosting** | **B — du bist Reseller** |
|---|---|---|
| Hosting-Vertrag | **Kunde ↔ Hoster** | **Gianni ↔ hosttech** |
| Vertrag mit dem Kunden | Gianni ↔ Kunde: **Webbetreuung** | Gianni ↔ Kunde: **Webbetreuung** |
| Was du verkaufst | deine Arbeit | deine Arbeit, **Serverplatz still inbegriffen** |
| Server fällt um 23 Uhr aus | Vertrag des Kunden | **dein Telefon** |
| Kunde kündigt | nichts umzuziehen | die Seite muss umziehen |
| Du brauchst | einen Zugang | ein Paket, das du sowieso hast |

**In beiden Modellen verkaufst du dasselbe: Webbetreuung.** Der Unterschied liegt nur
darin, wer den Serverplatz bezahlt — und wen der Kunde anruft, wenn der Hoster ausfällt.

„Verkauf kein Hosting" heisst deshalb nicht *„werde kein Reseller"*. Es heisst: **auf der
Rechnung und im Vertrag steht nie das Wort Hosting als eigene Position.**

```
✅  Webbetreuung bit-automobile.ch, inkl. Serverplatz und Mail     CHF 29/Mt
❌  Hosting                                                        CHF 29/Mt
```

Das Wort entscheidet, was der Kunde gekauft zu haben glaubt. *Hosting* heisst für ihn: ich
habe einen Server, der läuft rund um die Uhr, und wenn nicht, schuldest du mir etwas.
*Webbetreuung* heisst: ich habe deine Arbeit gekauft.

### 11.2 Ehrlich dazu: das Wort allein schützt nicht

Die Bezeichnung hilft beim Erwarten, nicht beim Haften. Was wirklich entscheidet, ist ein
Satz im Vertrag. Einer reicht:

> *„Der Betrieb umfasst Pflege, Aktualisierung und Sicherung der Website sowie die
> Einrichtung und Betreuung der E-Mail-Zustellung. Der Serverplatz wird bei einem
> Drittanbieter bezogen. Eine bestimmte Verfügbarkeit wird nicht zugesichert; Störungen
> des Anbieters werden gemeldet und deren Behebung nachverfolgt."*

Ohne diesen Satz ist Modell B genau das, wovor §1 warnt: du haftest mündlich für die
Verfügbarkeit einer Maschine, die du nicht reparieren kannst.

### 11.3 Und Mail? Genau so, wie Gianni es beschrieben hat

Seine eigene Formulierung war richtig, sie braucht nur eine Trennung:

| | |
|---|---|
| **Die Postfächer** | kommen mit dem Hosting. **Nichts extra verrechnen** — dafür würde der Kunde umsonst zahlen |
| **Die Arbeit daran** | MX, SPF, DKIM, Zertifikate, und einmal wirklich prüfen, dass Mail ankommt. **Das ist Betreuung und steckt in den 29** |

Das ist keine Formalie. §3 sagt warum: **eine Mail kann erfolgreich gesendet und nie
angekommen sein** — SMTP nimmt an und entscheidet später, und es gibt keinen Bildschirm, auf
dem das Wegwerfen steht. Genau deshalb ist die Arbeit daran etwas wert, obwohl sie niemand
sieht. Und genau deshalb hat sie gerade ein Kunde, bei dem sie nie gemacht wurde (§12).

### 11.4 Für ImmoBit heisst das

| | Modell | Warum |
|---|---|---|
| **ImmoBit AG** (bit-automobile) | **A** | Er **hat** schon Hoststar (§10.2). Nicht anfassen: die Mail läuft dort mit SPF und DKIM, und Mail umziehen ist das Einzige, was beim Übernehmen wirklich wehtun kann. Du bekommst einen Zugang und verrechnest deine Arbeit |

**Dass ein späterer Kunde Modell B braucht, ist kein Fehler**, sondern die normale Lage
eines Einzelkämpfers: der eine Kunde bringt sein Hosting mit, der nächste nicht. Was in
beiden Fällen gleich bleiben muss, ist die Rechnungszeile und der Satz aus §11.2.

### 11.5 Was an Sabits Aufbau gemessen ist — und was nicht

```
Domain bit-automobile.ch      registriert 30.08.2023, Status active
  └─ DNS bei Wix              ns12.wixdns.net · ns13.wixdns.net
       ├─ www  →  Wix         die Website
       └─ MX   →  Hoststar    die Postfächer
```

**Der Registrar liess sich nicht lesen.** Die `.ch`-RDAP-Antwort nennt ihn nicht — wer die
Domain hält, steht im Wix-Konto oder auf einer Rechnung, und das ist nach §4 die wichtigste
Frage von allen: **die Domain gehört dem Kunden, auch wenn sie bei Wix liegt.**

**Warum jemand so etwas baut**, zwei Möglichkeiten — beide plausibel, die Messung
unterscheidet sie nicht:

1. **Wix hat keine Postfächer.** Wix verkauft dafür Google Workspace, pro Benutzer und
   Monat. Ein kleines Hoststar-Paket kostet etwa gleich viel und enthält mehrere Postfächer.
   Dann ist das Hosting **absichtlich** gekauft worden, nur für Mail.
2. **Hoststar war zuerst da** — samt WordPress — dann kam Wix, und die Mail blieb einfach
   liegen.

Die Domain ist von 2023, die AG von Dezember 2022. Das spricht eher für (1), beweist es
aber nicht. **Der Hoststar-Zugang beantwortet es in zwei Minuten** — und sagt gleichzeitig,
ob das Paket für die neue WordPress-Seite reicht.

---

## 12. „Es kommen keine Mails" — gemessen bei bit-automobile.ch

**Nachtrag vom 15. September 2026.** Sabit beschwert sich, dass keine Mails ankommen. Das
ist der erste echte Störfall dieses Kunden, und er ist zur Hälfte von aussen messbar —
**ohne Zugang zu irgendetwas.** Gemessen über den DAS-Server, nur Lesen: DNS-Abfragen und
TCP-Verbindungen zu öffentlichen Ports. Keine Mail gesendet, keine Anmeldung versucht.

### 12.1 Was bewiesen ist, und was ausdrücklich nicht

Giannis Skepsis war berechtigt — *„Sabit hat scheinbar Mail bei Hoststar, du sagst das, wir
können es nicht bestätigen."* Also sauber getrennt:

| | |
|---|---|
| ✅ Der MX zeigt auf `mail.bit-automobile.ch` → `168.119.41.56` → `lx21.hoststar.hosting` | DNS, eindeutig |
| ✅ Die Maschine **lebt**: **Dovecot (Ubuntu)** antwortet auf IMAP (143), IMAPS (993) und POP3 (110) | selbst verbunden, Banner gelesen |
| ❌ **Nicht** bewiesen: dass dort ein Postfach `info@bit-automobile.ch` existiert | der MX sagt, **wohin** geleitet wird — nicht, dass es jemand lesen kann |
| ❌ **Nicht** bewiesen: dass der Vertrag Sabit gehört | das steht auf einer Rechnung, sonst nirgends |

**Die Ports 25, 465 und 587 liefen in einen Timeout — das sagt nichts über seinen Server.**
Ausgehendes Port 25 ist auf dem DAS-Server gesperrt, wie auf fast jedem Shared Hosting. Ein
SMTP-Test ist von hier nicht möglich, und ein Timeout darf hier nicht als „sein Mailserver
ist tot" gelesen werden. Dass Dovecot antwortet, beweist das Gegenteil.

### 12.2 Zwei echte Fehler im DNS

**1. Das DKIM steht am falschen Ort und wirkt deshalb gar nicht.**

```
bit-automobile.ch        TXT   "v=DKIM1; k=rsa; t=y; p=MIGfMA0GCSqG…"    ← hier
<selektor>._domainkey.bit-automobile.ch                                  ← gehört hierhin
```

Neun übliche Selektoren abgefragt — `default`, `mail`, `hoststar`, `k1`, `s1`, `selector1`,
`selector2`, `dkim`, `google` — **keiner existiert.** Der Schlüssel ist veröffentlicht,
aber kein Empfänger schaut je an der Stelle nach, an der er liegt. Dazu steht er auf
**`t=y`**, Testmodus: „bewerte eine fehlgeschlagene Signatur nicht hart." Zwei Fehler in
einem Eintrag.

**2. Es gibt kein DMARC.** `_dmarc.bit-automobile.ch` ist leer.

Das SPF ist `v=spf1 a:bit-automobile.ch mx ~all`. Der `a:`-Teil erlaubt die A-Records der
Domain — und die zeigen auf **Wix' Webserver**, die keine Mail versenden. Was trägt, ist
`mx`, also Hoststar. Das Ganze endet auf `~all` (softfail).

**Was das zusammen bedeutet: ausgehend trägt ihn allein SPF.** DKIM ist faktisch aus,
DMARC fehlt. Bei Gmail und Outlook ist das genau der Zustand, in dem Mail im Spam landet —
**aber es erklärt nur die eine der drei möglichen Beschwerden.**

### 12.3 Drei Fehler, die alle gleich klingen — und das ist die erste Frage

„Es kommen keine Mails" ist keine Diagnose. Es sind drei Störungen mit demselben Satz:

| Was Sabit meint | Wo der Fehler sitzt | Erklärt §12.2 das? |
|---|---|---|
| **a)** Leute schreiben ihm, nichts kommt an | Postfach, Weiterleitung, Spam-Ordner — **oder es gibt kein Postfach** | nein |
| **b)** Seine Mails erreichen den Empfänger nicht | **DKIM und DMARC**, §12.2 | **ja** |
| **c)** Vom **Kontaktformular der Website** kommt nichts | **Wix**, nicht Hoststar — das Formular verschickt über Wix an eine Adresse im Wix-Konto | nein |

**Ohne diese Unterscheidung wird an der falschen Stelle gesucht.** Bei (c) hat Hoststar
nichts damit zu tun und das Postfach ist völlig in Ordnung; bei (a) nützt das schönste
DKIM nichts.

### 12.4 Drei Schritte, fünf Minuten, kein Zugang nötig

1. **Sabit fragen: a, b oder c?** Eine Frage, und sie entscheidet alles Weitere.
2. **Eine Mail von Giannis eigener Adresse an `info@bit-automobile.ch`.** Kommt eine
   Unzustellbarkeit zurück → das Postfach existiert nicht. Kommt nichts zurück und er sieht
   sie trotzdem nicht → sie liegt irgendwo, und dann braucht es den Zugang.
3. **Ihn eine Mail schicken lassen** und im eigenen Spam-Ordner nachsehen. Das ist der Test
   für (b).

**Schritt 2 macht Gianni von seiner eigenen Adresse**, nicht diese Session: eine Mail an
einen Dritten von einem fremden Server aus ist nichts, was eine Diagnose rechtfertigt.

### 12.5 Warum das die Preisfrage berührt

Dieser Störfall ist der Beweis für §11.3 an einem lebenden Beispiel: **die Postfächer kosten
nichts, die Arbeit daran ist der ganze Wert.** DKIM richtig setzen, DMARC anlegen, das SPF
aufräumen und einmal wirklich prüfen, dass Mail ankommt — das sieht der Kunde nie, und es
ist der Unterschied zwischen einer Firma, deren Anfragen ankommen, und einer, die nicht
weiss, wie viele sie verliert.

**Es ist auch das beste Verkaufsargument, das dieser Abschnitt hergibt** — weil es kein
Argument ist, sondern ein Problem, das er schon hat.

**Und es gehört in die Übernahme, nicht in den laufenden Betrieb.** Diese drei Einträge
sind einmalige Arbeit, sie gehören in den Aufbaupreis aus §10.3 und nicht in die 29.

---

## 13. Kann die Software aufs Reseller-Paket? Nein — und nicht aus Vorsicht

**Nachtrag vom 15. September 2026.** Giannis Frage, und sie ist die technisch wichtigste
des Tages: *„Ich brauche ein Hosting für mich, für diese beiden Softwares — TCM und TIF
Handwerker. Kann ich das bei hosttech an dieser Reseller-Geschichte dranhängen?"*

**Nein.** §2 sagt „nie dieselbe Maschine" und begründet das mit Risiko. Hier ist der
Grund aber ein anderer und ein härterer: **es geht schlicht nicht.** Das ist keine
Empfehlung, gegen die man sich entscheiden könnte.

### 13.1 Warum Shared Hosting eine Web-App nicht ausführen kann

Ein Reseller-Paket ist geteiltes PHP-Hosting. Es führt ein Skript aus, wenn jemand eine
Seite aufruft, und beendet es danach. TCM Cloud ist nach `SCHWEIZ-SAAS.md` §2 etwas
anderes:

| Was die Software braucht | Was Shared Hosting gibt |
|---|---|
| Ein **Programm, das dauernd läuft** (Node/API-Server) | Skripte, die pro Aufruf starten und enden — Dauerläufer werden **abgeschossen** |
| **PostgreSQL** | MySQL/MariaDB, und sonst nichts |
| **Docker**, eigene Dienste, eigene Ports | kein Root, keine Container, keine Ports |
| Hintergrundarbeit (Cron, Warteschlangen, camt-Abgleich) | bestenfalls ein einfacher Cron |

**Das ist der Unterschied zwischen einer Website und einer Anwendung.** Eine Website wird
bei jedem Klick neu gebaut; eine Anwendung läuft. Für das eine ist ein Reseller-Paket
gemacht, für das andere ein Server.

### 13.2 Also zwei Dinge, und zusammen kostet es ungefähr 26 Franken

| | Produkt | Preis | Was darauf läuft |
|---|---|---|---|
| **Websites** | Reseller «start» | **12.90/Mt** *(geprüft)* | die Kundenseiten, WordPress |
| **Software** | **vServer** | ab 12.90/Mt *(Suche)* | **TCM Cloud** |

**Beide Programme dürfen sich einen vServer teilen** — das widerspricht §2 nicht. Die Regel
dort trennt *Websites* von *Software*, weil eine Kunden-Website von jemandem bearbeitet
wird, der nicht Gianni ist, und weil dort PHP-Versionen und Plugins kaputtgehen. Zwei
selbst geschriebene Anwendungen in zwei Containern auf einer Maschine sind der Normalfall,
nicht ein Risiko.

**Und die Kapazität ist nicht das Problem** (§5): ein vServer trägt zehn bis zwanzig
Mandanten dieser Grösse. Der erste zahlende Kunde bezahlt die ganze Serverrechnung.

### 13.3 Die Reihenfolge, und was heute wirklich gekauft wird

**Heute nur das, was heute etwas trägt.**

| Wann | Was | Warum |
|---|---|---|
| **jetzt nicht** | Hosting für **Sabit** | er hat Hoststar (§10.2) — erst hineinschauen |
| **erst mit dem ersten Kunden ohne Hosting** | Webhosting | wer nichts hat, braucht es zuerst; Sabit hat etwas |
| **erst wenn die Software läuft** | **vServer** | ein Server für eine Anwendung, die es nicht gibt, ist zwölf Monate Miete für nichts |

**Ob heute schon das Reseller-Paket oder ein einzelnes Webhosting**, ist eine Rechnung und
keine Grundsatzfrage:

| Kunden | einzelne Pakete à ~4.90 | Reseller «start» |
|---|---|---|
| 1 | **~4.90** ✅ | 12.90 |
| 2 | ~9.80 ✅ | 12.90 |
| **3** | ~14.70 | **12.90** ✅ |
| 10 | ~49.— | **12.90** ✅ |

**Ab drei Kunden gewinnt der Reseller**, und er gibt dann auch das, was man wirklich will:
getrennte Zugänge pro Kunde. Bei einem einzigen Kunden ist er acht Franken teurer — kein
Betrag, über den man lange nachdenkt, wenn der dritte Kunde absehbar ist.

---

## 14. Der Plan, wie Gianni ihn am 15. September gerechnet hat

Seine Worte: *„Ich mache Sabit und AINO jeweils eine Homepage. Ich kenne beide, das sind
meine Freunde. Pro Seite 1'000 Franken, inklusive die E-Mail-Problematik. Und dann biete
ich beiden jeweils die Software für 29 Franken im Monat als Abo. Und ich supporte die
Webpräsenz ohne Hosting."*

**Das ist zum ersten Mal ein Angebot und keine Preisfrage** — und es ist in zwei von drei
Teilen richtig. §9.4 empfahl „Pilotpreis mit benanntem Regelpreis"; §10.3 empfahl, das
Bauen vom Betreiben zu trennen. **Er hat beides selbst gemacht**, ohne dass jemand am
Betrag drehen musste.

### 14.1 Die 1'000 Franken: sauber, und die einzige Position ohne offene Frage

Eine WordPress-Seite kostet in der Schweiz üblicherweise 2'000 bis 5'000 einmalig (§10.3).
**1'000 ist ein Freundschaftspreis, und das darf er sein** — aber es gehört gesagt, was
darin steckt, damit die dritte Seite nicht automatisch auch 1'000 kostet:

| | |
|---|---|
| Realistisch für Seite 1 | **20 bis 40 Stunden** — Aufbau, Inhalte von Hand aus Wix holen, Mail (§12), Impressum, Datenschutz, SEO |
| Macht | **CHF 25 bis 50 pro Stunde** |
| Seite 2 und 3 | **deutlich schneller**, weil die Theme schon existiert — dort wird 1'000 ein guter Preis |

**Das gilt aber nur für den nächsten Autohändler.** Die Tiff Cardealer Theme passt nur dort;
ein Kunde aus einer anderen Branche braucht eine zweite oder eine neutrale Theme, und seine
Seite geht dann *nicht* automatisch gleich schnell wie Sabits.

### 14.2 Das Loch im Plan: „ich supporte die Webpräsenz" — gratis, für immer

Das ist der eine Teil, der nicht aufgeht.

| | in seinem Plan | was wirklich passiert |
|---|---|---|
| Website **bauen** | 1'000 einmalig ✅ | einmal |
| Website **betreiben** | **nichts** | WordPress- und Plugin-Updates, Backups, Sicherheit, kleine Änderungen — **jeden Monat, für immer** |
| Software | 29/Mt | **existiert noch nicht** |

**Nichts davon ist dramatisch, und genau das ist die Gefahr:** es ist jeden Monat zu wenig
Arbeit, um eine Rechnung zu schreiben, und nach zwei Jahren hat es einen Tag im Monat
gefressen. Eine WordPress-Seite, die ein Jahr keine Updates bekommt, ist ausserdem kein
Zustand, den man einem Freund hinstellt.

### 14.3 Die Empfehlung: die 29 Franken beginnen mit der Website, nicht mit der Software

> **Der Betrag ist seit §15 überholt — Gianni hat noch am selben Abend selbst auf 49
> korrigiert, und er hat recht.** Der *Mechanismus* unten bleibt aber genau so: das Abo
> beginnt mit der Aufschaltung der Website, nicht mit der Software. §15 ändert nur die Zahl
> und teilt sie auf zwei Positionen.

**Ein Vorschlag, der seinen Preis nicht anfasst und beide Probleme gleichzeitig löst.**

> **CHF 1'000 einmalig** — Aufbau, Inhalte, Mail in Ordnung.
> **CHF 29 im Monat ab Aufschaltung** — Betreuung der Webpräsenz. **Die Software kommt
> später ohne Aufpreis dazu.**

Was das gegenüber „29 für die Software" ändert:

- **Er verdient ab dem ersten Monat**, nicht erst in einem halben Jahr.
- **Der Kunde bekommt ab dem ersten Monat etwas Echtes** — die Betreuung ist real und
  sofort lieferbar.
- **Kein Liefertermin wird geschuldet.** Das ist der grosse Gewinn: Ein Abo für eine
  Software, die es nicht gibt, ist ein Versprechen mit Datum. Bei Sabit ist das milder als
  bei einem Kunden, für den gar nichts existiert — TCM läuft in Alaska, die Schweizer
  Fassung ist eine Portierung und kein Neubau. Milder ist aber nicht keines.
- **Die Software wird zum Geschenk statt zur Schuld.** Wenn sie kommt, ist sie ein Grund
  für Dankbarkeit und für den nächsten Kunden — nicht die verspätete Erfüllung einer
  Rechnung, die seit acht Monaten läuft.

### 14.4 Ist das ein gutes Geschäft? Zwei ehrliche Antworten

| | |
|---|---|
| **Als Geschäft, für sich** | **Nein.** 1'000 einmalig und ~590 im Jahr, abzüglich ~310 Infrastruktur, für eine Website und eine Software. Rechnet man die Stunden dazu, ist es nahe null |
| **Als **erster Kunde**, der den zweiten und dritten möglich macht** | **Ja, und deutlich.** Genau dafür ist er da |

**Was dieser Kunde wirklich einbringt, ist nicht das Geld:**

- **Eine fertige Referenz**, die man dem nächsten zeigen kann.
- **Eine Theme und eine Software, die an einem echten Betrieb bewiesen sind** —
  `KUNDE-FRAGEN.md` existiert genau deshalb.
- **Einen Mann, der Gianni vertraut**, und der ihm sagt, was fehlt, statt zu kündigen.

**Die eine Bedingung, damit es das bleibt: es darf kein Muster werden.** Zwanzig Freunde zu
1'000 und 29 sind ein Vollzeitberuf ohne Lohn. Der Regelpreis gehört deshalb heute
aufgeschrieben, auch wenn ihn noch niemand bezahlt (§9.4) — und *ihm* gegenüber darf ruhig
stehen, dass er den Freundschaftspreis bekommt. Das ist kein Nachteil, das ist der Grund,
warum er später weiterempfiehlt.

---

## 15. CHF 49 — Giannis eigene Korrektur, und sie ist richtig

**Nachtrag vom 15. September 2026, Abend.** Er hat §14.4 gelesen und selbst nachgerechnet:
*„Das ist sogar ein ganz schlechtes Geschäft. 12.90 für den Reseller und 12.90 für den
vServer — da zahle ich 26 Franken, und eine Kunde deckt das ab."* Sein Vorschlag:
**CHF 49 im Monat für beides, Webpräsenz-Betreuung und TCM.**

**Das ist die beste Zahl, die in diesem Dokument bisher steht**, und sie kommt nicht von
hier. Die Rechnung dazu:

### 15.1 Was 49 gegenüber 29 wirklich ändert

Die Infrastruktur kostet **CHF 25.80 im Monat** — fest, unabhängig von der Kundenzahl,
bis der erste vServer voll ist (§5: zehn bis zwanzig Mandanten).

| | bei **29** | bei **49** |
|---|---|---|
| Deckt ein Kunde die Infrastruktur? | **knapp** — 29 gegen 25.80 | **ja, mit Abstand** |
| 2 Kunden, pro Jahr nach Infrastruktur | ~390.— | **~866.—** |
| 10 Kunden, pro Jahr | ~3'000.— | **~5'400.—** |
| 50 Kunden, pro Jahr | ~15'900.— | **~28'500.—** |

**Aber die Zahl, auf die es ankommt, ist keine dieser** — es ist der Stundenlohn des
Supports. §9.3 hat es für 29 ausgerechnet; dasselbe für 49, bei zwanzig Minuten Support pro
Kunde und Monat:

| | Stunden/Monat | Ertrag/Monat | **pro Stunde** |
|---|---|---|---|
| 50 Kunden zu **29** | 16.7 | 1'450.— | **~87.—** |
| 50 Kunden zu **49** | 16.7 | 2'450.— | **~147.—** |

**Das ist der ganze Unterschied**, und er entscheidet, ob Entwicklungszeit übrig bleibt.
Bei 87 ist der Support der Beruf; bei 147 finanziert er ihn.

### 15.2 Trotzdem: zwei Positionen, auch wenn die Summe 49 ist

**Das ist die einzige Änderung, die dieser Abschnitt an seinem Vorschlag vornimmt** — und
sie kostet den Kunden nichts:

```
Webbetreuung bit-automobile.ch, inkl. Serverplatz und Mail     CHF 20.— / Mt
TCM Swiss, Lizenz                                              CHF 29.— / Mt
                                                               ─────────────
                                                               CHF 49.— / Mt
```

Drei Gründe, jeder für sich ausreichend:

1. **Die Website kann enden, ohne dass die Software endet** — und umgekehrt. Bei einer
   einzigen Zahl muss man in dem Moment neu verhandeln, in dem ohnehin etwas schiefgeht.
2. **Der nächste Autohändler hat vielleicht schon eine Website.** Dann braucht Gianni einen
   Preis für die Software allein, und der steht dann schon da — statt aus 49 herausgerechnet
   werden zu müssen.
3. **Preise lassen sich einzeln erhöhen.** Wenn TCM Swiss in zwei Jahren mehr kann, steigt
   die eine Zeile. Die Webbetreuung bleibt, wo sie ist, und die Erhöhung ist begründbar.

**Und der psychologische Teil, der hier zählt:** Gianni hat ihm bereits *29 für die
Software* gesagt. Diese Aufteilung nimmt nichts zurück — die 29 bleiben wörtlich stehen,
es kommt die Betreuung dazu, die es vorher gar nicht gab. Das ist ein leichteres Gespräch
als „aus 29 werden 49".

### 15.3 Das Angebot in seiner endgültigen Form — **entschieden am 15. September 2026**

**Gianni: *„Gut, dann mache ich es so mit 20 und 29."*** Damit ist dieser Abschnitt keine
Empfehlung mehr, sondern der Preis. §9.5 Punkt 1 und §14 sind damit vollständig erledigt;
offen bleiben nur noch **pro Firma oder pro Benutzer** (Empfehlung: pro Firma) und der
**Jahrespreis** (üblich: zehn Monate statt zwölf, also 490 statt 588).

| | | |
|---|---|---|
| **Einmalig** | Website: Aufbau, Inhalte, Mail in Ordnung (§12) | **CHF 1'000.—** |
| **Monatlich, ab Aufschaltung** | Webbetreuung | **CHF 20.—** |
| **Monatlich, ab Aufschaltung** | Software-Lizenz (TCM Swiss) | **CHF 29.—** |

**Das Abo beginnt mit der Website, nicht mit der Software** — §14.3, unverändert und der
wichtigste Punkt der ganzen Konstruktion. Der Kunde zahlt ab Tag eins für etwas, das es
gibt; die Software kommt dazu, wenn sie fertig ist. **So wird kein Liefertermin geschuldet.**

Jahr 1 mit Sabit allein: 1'000 einmalig plus 588 Abo minus ~310 Infrastruktur ≈ **1'278**.
Ab Jahr 2 laufen **~278** weiter, ohne dass noch eine Seite gebaut wird. Die Infrastruktur
ist dabei ein fester Block und wächst mit dem zweiten Kunden kaum mit — deshalb fällt jeder
weitere fast ganz durch. **Der zehnte Kunde bringt ~5'400 im Jahr, und für ihn wird nichts
mehr gebaut ausser seiner Website.**

### 15.4 Zu den „60 % schon vorhanden" — die Schätzung ist fair, die Aufteilung nicht

Gianni: *„TCM steht zwar, aber es ist für Alaska gemacht. Es muss für die Schweiz
programmiert werden. Aber das ist mehr oder weniger 60 % schon vorhanden."*

**Der Anteil stimmt ungefähr** — `SCHWEIZ-SAAS.md` §1 hat es gemessen: ~2'200 Zeilen
neutraler Kern ziehen wörtlich um, ~2'800 Zeilen behalten ihr Muster und tauschen den
Inhalt, ~2'150 Zeilen fallen **ersatzlos weg**, weil sie nur existieren, wo es keinen Server
gibt. Rechnet man „was nicht neu erfunden werden muss", landet man in dieser Gegend.

**Aber der Anteil beschreibt die falsche Achse**, und das gehört gesagt, bevor jemand daraus
einen Termin ableitet:

| | Stand |
|---|---|
| **Die Fachlogik** — Kalkulation, Fristen, Statistik, Recon, Zahlungen | **~60–70 % vorhanden** |
| **Die Auslieferung** — Mehrmandantenfähigkeit, PostgreSQL, Anmeldung auf einem Server, Web-Oberfläche | **0 %** |
| **Das Schweizerische** — MWST-Schema, QR-Rechnung, camt-Abgleich, MFK, Stammnummer | **0 %** |

**Der vorhandene Teil ist der, der schon durchdacht ist; der fehlende ist der, der wehtut.**
Das ist kein Gegenargument gegen das Geschäft — es ist der Grund, warum §14.3 das Abo an
die Website hängt und nicht an die Software.

---

## 16. Zwei Geschäfte, nicht eines — und ein Terminproblem

**Nachtrag vom 19. September 2026.** Gianni stellt klar, was bisher vermischt war.

### 16.1 Das Gespräch mit Sabit galt nur `bit-automobile.ch`

**`immobit.ch` war nie Teil der Preisverhandlung** aus §9–§15. Die CHF 1'000 einmalig /
CHF 20 monatlich gelten für **`bit-automobile.ch` allein** — und dessen Website **ist noch
nicht fertiggestellt.**

### 16.2 `immobit.ch` ist ein zweites, eigenes Geschäft

`immobit.ch` hat **bereits eine eigene, laufende Website** — auf Wix, wie
`bit-automobile.ch`. **Korrektur gegenüber der ersten Fassung dieses Abschnitts:** „Website
existiert bereits" hiess hier fälschlich „kein Neubau nötig". **Stimmt nicht** — Wix hat
keinen Export (Dossier §10.1), das gilt für `immobit.ch` genauso wie für
`bit-automobile.ch`. Die Seite muss **von Hand nachgebaut** werden, nur mit vorhandenem statt
neu zu erstellendem Inhalt — schneller als ein Neubau von null, aber ein Neubau. Dazu kommt
**dasselbe Impressum/Datenschutz-Problem** wie bei `bit-automobile.ch` (Dossier §5), von
Gianni bestätigt.

**Entscheidung: derselbe Aufbaupreis wie `bit-automobile.ch` — CHF 1'000 einmalig**, statt
nach Aufwand differenziert. Einfacher zu kommunizieren, deckt die Arbeit sicher ab, kein
Rabatt für „weniger Aufwand", den man später rechtfertigen müsste. Dazu **CHF 20.—/Mt**
Betreuung, wie bei `bit-automobile.ch`.

**Beides ist Sabit noch nicht mitgeteilt** — Giannis Entscheidung, keine bereits vereinbarte
Position. Getrennt abzurechnen von `bit-automobile.ch`, mit derselben Logik wie in
§11.3/§15.2: zwei Domains, zwei Rechnungspositionen, auch wenn derselbe Kunde dahintersteht.

**Total für beide Domains: CHF 2'000 einmalig + CHF 40.—/Mt.**

### 16.3 Terminproblem: Gianni ist bis 5.10.2026 in den Ferien

**Die am 19. September erhaltenen Hoststar-Transfercodes** (Dossier §4) sind **14 Tage
gültig** — das Fenster schliesst rund um den 3. Oktober, **vor** Giannis Rückkehr. Das ist
**kein Notfall**: Hoststar stellt die Freigabe auf Wunsch jederzeit neu aus (dieselbe
My-Panel-Aktion), sie kostet nichts und ist nicht auf einen einmaligen Versuch begrenzt.

**Entscheidung: die Codes verfallen lassen, nichts überstürzen.** Der Domain-Transfer hat
ohnehin keinen Sinn, solange der hosttech-Reseller nicht steht und niemand während der
Ferien die Mail-Migration überwachen kann — ein Transfer ohne fertige Zielumgebung riskiert
genau den Mail-Ausfall, vor dem Dossier §2 warnt. **Nach der Rückkehr (ab 5.10.) neue Codes
anfordern, dann in einem Zug durchziehen.**

**Was bis dahin trotzdem geht, weil es kein Zugriffsfenster braucht:** das Gespräch mit
Sabit über die `immobit.ch`-Position (§16.2), und die Entscheidung, ob `bit-automobile.ch`
in der Zwischenzeit weitergebaut wird.
