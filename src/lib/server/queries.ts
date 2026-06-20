import { and, eq, ilike, inArray, or, gte, sql } from "drizzle-orm";
import { db } from "./db";
import { laptops, brands, useCases, scoringConfig } from "./db/schema";
import { DEFAULT_SCORING_CONFIG, type ScoringConfig } from "$lib/scoring";
import type { LaptopRow } from "$lib/laptop";

export async function getActiveScoringConfig(): Promise<ScoringConfig> {
  const [row] = await db
    .select()
    .from(scoringConfig)
    .where(eq(scoringConfig.active, true))
    .limit(1);
  return (row?.config as ScoringConfig) ?? DEFAULT_SCORING_CONFIG;
}

const SELECT = {
  id: laptops.id,
  brand: brands.name,
  brandId: laptops.brandId,
  model: laptops.model,
  releaseYear: laptops.releaseYear,
  cpuTier: laptops.cpuTier,
  ramGb: laptops.ramGb,
  storageGb: laptops.storageGb,
  condition: laptops.condition,
  hasWarranty: laptops.hasWarranty,
  price: laptops.price,
  location: laptops.location,
  imagePath: laptops.imagePath,
  sourceUrl: laptops.sourceUrl,
};

export interface ListOpts {
  useCaseId?: number;
  location?: string;
  search?: string;
}

export async function listLaptops(opts: ListOpts = {}): Promise<LaptopRow[]> {
  const conds = [];
  if (opts.location) conds.push(eq(laptops.location, opts.location as never));
  if (opts.search)
    conds.push(
      or(
        ilike(laptops.model, `%${opts.search.replace(/[%_\\]/g, "\\$&")}%`),
        ilike(brands.name, `%${opts.search.replace(/[%_\\]/g, "\\$&")}%`),
      ),
    );
  if (opts.useCaseId) {
    const [uc] = await db
      .select()
      .from(useCases)
      .where(eq(useCases.id, opts.useCaseId))
      .limit(1);
    if (uc) {
      conds.push(gte(laptops.ramGb, uc.minRamGb));
      conds.push(gte(laptops.cpuTier, uc.minCpuTier));
      conds.push(gte(laptops.storageGb, uc.minStorage));
    }
  }
  return db
    .select(SELECT)
    .from(laptops)
    .innerJoin(brands, eq(laptops.brandId, brands.id))
    .where(conds.length ? and(...conds) : undefined) as Promise<LaptopRow[]>;
}

export async function getLaptopById(id: number): Promise<LaptopRow | null> {
  const [row] = await db
    .select(SELECT)
    .from(laptops)
    .innerJoin(brands, eq(laptops.brandId, brands.id))
    .where(eq(laptops.id, id))
    .limit(1);
  return (row as LaptopRow) ?? null;
}

export async function getLaptopsByIds(ids: number[]): Promise<LaptopRow[]> {
  if (ids.length === 0) return [];
  return db
    .select(SELECT)
    .from(laptops)
    .innerJoin(brands, eq(laptops.brandId, brands.id))
    .where(inArray(laptops.id, ids)) as Promise<LaptopRow[]>;
}

export function getUseCases() {
  return db.select().from(useCases).orderBy(useCases.name);
}

export function getBrands() {
  return db.select().from(brands).orderBy(brands.name);
}

export async function priceRangeForModel(model: string, brandId: number) {
  const [r] = await db
    .select({
      min: sql<number>`min(${laptops.price})`,
      max: sql<number>`max(${laptops.price})`,
      count: sql<number>`count(*)::int`,
    })
    .from(laptops)
    .where(and(eq(laptops.model, model), eq(laptops.brandId, brandId)));
  return { min: r?.min ?? 0, max: r?.max ?? 0, count: r?.count ?? 0 };
}

/** Postgres foreign_key_violation (e.g. deleting a brand still used by laptops). */
export function isFkViolation(e: unknown): boolean {
  return (
    typeof e === "object" &&
    e !== null &&
    "code" in e &&
    (e as { code?: string }).code === "23503"
  );
}

type BrandInput = { name: string; notes: string | null };
export const createBrand = (d: BrandInput) => db.insert(brands).values(d);
export const updateBrand = (id: number, d: BrandInput) =>
  db.update(brands).set(d).where(eq(brands.id, id));
export const deleteBrand = (id: number) =>
  db.delete(brands).where(eq(brands.id, id));

type UseCaseInput = {
  name: string;
  minRamGb: number;
  minCpuTier: number;
  minStorage: number;
};
export const createUseCase = (d: UseCaseInput) => db.insert(useCases).values(d);
export const updateUseCase = (id: number, d: UseCaseInput) =>
  db.update(useCases).set(d).where(eq(useCases.id, id));
export const deleteUseCase = (id: number) =>
  db.delete(useCases).where(eq(useCases.id, id));

type LaptopInput = {
  brandId: number;
  model: string;
  releaseYear: number;
  cpuTier: number;
  ramGb: number;
  storageGb: number;
  condition: number;
  hasWarranty: boolean;
  price: number;
  location: string;
  imagePath: string | null;
  sourceUrl: string | null;
};
// location is `string` in LaptopInput because format.ts (client-accessible) can't import
// the server-only pgEnum type. Zod validates the value via REGION_KEYS before reaching here.
export const createLaptop = (d: LaptopInput) =>
  db.insert(laptops).values({ ...d, location: d.location as never });
export const updateLaptop = (id: number, d: LaptopInput) =>
  db
    .update(laptops)
    .set({ ...d, location: d.location as never, updatedAt: new Date() })
    .where(eq(laptops.id, id));
export const deleteLaptop = (id: number) =>
  db.delete(laptops).where(eq(laptops.id, id));

export async function updateActiveScoringConfig(config: ScoringConfig) {
  const [active] = await db
    .select({ id: scoringConfig.id })
    .from(scoringConfig)
    .where(eq(scoringConfig.active, true))
    .limit(1);
  if (active) {
    await db
      .update(scoringConfig)
      .set({ config, updatedAt: new Date() })
      .where(eq(scoringConfig.id, active.id));
  } else {
    await db.insert(scoringConfig).values({ config, active: true });
  }
}
