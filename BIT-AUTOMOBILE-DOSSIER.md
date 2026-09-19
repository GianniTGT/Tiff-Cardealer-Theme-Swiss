# BIT Automobile — Dossier

**Stand: 18. September 2026.** Alles, was über den Schweizer Interessenten zusammengetragen
ist: Firma, Domain, Hosting, Mail, Logo — und der offene Konflikt um die Zugänge.

Jede Angabe trägt ihre Herkunft. Was gemessen ist, steht als gemessen da; was Sabit gesagt
hat, steht als seine Aussage da. Die beiden nicht vermischen — beim Umzug einer Domain hängt
an dem Unterschied seine Geschäftspost.

> Die gestaltete Fassung dieses Dossiers liegt in `dossier/` als HTML (mit abhakbarer
> Fragenliste) und als PDF.

---

## 1 · Firma

Quelle: Handelsregister des Kantons Bern.

| | |
|---|---|
| Firma | **ImmoBit AG** |
| Rechtsform | Aktiengesellschaft |
| UID | CHE-345.577.846 |
| Eingetragen | 5. Dezember 2022 · Statuten 1. Dezember 2022 |
| Sitz | Köniz |
| Domizil | Herzwilstrasse 262, 3173 Oberwangen b. Bern |
| Kapital | CHF 100'000, voll liberiert · 1'000 Namenaktien à CHF 100, vinkuliert |
| Zweck | Immobilien und Handel mit Neu- und Occasionswagen sowie Betrieb einer Autowerkstätte |
| Person | Sabit Kadriu, zeichnungsberechtigt |

**Die Rechtsperson heisst nicht BIT Automobile.** BIT Automobile ist ein Handelsname der
ImmoBit AG. Ein Impressum, das nur „BIT Automobile" nennt, ist falsch — es muss die AG und
die UID tragen.

Dazu eine Schreibweisen-Falle: das Register schreibt **BIT Automobile** ohne Bindestrich, die
Visitenkarte **BIT-Automobile** mit. Fürs Impressum gilt das Register; das Logo darf seine
Schreibweise behalten.

---

## 2 · Domain, DNS und Hosting

Öffentliche DNS-Einträge, selbst abgefragt am 16. September 2026. Kein Zugriff auf Postfächer
oder Konten.

| | |
|---|---|
| Domain | bit-automobile.ch |
| DNS-Zone | **Wix** — ns12.wixdns.net, ns13.wixdns.net |
| Webseite | **Wix** — 185.230.63.186 / .171 / .107 → unalocated.63.wixsite.com |
| www | 34.149.87.45 |
| Mail (MX) | **Hoststar** — 10 mail.bit-automobile.ch → 168.119.41.56 → `lx21.hoststar.hosting` |
| SPF | `v=spf1 a:bit-automobile.ch mx ~all` |
| DKIM | TXT an der Domain statt unter einem Selektor · `t=y` (Testmodus) · 13 übliche Selektoren geprüft, keiner existiert |
| DMARC | fehlt |
| Startseite | 632 KB |

**Zweite Domain: `immobit.ch`.** Löst auf dieselben drei Wix-IPs auf
(185.230.63.107 / .171 / .186) — also gleiche Web-Infrastruktur wie bit-automobile.ch.
MX und NS von immobit.ch sind **nicht** gemessen; der Resolver in dieser Umgebung beantwortet
nur A-Anfragen. Das ist nachzuholen, bevor irgendetwas umzieht.

### Beim Umzug hängt seine Geschäftspost daran

Die Zone liegt bei Wix, das Postfach bei Hoststar. Wer die Webseite von Wix wegholt, muss die
DNS-Zone mitnehmen — und dabei den MX-Eintrag exakt übernehmen. Wird er vergessen oder falsch
gesetzt, steht seine Mail.

Vorgehen: **Zonenexport vor dem Umzug**, MX und SPF eins zu eins übertragen, TTL vorher
runtersetzen.

### Pronar ligjor vs. kontroll teknik — dy gjëra të ndryshme

