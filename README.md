# Tiff Cardealer — the WordPress theme

> ## ⛔ Dieses Repository ist nicht mehr die Quelle
>
> **Seit 15. September 2026 lebt das Theme im Manager-Repository**, als Unterordner:
> [`GianniTGT/Tiff-Cardealer-Manager` → `theme/`](https://github.com/GianniTGT/Tiff-Cardealer-Manager/tree/main/theme).
> So hat es der Inhaber entschieden. Was hier liegt, ist eine eingefrorene Kopie —
> **jede Änderung gehört dorthin**, nicht hierher.
>
> **Verloren ist nichts.** Beim Umzug geprüft: 35 Dateien auf beiden Seiten, alle Blob-Hashes
> identisch bis auf diese `README.md`. Die Historie dieses Repos und die beiden Pull Requests
> bleiben hier lesbar:
>
> | | |
> |---|---|
> | [#1](https://github.com/GianniTGT/Tiff-Cardealer-Theme-Swiss/pull/1) | Design-Session vom 15.09. — Motion-Ebene, Refined-Look, Markenspur, Kontrast, der Seitenrand-Fehler auf dem Handy |
> | [#2](https://github.com/GianniTGT/Tiff-Cardealer-Theme-Swiss/pull/2) | Submenü verschwand hinter dem Hero im Refined-Look |
>
> **Zum Namen, weil er die Verwirrung ausgelöst hat:** trotz „Swiss" enthielt dieses
> Repository nie eine Schweizer Variante. Es war von Anfang an das Theme, das
> `downtownautosale.com` in Anchorage rendert — am 15.09. gemessen, byte-identisch mit
> `wp-content/themes/das-v4/` auf dem Server. Die Schweizer Hälfte ist laut `DESIGN-SYSTEM.md`
> noch nicht gebaut.
>
> **Live zum Zeitpunkt des Umzugs: Version 1.3.1.**


The website half of the Tiff Cardealer product line. The desktop half is
[`Tiff-Cardealer-Manager`](https://github.com/GianniTGT/Tiff-Cardealer-Manager), and its
`CLAUDE.md` is the shared memory for both — every `§` reference below points there.

**This repository was created on 13 September 2026, and the theme is older than it.** Until
that day the theme existed **only** on one customer's live server. Everything before the
first commit is in `CLAUDE.md` §11, which is long and is the real history.

---

## The first commit is a snapshot, not a cleanup

It is exactly what was running on `downtownautosale.com` — folder `das-v4`, prefix `das_`,
theme version 1.2.0 — with nothing renamed and nothing tidied.

**That was a decision, and the alternative is the trap.** Renaming in the same breath would
make the first commit a theme that has never run anywhere, and §12 records what an untested
write to this theme costs: on 18 August a half-written script left eight bytes of garbage on
`inc/cars.php` and **took the whole site down** — front end, wp-admin, and the tool that
would have fixed it. It took a person at a cPanel File Manager to restore one file.

So: the repository first agrees with reality. Every change after that is a change somebody
can read as a diff.

### How it got here, and how to do it again

The theme was pulled through the site's own Novamira connector (`novamira/execute-php`
returning `file_get_contents`), because **the sandbox cannot reach the site over HTTPS** —
measured, `000`, the network policy refuses it. Then every file was checked:

```
31 of 31 files match the server byte for byte   (md5, 13 September 2026)
```

**Do that again if you ever re-pull.** A transfer that was not verified is a transfer you are
hoping about — rule 15 in `KLIENT-I-RI.md`.

### What was deliberately left out

| | why |
|---|---|
| `assets/img/team/*.{jpg,webp}` | photographs of real people at one dealership. Dealer content that happens to sit in the theme folder — §5. The empty `index.html` stays as the directory-listing guard |
| `_backup-20260814-105228/` | a backup folder from 14 August. Backups are not the theme |
| `inc/cars.php.bak`, `style.css.bak` | same |

---

## Four things block the second dealership, and all four are measured

None is a bug. Each is the dealer's identity sitting inside a product that is sold to
somebody else, which is exactly what §5 exists to prevent — and the theme has never had a
`scripts/status.mjs` to catch it the way the Manager does.

| | where | what it should be |
|---|---|---|
| **The dealer's logo is compiled in** | `assets/img/logo.png`, 178×96, read by `inc/template-tags.php` and `inc/watermark.php` | uploaded per dealership, or an option — never a file in the theme |
| **`Theme URI` and `Author URI`** | `style.css`, both `https://downtownautosale.com/` | the vendor's own site. A customer's address as the *author's* URI is the wrong way round |
| **`Text Domain: das-v4`** | `style.css` | `tiff-cardealer` |
| **The folder and the function prefix** | `das-v4/`, `das_*` | `tiff-cardealer/`, `tcd_*` — §5 has recorded this as "a separate job" since August |

**The rename is one job and it is cheapest now**, because the second dealership's site is the
first one that will carry the new name. It is not the first commit's job.

**Do it as its own change, with the live site in mind**: `das_` is ~200 call sites, the
folder name is what `wp-content/themes/` keys on, and changing the active theme's directory
means WordPress loses track of it until the option is updated. Test it somewhere that is not
a dealership's live site.

---

## Where this theme is installed

| | |
|---|---|
| `downtownautosale.com` | Downtown Auto Sales, Anchorage, Alaska. Live since the DNS switch (§10) |

**The desktop app writes to this site over REST**, and the two halves agree on exactly
sixteen `car_*` meta fields plus two routes the theme owns:

- `das/v1/leads` — the enquiry list, `edit_posts`, oldest first (§20.4)
- `das/v1/vehicle-photos` — a vehicle's public photo addresses by VIN, which is what makes
  Instagram posting possible at all (§25)

**`inc/privacy-boundary.php` is the wall**, and it is the one file to be careful with: only
the sixteen public fields are registered with REST, anything else is discarded before the
database, and any financial-looking meta key is blocked from any route and logged. §6 is the
whole argument. **A change that widens it is a change that leaks a dealership's costs.**

---

## Two rules this theme has already paid for

**Back the file up in the same call that edits it.** Not before, not after. Novamira is a
WordPress plugin, so a fatal in the theme takes the tool that would fix it, and there is no
second way in (§12).

**Verify on the rendered page, never on the source.** The theme source answers what it was
told to say; only fetching the page proves what a customer gets. §11 records the five
"differences" that were hunted for and did not exist, and the opcache that served old
bytecode over a correct file for an hour.

---

## Swiss dealerships work the other way round

The Alaska install is *app → website*: the desktop app is the source of truth and pushes
vehicles up (§9). **A Swiss dealership lists on AutoScout24 first**, and the website is fed
*from* there. So the inventory direction reverses, and `das/v1/vehicle-photos`,
`inc/privacy-boundary.php` and the whole push target are not what a Swiss site needs.

Nothing for that is built, and the mechanism — partner API, dealer feed export, or neither —
**has not been measured.** Do not guess at it: three guesses at one third-party URL already
cost this project an evening each (§23).
