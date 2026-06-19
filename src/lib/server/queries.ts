import { and, eq, ilike, or, gte, sql } from "drizzle-orm";
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
        ilike(laptops.model, `%${opts.search}%`),
        ilike(brands.name, `%${opts.search}%`),
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

export function getUseCases() {
  return db.select().from(useCases).orderBy(useCases.name);
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