**Nachtrag vom 18. September 2026.** Gianni fragte, ob „Transfer zu ImmoBit AG" überhaupt
das richtige Wort ist, wenn beide Domains ohnehin schon Sabit gehören könnten. Die Antwort:
**es sind zwei verschiedene Fragen, und die Messungen oben beantworten nur die zweite.**

| Frage | Was sie bedeutet | Ist sie beantwortet? |
|---|---|---|
| **Wer ist rechtlicher Inhaber (Registrant)?** | Der Name, der beim Registrar hinterlegt ist | **Nein** — WHOIS/RDAP sind aus dieser Umgebung blockiert (§8) |
| **Wer kontrolliert die Nameserver technisch?** | Wer die Zone tatsächlich ändern kann | **Ja, teilweise** — die Zone liegt bei Wix, verwaltet vermutlich von Arca-IT (siehe §4) |

**Das ändert, was „Transfer" bedeuten kann:**

- **Ist ImmoBit AG bereits der eingetragene Inhaber** (Arca-IT hat nur als Dienstleister die
  Zone verwaltet) → kein rechtlicher Transfer nötig, nur die **Nameserver umstellen** — das
  kann sogar Arca-IT selbst tun, sobald sie das Ziel kennen.
- **Ist Arca-IT AG der eingetragene Inhaber** (Domain „für den Kunden" auf eigenen Namen
  registriert, eine verbreitete Agenturpraxis) → die Domain gehört **heute rechtlich noch
  nicht** ImmoBit AG. Dann braucht es einen echten **Inhaberwechsel** (Registrant-Wechsel,
  ggf. mit Auth-Code), bevor irgendetwas anderes zählt.

**Und das gilt für `immobit.ch` genauso — separat geprüft, nicht automatisch dasselbe
Ergebnis wie für `bit-automobile.ch`.** Beide Domains landen auf denselben Wix-IPs, das sagt
nichts über ihre jeweiligen Registrare.

**Die eine Frage, die das klärt** (an Sabit oder direkt an Arben): *„Seid ihr (ImmoBit AG)
der eingetragene Inhaber von `bit-automobile.ch` und `immobit.ch`, oder ist es Arca-IT AG?"*

---

## 3 · Der Mail-Zugang (Hoststar)

Am 18. September 2026 hat Gianni eine **Hoststar-KONTO-Seite** gezeigt. Was darauf steht:

| | |
|---|---|
| Benutzername | **`info@immobit.ch`** — nicht `info@bit-automobile.ch` |
| Webmail | https://webmail.hoststar.ch/ |
| Letzter Login | 2026-09-18 18:06:10 |
| Standard-Identität | `<info@immobit.ch>` |
| Hostname | **`lx21.hoststar.hosting`** |
| Unverschlüsselt | SMTP 25 / 587 · IMAP 143 · POP 110 |
| Verschlüsselt | SMTP-SSL 465 · IMAP-SSL 993 · POP-SSL 995 |

Drei Dinge folgen daraus:

1. **Der Server bestätigt die DNS-Messung.** `lx21.hoststar.hosting` ist exakt der Host, auf
   den der MX von bit-automobile.ch zeigt. Die Mail läuft bei Hoststar, nicht bei Wix — das
   war Giannis Vermutung und sie stimmt.
2. **Das ist ein Postfach-Zugang, kein Panel-Zugang.** Genau diese Klasse von Zugangsdaten
   braucht eine IMAP-Migration — und nur diese. Der Admin-Zugang zum Reseller-Panel wird für
   den Umzug der Post nicht gebraucht.
3. **Der Benutzername lautet auf `immobit.ch`.** Zu klären: ist das Sabits eigenes Postfach,
   und ist `immobit.ch` eine zweite, eigene Domain der AG — oder liegt hier eine dritte
   Partei dazwischen.

