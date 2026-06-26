# LaptopVal v2 — Technical Specification

## Stack

| Layer      | Choice                                            | Why                                                      |
| ---------- | ------------------------------------------------- | -------------------------------------------------------- |
| Runtime    | **Bun**                                           | Fast, built-in tooling, one language across stack        |
| Framework  | **SvelteKit**                                     | SSR + progressive enhancement, least boilerplate, top DX |
| Database   | **PostgreSQL** (Neon free tier ok)                | Production standard, rich features (JSONB, FTS)          |
| ORM        | **Drizzle**                                       | Typed, parameterized queries, first-class migrations     |
| Validation | **Zod** (shared client/server)                    | Single source of truth for input shapes                  |
| Auth       | Session cookie (httpOnly, signed) + argon2/bcrypt | Simple, secure; admin seeded from env                    |
| Testing    | **Vitest** (unit) + Playwright (e2e, later)       | Scoring engine is pure → easy unit coverage              |
| Deploy     | Bun adapter / Docker + managed Postgres           | Single deployable + managed DB                           |

## Architecture

- **SvelteKit full-stack**: server load functions + form actions for mutations.
  Works without JS; enhanced (live preview, compare, tier-list) with JS.
- **Scoring engine** is a **pure TypeScript module** (`src/lib/scoring/`) with no DB or
  framework dependency. Both the self-service evaluator and the catalog call it. This
  isolation makes it deterministic and trivially unit-testable.
- All DB access through Drizzle (parameterized). Svelte auto-escapes output.
- Secrets via environment variables.

### Module Boundaries

- `src/lib/scoring/` — pure engine: `score(specs, config) → { total, verdict, breakdown[] }`.
  Depends on nothing. Testable in isolation.
- `src/lib/server/db/` — Drizzle schema + query helpers. The only module touching Postgres.
- `src/lib/server/auth/` — session + password handling.
- Routes consume the above through typed interfaces; no business logic in components.

## Data Model (Drizzle / Postgres)

- **users** — `id`, `username` (unique), `password_hash`, `created_at`. Admin accounts only.
- **brands** — `id`, `name` (unique), `notes`.
- **cpus** — `id`, `name` (unique), `benchmark` (PassMark CPU Mark), `vendor` (Intel/AMD/Apple). Admin-managed.
- **gpus** — `id`, `name` (unique), `benchmark` (PassMark G3D Mark), `kind` (integrated/discrete). Admin-managed.
- **laptops** — `id`, `brand_id` (fk), `model`, `release_year`, `cpu_id` (fk → cpus), `gpu_id`
  (fk → gpus, nullable), `ram_gb`, `storage_gb`, `condition`, `has_warranty`, `price`,
  `location` (region), `image_path`, `source_url`, `created_by` (fk), `created_at`, `updated_at`.
- **use_cases** — `id`, `name`, `min_ram_gb`, `min_cpu_benchmark`, `min_gpu_benchmark`, `min_storage`.
- **scoring_config** — data-driven weights/thresholds the admin can edit. Shape: `factor`,
  rule params (thresholds → points), `weight`, `active`. The engine reads this; changing it
  recomputes cached evaluations.
- **evaluations** — cached `value_score`, `verdict`, and per-factor `breakdown` (JSONB) for
  catalog laptops. Recomputed on laptop write **and** on `scoring_config` change.

**Price range** (location feature) derives from grouping listings by model + region via a
query/view — no separate table needed for MVP.

**Tier lists**: MVP keeps tier state in the URL (shareable, no persistence). A `tier_lists`
table is deferred until named/saved lists are needed.

## Scoring Engine

Contract: `score(specs, config) → { total, verdict, breakdown[] }`

Factors (config-driven): **CPU, GPU, RAM, storage, condition, warranty, age**. Each factor's
thresholds → points come from `scoring_config`. CPU and GPU scores are derived from benchmark
values (PassMark) looked up from the `cpus`/`gpus` tables.

**Real price logic**: the engine derives a _fair price_ from the spec-driven quality, then
`price_score` reflects actual-vs-fair deviation — under fair price → bonus, over fair price →
penalty — so price genuinely moves the verdict.

**Regional adjustment**: the fair-price estimate is shifted by `location` so a higher price
in a more expensive region is not unfairly penalized.

**Verdict bands** (configurable):

| Label        | Meaning                              |
| ------------ | ------------------------------------ |
| `Great Deal` | Score well above fair threshold      |
| `Fair`       | Score near fair threshold            |
| `Overpriced` | Spec-quality fine, price too high    |
| `Avoid`      | Low spec quality regardless of price |

Fully deterministic; covered by Vitest unit tests (factor boundaries, price over/under,
regional shift, verdict band edges).

## Routes

### Public

| Route           | Description                                                                |
| --------------- | -------------------------------------------------------------------------- |
| `/`             | Catalog: filter by use case + location, search, sort                       |
| `/laptops/[id]` | Detail: per-factor score breakdown + price range for the model             |
| `/evaluate`     | Self-service evaluator: enter specs + price + location → verdict, no login |
| `/compare`      | Side-by-side specs/scores/breakdown for 2–4 laptops; shareable URL         |
| `/tier-list`    | Drag-drop S/A/B/C/D board, auto-seeded from score; shareable via URL       |

### Auth

| Route     | Description          |
| --------- | -------------------- |
| `/login`  | Admin login form     |
| `/logout` | Session invalidation |

### Admin (guarded)

| Route              | Description                                            |
| ------------------ | ------------------------------------------------------ |
| `/admin`           | Dashboard: totals, avg score per brand, top deals      |
| `/admin/laptops`   | Laptop list + create/edit form with live score preview |
| `/admin/brands`    | Brand management                                       |
| `/admin/cpus`      | CPU dataset management (name, PassMark benchmark)      |
| `/admin/gpus`      | GPU dataset management (name, PassMark benchmark)      |
| `/admin/use-cases` | Use-case management                                    |
| `/admin/scoring`   | Edit scoring weights, thresholds, verdict bands        |

## Cross-Cutting Concerns

- **Validation**: Zod schemas shared client + server. Single source of truth for input shapes.
- **Auth / CSRF**: SvelteKit form actions + same-site httpOnly session cookie.
- **Security**: Drizzle parameterizes all queries; Svelte auto-escapes; secrets in env vars.
- **Images**: file upload with URL fallback for catalog laptops.
- **Testing**: Vitest for scoring engine + utils; Playwright e2e added in later phases.
- **Deploy**: Bun adapter or Docker + managed Postgres (Neon free tier compatible).

## Global Constraints

- No raw string concatenation into SQL — Drizzle parameterized queries only.
- No business logic in Svelte components — goes in server load functions or the scoring module.
- Scoring engine (`src/lib/scoring/`) must remain framework-free and DB-free.
- All secrets (DB URL, session secret, admin seed credentials) via environment variables.
- `scoring_config` changes must trigger recomputation of cached `evaluations`.
