import { z } from "zod";
import type { LaptopSpecs, ScoringConfig } from "$lib/scoring";
import { REGION_KEYS } from "$lib/format";

export const evaluateSchema = z.object({
  cpuTier: z.coerce.number().int().min(1).max(3),
  ramGb: z.coerce.number().int().min(1).max(256),
  storageGb: z.coerce.number().int().min(1).max(8000),
  condition: z.coerce.number().int().min(1).max(4),
  hasWarranty: z.coerce.boolean(),
  releaseYear: z.coerce.number().int().min(2008).max(2030),
  price: z.coerce.number().int().positive(),
  location: z.string().optional(),
});

export type EvaluateInput = z.infer<typeof evaluateSchema>;

export function parseSpecs(
  data: Record<string, unknown>,
):
  | { ok: true; specs: LaptopSpecs }
  | { ok: false; errors: Record<string, string> } {
  const r = evaluateSchema.safeParse(data);
  if (!r.success) {
    const errors: Record<string, string> = {};
    for (const issue of r.error.issues)
      errors[String(issue.path[0])] = issue.message;
    return { ok: false, errors };
  }
  return { ok: true, specs: r.data as LaptopSpecs };
}

const optionalText = (max: number) =>
  z
    .string()
    .trim()
    .max(max)
    .optional()
    .or(z.literal(""))
    .transform((v) => v || null);

// Same as optionalText but rejects anything that isn't an http(s) URL.
// Stored values are rendered into href/src attributes, where Svelte does NOT
// strip dangerous protocols — a `javascript:` URL would execute on click.
const optionalUrl = (max: number) =>
  optionalText(max).refine(
    (v) => v === null || /^https?:\/\//i.test(v),
    "URL harus diawali http:// atau https://",
  );

export const brandSchema = z.object({
  name: z.string().trim().min(1, "Nama wajib diisi").max(50),
  notes: optionalText(1000),
});

export const useCaseSchema = z.object({
  name: z.string().trim().min(1, "Nama wajib diisi").max(50),
  minRamGb: z.coerce.number().int().min(0).max(256),
  minCpuTier: z.coerce.number().int().min(1).max(3),
  minStorage: z.coerce.number().int().min(0).max(8000),
});

export const laptopSchema = z.object({
  brandId: z.coerce.number().int().positive("Brand wajib dipilih"),
  model: z.string().trim().min(1, "Model wajib diisi").max(100),
  releaseYear: z.coerce.number().int().min(2008).max(2030),
  cpuTier: z.coerce.number().int().min(1).max(3),
  ramGb: z.coerce.number().int().min(1).max(256),
  storageGb: z.coerce.number().int().min(1).max(8000),
  condition: z.coerce.number().int().min(1).max(4),
  hasWarranty: z.coerce.boolean(),
  price: z.coerce.number().int().positive("Harga harus > 0"),
  location: z
    .string()
    .refine((v) => REGION_KEYS.includes(v), "Lokasi tidak valid"),
  imagePath: optionalUrl(255),
  sourceUrl: optionalUrl(255),
});

const band = z.object({ min: z.number(), points: z.number() });
export const scoringConfigSchema: z.ZodType<ScoringConfig> = z.object({
  cpuPerTier: z.number(),
  ram: z.array(band).min(1),
  storage: z.array(band).min(1),
  conditionPerLevel: z.number(),
  warrantyBonus: z.number(),
  agePenalty: z.array(
    z.object({ beforeYear: z.number(), penalty: z.number() }),
  ),
  price: z.object({
    idrPerQualityPoint: z.number().positive(),
    bands: z
      .array(z.object({ maxRatio: z.number(), points: z.number() }))
      .min(1),
    regionMultipliers: z.record(z.string(), z.number()),
  }),
  verdictBands: z
    .array(
      z.object({
        min: z.number(),
        verdict: z.enum(["Great Deal", "Fair", "Overpriced", "Avoid"]),
      }),
    )
    .min(1),
}) as z.ZodType<ScoringConfig>;

export function parseWith<T>(
  schema: z.ZodType<T>,
  data: unknown,
): { ok: true; data: T } | { ok: false; errors: Record<string, string> } {
  const r = schema.safeParse(data);
  if (r.success) return { ok: true, data: r.data };
  const errors: Record<string, string> = {};
  for (const issue of r.error.issues)
    // Join the full path so nested fields (e.g. "price.bands.0.points") keep a
    // distinct key instead of collapsing onto their top-level parent.
    errors[issue.path.length ? issue.path.join(".") : "_"] = issue.message;
  return { ok: false, errors };
}