**Nachtrag vom 18. September 2026.** Gianni hat zusätzlich einen Screenshot der
**Hoststar-Login-Seite** gezeigt (`My Panel`, Login mit Domain + Passwort) — **leer, keine
Zugangsdaten eingegeben, kein Dashboard sichtbar.** Das ist die öffentliche Login-Maske, kein
Beleg für Zugriff. **Wichtig: `My Panel` ist etwas anderes als das Webmail oben** — `My Panel`
wäre die eigentliche Hosting-/Domain-Verwaltung bei Hoststar, während `webmail.hoststar.ch`
nur die Mailbox `info@immobit.ch` zeigt. Punkt 2 oben gilt unverändert: **`My Panel` wird für
den geplanten Umzug nicht gebraucht**, selbst wenn Arben es später doch freigibt — und selbst
ein Blick hinein würde nicht zwingend den Registrar-Inhaber zeigen, nur was innerhalb der
Hoststar-Hosting-Verwaltung liegt.

*Es wurde kein Postfachinhalt gelesen. Die Angaben stammen ausschliesslich von der gezeigten
Kontoseite.*

---

## 4 · Das Zugangsproblem

**Ausgangslage (Gianni, 18. September 2026):** Die Person, die Sabits Webseite gebaut hat,
gibt weder den Hosting- noch den Wix-Zugang heraus. Begründung: im Panel lägen 60 Postfächer
verschiedener Kunden.

### Die Begründung ist berechtigt — und blockiert fast nichts

Bei einem Reseller-Panel ist die Weigerung nach revDSG sogar korrekt: wer dort hineinkommt,
sieht fremde Kundendaten. Dieses Argument ist nicht zu bekämpfen, sondern zu umgehen. Es ist
nämlich für den Umzug gar nicht nötig:

- **Die Nameserver bestimmt der Domaininhaber, nicht der Hoster.** Wer die Domain beim
  Registrar hält, zeigt sie auf neue Nameserver — mit oder ohne Zustimmung des Entwicklers.
- **Die Mail zieht per IMAP um, mit den Postfach-Passwörtern.** Sabit bestellt eigenes
  Hosting, legt dort dieselben Adressen an und kopiert die alte Post per IMAP herüber — mit
  *seinen eigenen* Zugangsdaten. Der Admin-Zugang wird nie gebraucht. Die Serverdaten dafür
  stehen bereits fest: `lx21.hoststar.hosting`, IMAP-SSL 993.
- **Der Wix-Login wird überhaupt nicht gebraucht.** Die neue Seite entsteht unabhängig; beim
  Umschalten wird die Zone am Registrar freigegeben. Wix läuft danach einfach aus.

### Was zu verlangen ist — statt „des Logins"

1. **Registrar-Zugang bzw. Domaintransfer auf die ImmoBit AG.** Das ist der einzige Punkt,
   an dem es ohne die Gegenseite wirklich nicht geht. Die Domain gehört der AG, nicht dem
   Entwickler.
2. **Die Passwörter der eigenen Postfächer** (`info@…` und was sonst existiert). Kein
   Panel-Zugang — nur die Postfächer der AG.
3. **Sonst nichts.** Je kleiner die Forderung, desto schwerer ist sie abzulehnen.

### Das ist zugleich das stärkste Verkaufsargument

Sabits Geschäftspost liegt heute im Konto eines Dritten, der den Zugang verweigert. Solange
das so ist, gehört ihm seine eigene Mail faktisch nicht. Das ist kein technisches Detail,
sondern ein Geschäftsrisiko — und der beste Grund, den Umzug jetzt zu machen statt später.

### Der Name der Agentur — jetzt belegt, mit zwei offenen Punkten

**Nachtrag vom 18. September 2026.** Sabit hat zwei weitere Dokumente gezeigt. Erst eine
Dokument-Fusszeile „Erstellt von: Arca-IT AG" (Vermutung), dann eine **echte Rechnung**:

```
Arca-IT AG, Wölflistrasse 5, 3006 Bern — MWST CHE-492.626.439
Rechnung RE-00282, Kundennummer 000170, Datum 01.02.2024
An: Immobit AG, Breitmattweg 6, 3173 Oberwangen b. Bern
Pos. 1: Hosting www.immobit.ch — 1.00 Jahr — CHF 179.00
Total inkl. 8.10% MWST: CHF 193.50
```

