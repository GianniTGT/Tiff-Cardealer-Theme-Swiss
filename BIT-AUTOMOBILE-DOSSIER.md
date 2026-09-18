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

---

## 5 · Der kommerzielle Befund

`/fahrzeuge` liefert im Server-HTML **kein einziges Fahrzeug**. Kein AutoScout24, kein
Widget, kein iframe. Wix zeichnet manches per JavaScript, deshalb ist die saubere Aussage
nicht „leer", sondern die schärfere:

> **Google sieht keines seiner Fahrzeuge.**

Wer „Volvo XC60 Occasion Bern" sucht, findet ihn nicht — weil es nichts zu indexieren gibt.

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
