# LaptopVal v2 — Task List

## Phase 0: Docs

- [x] Write `docs/PRD.md`
- [x] Write `docs/SPECS.md`
- [x] Write `docs/TASKS.md`
- [x] Write `README.md`
- [x] Delete `docs/BLUEPRINT.md` (PHP-era, superseded)

## Phase 1: Foundation

- [x] Scaffold SvelteKit + Bun project
- [x] Configure Drizzle + Postgres connection
- [x] Write schema (`users`, `brands`, `laptops`, `use_cases`, `scoring_config`, `evaluations`)
- [x] Migrations + seed data (brands, use cases, default scoring config, admin user)
- [x] Implement scoring engine (`src/lib/scoring/index.ts`) — pure function, no DB/framework
- [x] Vitest unit tests for scoring engine (factor boundaries, price deviation, regional
      adjustment, verdict band edges)
- [x] Docker Compose setup (Postgres)

## Phase 2: Public

- [ ] Catalog page (`/`) — listing, use-case filter, location filter, search, sort
- [ ] Detail page (`/laptops/[id]`) — specs, per-factor breakdown, model price range
- [ ] Self-service evaluator (`/evaluate`) — spec input form, live verdict + breakdown

## Phase 3: Admin

- [ ] Auth (`/login`, `/logout`, session middleware)
- [ ] Admin dashboard (`/admin`) — totals, avg score per brand, top deals
- [ ] Laptop CRUD (`/admin/laptops`) — list, create/edit form with live score preview
- [ ] Brand management (`/admin/brands`)
- [ ] Use-case management (`/admin/use-cases`)
- [ ] Scoring config editor (`/admin/scoring`) — weights, thresholds, verdict bands

## Phase 4: Features

- [ ] Compare (`/compare`) — side-by-side specs/scores/breakdown for 2–4 laptops, shareable URL
- [ ] Location filter + model price range on detail page
- [ ] Tier list maker (`/tier-list`) — drag-drop S/A/B/C/D, shareable URL state

## Future

- [ ] Public REST/JSON API for laptops + specs
- [ ] Benchmark integration (pluggable CPU/GPU tier source)