**Das ist ein Beleg, keine Vermutung mehr: Arca-IT AG stellt Immobit AG Hosting für
`immobit.ch` in Rechnung.** Das erklärt auch die Weigerung aus §4 direkt — Arca-IT AG ist
selbst Reseller-Kunde bei Hoststar (`lx21.hoststar.hosting`, derselbe Host, auf den der MX
von `bit-automobile.ch` zeigt, §3) und verwaltet fremde Kundenpostfächer im selben Panel.

**Von Sabit bestätigt (18. September 2026): der Vertrag läuft noch, und der Ansprechpartner
bei Arca-IT AG heisst Arben.** Damit ist Punkt 1 erledigt — der Kontakt geht an eine aktive
Geschäftsbeziehung, nicht ins Leere.

**Ein Punkt bleibt offen:** Die Adresse weicht von der zuvor gezeigten ab — hier
`Breitmattweg 6`, auf der Visitenkarte vorher `Herzwilstrasse 262`, beides
3173 Oberwangen b. Bern. Entweder ist die AG umgezogen, oder es sind zwei unterschiedliche
Angaben im Umlauf. Für den Kontakt mit Arben unwichtig, für ein Impressum oder einen
Vertrag nicht.

### Kontaktaufnahme mit Arben — vorbereitet, noch nicht abgeschickt

**Stand 18. September 2026: wartend.** Kontaktdaten von Arca-IT AG per Impressum bestätigt
(nicht vermutet):

| | |
|---|---|
| E-Mail | **info@arca-it.ch** |
| Telefon | **031 829 80 80** |
| Adresse | Wölflistrasse 5, CH-3006 Bern |

*Kein persönlicher Arben-Kontakt öffentlich auffindbar — nur die allgemeine Firmenadresse.*

**Offen, bevor etwas verschickt wird:**
1. Wer schreibt — Sabit (Vertragskunde, mehr Gewicht) oder Gianni in seinem Auftrag?
2. Welcher Kanal — E-Mail an `info@arca-it.ch` z.Hd. Arben, oder Telefonanruf?

**Entwurf, eine einzige Forderung (§4):**

```
Betreff: Domain-Inhaberschaft bit-automobile.ch / immobit.ch

Grüezi Arben

Wir planen einen Website-Relaunch für ImmoBit AG (bit-automobile.ch) und
benötigen dafür eine kurze Bestätigung:

Ist ImmoBit AG der eingetragene Domaininhaber (Registrant) von
bit-automobile.ch und immobit.ch, oder läuft die Registrierung über
Arca-IT AG?

Falls die Domains bereits auf ImmoBit AG eingetragen sind, benötigen wir
lediglich die Möglichkeit, die Nameserver anzupassen. Falls sie auf
Arca-IT AG registriert sind, bitten wir um die Übertragung des Inhabers
auf ImmoBit AG.

Hosting-Zugang oder der Wix-Login werden dafür nicht benötigt.

Besten Dank und freundliche Grüsse
[Sabit Kadriu / ImmoBit AG]
```

**Nächster Schritt liegt bei Gianni/Sabit, nicht bei dieser Session:** entscheiden wer/wie,
dann abschicken.

### Durchbruch: beide Domains bei Hoststar zum Transfer freigegeben

**Stand 19. September 2026.** Jemand (vermutlich Arben, oder Sabit selbst) hat im
Hoststar-„My Panel" (§3) den Transfer beider Domains ausgelöst. Hoststar hat automatisch
bestätigt:

| Domain | Registrierungsperiode bis | Status |
|---|---|---|
| `bit-automobile.ch` | 2027-08-29 | **zum Transfer freigegeben** |
| `immobit.ch` | 2027-01-03 | **zum Transfer freigegeben** |

**Das beantwortet die offene Frage aus §2/§8 direkt: Hoststar ist der Registrar beider
Domains** — nicht Arca-IT AG separat, nicht ein Dritter. Damit ist der schwierigste Teil von
§4 (Registrar-Zugang) faktisch erledigt.

