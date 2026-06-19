# LaptopVal v2 — Product Requirements Document

## Problem

Buying a used laptop is opaque. Sellers list prices arbitrarily; specs vary across listings; and
buyers have no reliable way to compare whether a deal is actually good value for their needs.

## Solution

LaptopVal is a transparent, data-driven laptop deal evaluator. Every listing in the catalog is
scored by a deterministic engine against a configurable set of factors (CPU tier, RAM, storage,
condition, warranty, age, and real price deviation). The score produces a clear verdict:
**Great Deal**, **Fair**, **Overpriced**, or **Avoid** — with a per-factor breakdown so the user
understands exactly why.

## Two Product Modes

| Mode                       | Who                    | What                                                                             |
| -------------------------- | ---------------------- | -------------------------------------------------------------------------------- |
| **Self-service evaluator** | Any visitor (no login) | Enter specs + price + location → instant verdict + breakdown. No account needed. |
| **Admin-curated catalog**  | Admin                  | Maintain a public catalog of evaluated listings. CRUD + scoring config.          |

## Users

- **Anonymous visitor** — browses the catalog, uses filters, views detail pages, runs the
  self-service evaluator, compares laptops, builds tier lists. No account required.
- **Admin** — authenticated user who manages brands, laptops, use cases, and scoring
  configuration.

## Features

### Catalog & Browsing `[v2]`

- Catalog listing with filter by use case, location/region, and free-text search `[v2]`
- Sort by price, score, name `[v2]`
- Laptop detail page with per-factor score breakdown + price range for the same model `[v2]`

### Self-Service Evaluator `[v2]`

- Enter specs + asking price + location → live verdict + breakdown, no login required `[v2]`
- Optional "save to catalog" action for admins `[v2]`

### Compare `[v2]`

- Side-by-side comparison of 2–4 laptops (specs, scores, breakdown) `[v2]`
- Best-in-row highlighting; shareable URL `[v2]`

### Tier List Maker `[v2]`

- Drag-and-drop S/A/B/C/D tier board, auto-seeded from score `[v2]`
- Rearrangeable; shareable via URL state (no persistence required) `[v2]`

### Admin `[v2]`

- Dashboard: totals, average score per brand, top deals `[v2]`
- Laptop CRUD with live score preview on create/edit form `[v2]`
- Brand management `[v2]`
- Use-case management `[v2]`
- Scoring configuration editor (weights, thresholds, verdict bands) `[v2]`

### Future

- Public REST/JSON API for laptops + specs `[future]`
- Pluggable benchmark integration (CPU/GPU tier population from benchmark source) `[future]`

## Out of Scope

- **Public user registration** — admin auth only.
- **Real-time price scraping** — prices are entered manually.
- **AI / LLM integration** — scoring is deterministic and rule-based.
