# The Swiss business side

**Moved here from `Tiff-Cardealer-Manager` on 15 September 2026**, on Gianni's own decision.
That repo is the American desktop app (Alaska, English, local-first) and its own theme; this
repo already holds the Swiss theme. None of what is in this folder is American, and it had
been sitting in the American app's repo since the "Swiss SaaS expansion" session added it,
which is exactly the mixing Gianni flagged: two customers' commercial material — pricing,
hosting operations, client offers — inside the source repository of a different product.

**Nothing in here was rewritten for the move.** Every `§` reference to `CLAUDE.md` inside
these files still points at `Tiff-Cardealer-Manager`'s handover document — that is where the
measurements they cite (module counts, IPC channels, the sync boundary) were taken, and it
remains correct even though the files describing the Swiss business now live somewhere else.

| File | What it is |
|---|---|
| `SCHWEIZ-SAAS.md` | Assessment and architecture proposal for a future Swiss multi-tenant cloud product — nothing of it is built |
| `BETRIEB-UND-HOSTING.md` | The hosting/operations business model |
| `OFFERTE-VORLAGE.md` | The offer template |
| `offerte/` | Two rendered client offers — BIT Automobile and "aino" |
| `BIT-AUTOMOBILE-DOSSIER.md` | Everything known about the Swiss prospect: company, domain, DNS, mail hosting, logo, the open questions, and the access dispute with their previous developer |
| `dossier/` | The same dossier rendered — HTML (with a checkable question list) and PDF |

**One caution about `BIT-AUTOMOBILE-DOSSIER.md`.** Every line in it carries its own origin,
and that is not decoration: what was *measured* (public DNS, the company register) and what
Sabit *said* are two different kinds of fact. A domain move planned on the wrong one takes his
business mail down with it.