**Die Transfercodes selbst stehen absichtlich nicht hier.** Sie funktionieren wie ein
Passwort — wer sie hat, kann den Transfer auslösen — und gehören nicht in ein Git-Repo,
dessen Historie für immer bleibt. Sie sind bei Gianni/Sabit sicher hinterlegt (nicht im
Repo), **gültig 14 Tage ab Erhalt der Hoststar-Mail vom 19. September 2026** — danach
verfallen sie und müssen neu angefordert werden.

**Nächster Schritt:** Sobald der hosttech-Reseller-Account existiert (§5 in
`BETRIEB-UND-HOSTING.md`), dort mit dem jeweiligen Transfercode den Domain-Transfer
starten — **innerhalb des 14-Tage-Fensters**, sonst verfällt die Freigabe.

---

## 5 · Der kommerzielle Befund

`/fahrzeuge` liefert im Server-HTML **kein einziges Fahrzeug**. Kein AutoScout24, kein
Widget, kein iframe. Wix zeichnet manches per JavaScript, deshalb ist die saubere Aussage
nicht „leer", sondern die schärfere:

> **Google sieht keines seiner Fahrzeuge.**

Wer „Volvo XC60 Occasion Bern" sucht, findet ihn nicht — weil es nichts zu indexieren gibt.

### Impressum und Datenschutz fehlen auf immobit.ch — von Gianni berichtet, nicht gemessen

**Nachtrag vom 18. September 2026.** Gianni meldet: `https://www.immobit.ch/kontakt` hat
kein Impressum und keine Datenschutzerklärung. **Diese Session konnte das nicht selbst
prüfen** — der Zugriff auf `immobit.ch` ist aus dieser Umgebung netzwerkseitig blockiert,
wie schon bei anderen externen Domains in diesem Projekt. Als Sabits Aussage/Beobachtung
übernommen, nicht als Messung.

**Falls das stimmt, ist es ein echter Mangel, kein Stilfehler:** Ein Impressum ist in der
Schweiz für Geschäftswebseiten Pflicht (Art. 3 UWG), eine Datenschutzerklärung seit dem
revDSG ebenso, sobald Personendaten verarbeitet werden — und ein Kontaktformular tut das
per Definition. **Beides gehört in dieselbe Übernahme wie die Mail-Reparatur aus §12** (in
`BETRIEB-UND-HOSTING.md`): einmalige Arbeit beim Aufbau, kein laufender Posten, und ein
weiteres, überprüfbares Verkaufsargument neben der SEO-Lücke oben.

**Vor dem Bauen zu prüfen, nicht zu vermuten:** ob `bit-automobile.ch` (Wix) dasselbe fehlt,
und ob die Firmendaten aus §1 (ImmoBit AG, UID CHE-345.577.846) für ein korrektes Impressum
ausreichen.

**Nachprüfung für `bit-automobile.ch`, diesmal gemessen** — per PHP-`curl` über den
DAS-Server (normaler Internetzugang, siehe §10), Startseite abgerufen (633 KB, HTTP 200):

| | |
|---|---|
| Text „Impressum" | **vorhanden**, als Überschrift (`<h5>`) im HTML |
| Text „Datenschutzerklärung" | **vorhanden**, ebenso als `<h5>`-Überschrift |
| `href` zu einer Impressum-/Datenschutz-Seite | **keiner gefunden** |
| `/impressum`, `/datenschutz` als eigene Pfade | **beide 404** |

**Wahrscheinlichste Erklärung: Wix-„Lightbox"** — ein Popup-Fenster, das per JavaScript
öffnet statt über eine echte URL. Anders als bei `immobit.ch` (dort fehlt der Text
offenbar komplett) **existiert hier zumindest der Anspruch auf Impressum/Datenschutz** —
ob das Popup wirklich funktioniert und was drinsteht, ist damit noch nicht gemessen. Das
gehört per Browser geprüft, nicht per `curl`, bevor man daraus einen Befund macht.

