# LaptopVal

A transparent, data-driven evaluator for used laptop deals.

## Problem

Buying a used laptop is opaque. Prices are arbitrary, specs vary, and there is no reliable way
to know whether a deal is genuinely good value. LaptopVal fixes this with a deterministic
scoring engine that gives every listing a clear verdict and a per-factor breakdown.

## How It Works

1. **Input specs** — enter CPU tier, RAM, storage, condition, warranty, age, asking price, and
   region (or browse the admin-curated catalog).
2. **Engine scores** — a pure TypeScript scoring function computes a weighted score across all
   factors. Price is evaluated against a derived _fair price_ with a regional adjustment.
3. **Verdict** — `Great Deal`, `Fair`, `Overpriced`, or `Avoid` — with a breakdown showing
   exactly why each factor contributed.

## Tech Stack

| Layer      | Choice                           |
| ---------- | -------------------------------- |
| Runtime    | Bun                              |
| Framework  | SvelteKit                        |
| Database   | PostgreSQL (Neon free tier ok)   |
| ORM        | Drizzle                          |
| Validation | Zod (shared client/server)       |
| Testing    | Vitest (unit) + Playwright (e2e) |

## Local Dev

```bash
# Install dependencies
bun install

# Start Postgres
docker compose up -d db

# Push schema and seed
bun run db:push
bun run db:seed

# Start dev server
bun run dev
```

App: http://localhost:5173
Admin login: seeded from `ADMIN_USERNAME` / `ADMIN_PASSWORD` env vars.

## Features

- **Catalog** — filter by use case + location, search, sort by price or score
- **Detail page** — per-factor score breakdown + price range for the same model
- **Self-service evaluator** — get a verdict without an account
- **Compare** — side-by-side specs/scores for 2–4 laptops, shareable URL
- **Tier list maker** — drag-drop S/A/B/C/D board, shareable via URL
- **Admin** — manage brands, laptops, use cases, and scoring configuration

## Folder Structure

```
src/
  lib/
    scoring/        # Pure scoring engine (no DB, no framework)
    server/
      db/           # Drizzle schema + query helpers
      auth/         # Session + password handling
  routes/           # SvelteKit file-based routes
docs/
  PRD.md            # Product requirements
  SPECS.md          # Technical specification
  TASKS.md          # Phase checklist
```

## Docs

- [Product Requirements](docs/PRD.md)
- [Technical Specification](docs/SPECS.md)
- [Task List](docs/TASKS.md)
