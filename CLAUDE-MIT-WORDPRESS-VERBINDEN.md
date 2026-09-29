# Claude mit einer WordPress-Seite verbinden (Novamira)

So bekommt Claude (auch Claude Code in der Cloud) Zugriff auf eine WordPress-Seite.
Getestet am 30.09.2026 mit `bit.tiff-software-solutions.com`.

**Kurz:** Der Weg geht **immer über den Connector in claude.ai**, bestätigt im
**Chrome**-Browser. Befehle in der Cloud-Sitzung (`novamira auth login`,
`claude mcp add`) funktionieren dort nicht, siehe unten.

---

## Vorher prüfen (auf der WordPress-Seite)

| Was | Wo | Warum |
|---|---|---|
| Plugin **Novamira** installiert und aktiv | WordPress → Plugins | ohne Plugin keine Verbindung |
| **Permalinks gespeichert** («Beitragsname») | WordPress → Einstellungen → Permalinks → «Änderungen speichern» | sonst gibt `/.well-known/oauth-…` 404, und die Anmeldung scheitert |
| **SSL-Zertifikat** aktiv (https) | Plesk → Domain → SSL/TLS-Zertifikate → Let's Encrypt | die Anmeldung läuft nur über https |

Nach einer neuen WordPress-Installation die Permalinks **immer einmal speichern**, auch
wenn schon «Beitragsname» gewählt ist. Erst dann schreibt WordPress die
Weiterleitungsregel für den Server.

## Verbinden: Schritt für Schritt

**Browser: Chrome.** Mit Edge klappt die Bestätigung nicht.

1. **Im WordPress-Admin** der Seite (in Chrome angemeldet) das Menü **Novamira** öffnen und
   **Claude AI** wählen. Dort auf **«Add the connector to Claude AI»** klicken.
   Oder von Hand: **claude.ai → Einstellungen → Konnektoren → «Benutzerdefinierten
   Konnektor hinzufügen»**
   - Name: z. B. `Novamira - BIT Automobile`
   - URL: `https://<domain>/index.php?rest_route=/mcp/novamira-oauth`
2. claude.ai öffnet die Anmeldung. Sie leitet auf die **WordPress-Seite** weiter.
3. In WordPress erscheint eine **Meldung zur Freigabe** → **Zugriff erlauben**.
4. Zurück in claude.ai steht der Connector als «verbunden».
5. In Claude Code erscheint er als Werkzeug `Novamira - <Name>`. Eine laufende Sitzung
   zeigt ihn meist sofort, sonst eine neue Sitzung starten.

Passwörter und Anwendungspasswörter gibt man **nur im Browser** ein, nie im Chat.

## Was nicht funktioniert (Zeit sparen)

| Versuch | Ergebnis | Grund |
|---|---|---|
| `novamira auth login https://…` in der Cloud-Sitzung | `OAuth metadata is malformed or unavailable` | Die Cloud-Umgebung darf die Domain nicht erreichen (Netzwerk-Regel) |
| `claude mcp add --transport http … /mcp/novamira-oauth` in der Cloud-Sitzung | `Failed to connect` / `ERR_PROXY_TUNNEL` | derselbe Grund |
| Bestätigung in **Edge** | bleibt hängen | in **Chrome** machen |

Der Connector in claude.ai läuft nicht über diese Sperre. Darum ist er der richtige Weg.
Die Alternative wäre, die Domain in den Einstellungen der Cloud-Umgebung
(Network access) freizugeben. Das ist unnötig, solange der Connector geht.

## Fehlersuche

| Zeichen | Lösung |
|---|---|
| `/kontakt/`, `/wp-json/` oder `/.well-known/oauth-protected-resource` geben 404 | WordPress → Einstellungen → Permalinks → speichern |
| Hilft das nicht | Plesk → Domain → «Apache & nginx-Einstellungen» → «Zusätzliche nginx-Anweisungen»: `if (!-e $request_filename) { rewrite ^(.*)$ /index.php last; }` |
| Freigabe-Meldung erscheint nicht / hängt | Chrome statt Edge; in Chrome vorher im WordPress-Admin angemeldet sein |
| Connector verbunden, aber Claude sieht ihn nicht | neue Claude-Code-Sitzung starten |

## Verbundene Seiten

| Seite | Connector-Name in claude.ai | Hosting |
|---|---|---|
| `bit.tiff-software-solutions.com` (BIT-Testseite, für Google gesperrt) | Novamira - BIT Automobile | hosttech (Plesk 357.hostserv.eu) |
| `hoponeurope.com` | novamira-hoponeurope-com | netcup (der WordPress-Ordner dahinter heisst `ride2balkan.com`) |