---

## 6 · Kontakt und Auftritt

Von seiner Seite, gemessen am 14. September 2026.

| | |
|---|---|
| Telefon | 031 552 00 02 · 078 868 87 97 |
| E-Mail | info@bit-automobile.ch |
| Öffnungszeiten | Mo–Fr 08:00–18:00 · Sa 09:00–14:00 |
| Slogan | „Qualität findet hier ein Zuhause!" |
| Leistungen | An- und Verkauf · Fahrzeugaufbereitung · Carrosserie und Werkstatt · Fahrzeugbewertung |

---

## 7 · Logo

| | |
|---|---|
| Hausfarbe | `#3C77B2` — der einzige gemessene Farbwert |
| Transparenz | keine — blau auf weissem Grund |
| Original | 3555 × 961 px |
| Gebäudetafel | einzeilig, weiss, ohne Balken — beide *t* mit Querbalken auf beiden Seiten |
| Web und Karte | zweizeilig, blau, mit Balken — beide *t* nur nach rechts |

**Es sind zwei verschiedene Logos im Umlauf, nicht eines.** Wer von der Tafel auf die
Webseite kommt, sieht zwei Marken. Das muss Sabit entscheiden.

---

## 8 · Fragen an Sabit

- [ ] **AutoScout24: wie bekommt man `client_id` und `client_secret`?** Selbst im Portal oder
      per Vertrag — und darf TIFF im Auftrag des Händlers zugreifen? Diese Antwort entscheidet,
      ob es zwei Wochen Arbeit sind oder ein Geschäftsverhältnis.
- [ ] **Was kostet die VIN-Abfrage bei AutoScout24?** Die Spezifikation sagt es ausdrücklich:
      „There will be a fee to obtain vehicle information using the VIN." Pro Aufruf, im Abo,
      mit Kontingent? Es gibt kostenlose Alternativen über Stammnummer und Typenscheinnummer.
- [ ] **Ist der gezeigte Hoststar-Zugang (`info@immobit.ch`) seiner?** Und gehört `immobit.ch`
      der AG?
- [ ] **Wer ist bei Hoststar Vertragskunde — er selbst oder die Agentur?** Und welcher Tarif.
- [x] ~~Läuft der Arca-IT-AG-Vertrag heute noch?~~ **Ja, von Sabit bestätigt (18.9.2026).
      Ansprechpartner: Arben.**
- [ ] **Wer hält die Domain beim Registrar?** Von aussen nicht sichtbar (WHOIS-Port 43 läuft
      aus dieser Umgebung in einen Timeout, RDAP antwortet 403). Liegt die Domain bei der
      Agentur, gehört sie **vor** jedem Umzug auf die ImmoBit AG übertragen.
- [ ] **Wo pflegt er seine Fahrzeuge heute wirklich?** Gemessen ist nur, dass auf `/fahrzeuge`
      serverseitig nichts steht.
- [ ] **MWST-Nummer — ist die AG pflichtig?** Fehlt fürs Impressum.
- [ ] **Ist ausser ihm jemand zeichnungsberechtigt?** Der übermittelte
      Handelsregister-Auszug brach vor dem Abschnitt *Personen* ab.

---

## 9 · Dateien, die wir brauchen

- **Logo als AI oder EPS** — von der Firma, die die Tafel gemacht hat. Das ist das Einzige,
  was das Logo blockiert. Über ein PNG lassen sich gemessene Rechtecke legen, aber der neue
  Arm des *t* berührt den Nachbarn mit null Pixel Luft. Die Laufweite zu öffnen ist im Vektor
  eine Minute und über Bitmap unmöglich.
- **Visitenkarte als PDF oder AI** von der Druckerei. Das Blau der Karte wirkt tiefer als das
  der Webseite. Mit dieser Datei wird daraus ein exaktes CMYK oder Pantone — und Webseite,
  Tafel und Karte sagen zum ersten Mal dasselbe. Aus einem Handyfoto lässt sich kein Hex
  messen.
