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

- [x] Catalog page (`/`) — listing, use-case filter, location filter, search, sort
- [x] Detail page (`/laptops/[id]`) — specs, per-factor breakdown, model price range
- [x] Self-service evaluator (`/evaluate`) — spec input form, live verdict + breakdown

## Phase 3: Admin

- [x] Auth (`/login`, `/logout`, session middleware)
- [x] Admin dashboard (`/admin`) — totals, avg score per brand, top deals
- [x] Laptop CRUD (`/admin/laptops`) — list, create/edit form with live score preview
- [x] Brand management (`/admin/brands`)
- [x] Use-case management (`/admin/use-cases`)
- [x] Scoring config editor (`/admin/scoring`) — weights, thresholds, verdict bands

## Phase 4: Features

- [x] Compare (`/compare`) — side-by-side specs/scores/breakdown for 2–4 laptops, shareable URL
- [x] Location filter + model price range on detail page
- [x] Tier list maker (`/tier-list`) — drag-drop S/A/B/C/D, shareable URL state

## Phase 5: Polish

- [x] Design system integration (Wana tokens via Tailwind `@theme`, light/dark)
- [x] E2E tests (Playwright) — public flows + admin auth (`bun run test:e2e`)
- [x] Resolve `state_referenced_locally` warnings in `LaptopForm`, `scoring`, `tier-list`

## Future

- [ ] Public REST/JSON API for laptops + specs
- [ ] Benchmark integration (pluggable CPU/GPU tier source)
