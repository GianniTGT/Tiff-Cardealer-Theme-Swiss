# Lokal arbeiten: Claude auf dem eigenen Computer

Damit Claude den Ordner direkt sieht und die Browser auf dem Computer bedienen kann,
läuft Claude lokal statt in der Cloud. Die Cloud-Sitzung bleibt möglich, kann aber weder
auf lokale Ordner noch auf die eigenen Browser zugreifen.

Dieser Computer: Windows, Benutzer `Gianni`.

## Was wo liegt

| Was | Pfad | Bemerkung |
|---|---|---|
| **Klon des Repos** (Code, Theme, Anleitungen) | `C:\Users\Gianni\Tiff-Cardealer-Theme-Swiss` | Aus `git clone` vom 01.10.2026 |
| **Eigene Unterlagen** (Fotos von Sabit, Dossiers) | `C:\Users\Gianni\myCloud\BIT AUTO` | Liegt in `myCloud`, wird also synchronisiert |

**Der Klon gehört nicht in einen synchronisierten Ordner.** Ein Cloud-Abgleich kann den
versteckten Ordner `.git` beschädigen. Darum bleibt der Klon unter `C:\Users\Gianni\` und
`BIT AUTO` ist nur der Ablageort für Unterlagen. Soll der Klon doch dorthin, sag es
Claude; dann zuerst die Synchronisierung für diesen Ordner ausnehmen.

## Einmal einrichten (auf dem Computer)

1. **Git** ist installiert, sonst klappt `git clone` nicht. (Browser: `https://git-scm.com`)
2. **Klonen** (PowerShell). Das ist schon erledigt:
   ```
   cd C:\Users\Gianni
   git clone https://github.com/GianniTGT/Tiff-Cardealer-Theme-Swiss.git
   ```
   `cd C:\Projekte` scheitert, wenn der Ordner nicht existiert. Dann landet der Klon im
   aktuellen Ordner.
3. **Claude Desktop-App** öffnen, Bereich **Code**, den Ordner
   `C:\Users\Gianni\Tiff-Cardealer-Theme-Swiss` wählen. Oder im Terminal:
   ```
   cd C:\Users\Gianni\Tiff-Cardealer-Theme-Swiss
   claude --add-dir "C:\Users\Gianni\myCloud\BIT AUTO"
   ```
   Mit `--add-dir` sieht Claude auch die Unterlagen in `BIT AUTO`.
4. **Browser:** In **Chrome** die Erweiterung «Claude in Chrome» installieren und mit dem
   Claude-Konto anmelden. Die Desktop-App hat zusätzlich einen eingebauten Browser.
   Die Bestätigung beim Novamira-Connector bleibt in **Chrome**, nicht in Edge.

## Bei jedem Start

1. PowerShell: `cd C:\Users\Gianni\Tiff-Cardealer-Theme-Swiss`, dann `git pull`.
2. Claude starten und sagen: **«Lies STAND.md und mach weiter.»**
3. Änderungen gehen wie bisher über einen Branch und einen Pull Request nach GitHub.
   Mergen kannst du auf github.com oder über Claude.

## Was lokal besser geht

- Claude ruft die Testseite von deinem Computer ab. Die Netzwerk-Regel der Cloud und der
  Bot-Schutz von hosttech («One moment, please…») stören dann kaum.
- Claude sieht Fotos direkt aus `BIT AUTO`, ohne dass du sie in den Chat ziehen musst.
- `tests/e2e.js` und `tests/run-tests.php` brauchen eine WordPress-Instanz oder die
  Testseite. Auf der Testseite: `BASE=https://bit.tiff-software-solutions.com node tests/e2e.js`.

## Gleich geblieben

- Zugriff auf WordPress über den **Novamira-Connector** (siehe
  `CLAUDE-MIT-WORDPRESS-VERBINDEN.md`).
- Passwörter, Anwendungspasswörter und Transfer-Codes nie in den Chat und nie ins Repo.
- Die Testseite bleibt für Suchmaschinen gesperrt.