- **Echte Fahrzeugdaten** — Preise, Kilometer, Baujahre. Die Fotos sind seine, die Zahlen im
  Entwurf nicht.

---

## 10 · Was Sabit entscheiden muss

- **Welches der beiden Logos gilt** — Tafel oder Web/Karte. Heute sind zwei Marken im Umlauf.
- **Welche der vier Logo-Farben.**
- **Maus-Effekt auf „bit"**: Schatten / Sofort / Spur / Aus. Steht derzeit auf 32px.
- **Dunkel oder hell?** Der Entwurf wurde dunkel, weil ein grauer AMG und ein mattschwarzer
  911 auf Weiss die Hälfte ihrer Wirkung verlieren. Gianni hatte früher Weiss gewünscht — die
  Rückkehr ist ein Satz Variablen, kein Neubau.
- **Braucht er die Rolle „Verkäufer" überhaupt?** Arbeitet er allein, ist die Trennung
  Chef/Verkäufer Arbeit ohne Nutzen — dann sollte die Demo den Schalter verstecken, bevor er
  sie sieht. Hat er jemanden im Salon, ist es das stärkste Argument.

---

## 11 · Technische Arbeit, die sich sofort lohnt

Drei DNS-Einträge, eine halbe Stunde — und ein Nutzen, den er merkt, bevor über die Webseite
geredet wird.

- **DKIM an die richtige Stelle setzen.** Der Schlüssel steht heute direkt auf der Domain
  statt unter `<selektor>._domainkey`. So findet ihn kein Mailserver — er tut schlicht nichts.
  Dazu steht `t=y` darin, der Testmodus.
- **DMARC anlegen.** `_dmarc.bit-automobile.ch` existiert nicht. Ohne DMARC ist die Domain
  schlecht gegen Fälschung geschützt.
- **SPF schärfen.** `a:bit-automobile.ch` autorisiert die Wix-Webserver zum Mailversand — die
  versenden aber gar nicht. Und `~all` ist Softfail statt Hardfail.
- **Zonenexport sichern, bevor irgendetwas umzieht.** Der MX zeigt auf Hoststar, die Zone
  liegt bei Wix. Beim Wegzug von Wix muss der MX exakt mit — sonst steht die Post.

---

## 12 · Nicht verwechseln

**Im Entwurf und in der Demo sind Zahlen erfunden.** Preise, Kilometerstände und Baujahre
sind frei erfunden — die Seite sagt das an drei Stellen selbst. Echt sind seine eigenen
Showroom-Fotos und die Fahrzeugnamen, die daraus abgelesen wurden: AMG GT Roadster, 911 Turbo
Cabrio, AMG GT 4-Türer. Wenn du das jemandem zeigst: nur Sabit kann die echten Zahlen liefern.

**Eine Notiz ist inzwischen überholt.** In `CLAUDE.md` §26 (im Manager-Repo) steht noch
„Schweiz ist AutoScout24 → Webseite". Das ist seit dem 15. September falsch — Sabits eigene
Beobachtung hat die Richtung umgedreht auf **TCM → Webseite + AutoScout24**. Korrigiert ist
es in `AUTOSCOUT24-API.md` und der `TODO.md` des Manager-Repos; der alte Satz gehört bei
Gelegenheit nachgezogen.

---

### Herkunft der Angaben

Firma aus dem Handelsregister des Kantons Bern. Kontakt, Auftritt und Logo aus seiner Webseite
und den übermittelten Bildern, gemessen am 14. September 2026. DNS und Hosting selbst abgefragt
am 16. September 2026, `immobit.ch` am 18. September 2026 — ausschliesslich öffentliche
Einträge, kein Zugriff auf Postfächer oder Konten. Die Hoststar-Kontoangaben aus dem am
18. September 2026 gezeigten Bildschirmfoto.

### Technische Notiz

`bit-automobile.ch` ist aus einer Websitzung heraus nicht erreichbar (curl antwortet 000).
Gemessen wurde über das PHP des DAS-Servers, das normalen Internetzugang hat.
