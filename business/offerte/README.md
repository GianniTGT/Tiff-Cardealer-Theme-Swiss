# Die druckbaren Offerten

Zwei A4-Seiten, je **eine Seite**, in den Farben von Tiff Software Solutions.
Text und Begründung stehen in `../OFFERTE-VORLAGE.md`, die Preise in
`../BETRIEB-UND-HOSTING.md` §15.3.

| Datei | Für |
|---|---|
| `offerte-bit-automobile.html` | **ImmoBit AG** / BIT Automobile — Sabit Kadriu, Adresse vollständig |
| `offerte-aino.html` | **AINO Haustechnik GmbH** — Adresse und Anrede sind noch auszufüllen |
| `_offerte.css` | das gemeinsame Aussehen, A4-Druck |

## So machst du ein PDF daraus

Datei im Browser öffnen → **Drucken** → Ziel **„Als PDF speichern"** → Format **A4**,
Ränder **Standard** (oder „Keine" — die Ränder stehen im Stylesheet), Hintergrundgrafiken
**einschalten**, sonst fehlt der gelbe Kasten.

## Was noch auszufüllen ist

Die Stellen mit **gepunkteter goldener Linie** sind Platzhalter:

- **Beide:** deine Strasse, PLZ und Telefonnummer im Absender
- **AINO:** Anrede, Name, Strasse, PLZ und Ort

Suche im HTML nach `class="fuellen"` — dort stehen sie alle.

## Was du prüfen musst, bevor du sie schickst

| | |
|---|---|
| **MWST** | Die Seite sagt *„ohne MWST (nicht mehrwertsteuerpflichtig)"*. Stimmt das nicht, gehört dort *„zzgl. 8.1 % MWST"* hin und deine UID in die Fusszeile |
| **Datum** | Beide tragen den **15.09.2026** und gelten bis **15.10.2026** — an drei Stellen: Datum, Gültigkeit, Fusszeile |
| **Kündigungsfrist** | zwei Monate auf Ende Monat |

## Drei Sätze, die nicht verschwinden dürfen

Sie sind das Ergebnis von `../BETRIEB-UND-HOSTING.md` §11 bis §15 und sehen nur wie
Formulierung aus:

1. **Der gelbe Kasten** — *„Ein Liefertermin wird nicht zugesichert."* Er ist der Grund,
   warum das Abo mit der Website beginnt und nicht mit der Software (§14.3).
2. **Bedingung 2, Verfügbarkeit.** Ohne sie haftest du mündlich für die Erreichbarkeit einer
   Maschine, die du nicht reparieren kannst (§11.2).
3. **Drei Positionen statt einer.** Die Website kann enden, ohne dass die Software endet —
   und der nächste Kunde hat vielleicht schon eine Website (§15.2).

## Geprüft, nicht angenommen

Beide Seiten wurden mit Chromium nach A4 gerendert und die Seitenzahl im PDF gezählt:
**je eine Seite**, mit rund **12 mm Luft** am Fuss. Die erste Fassung brauchte zwei Seiten —
das war der Grund, es zu messen statt zu schätzen.
