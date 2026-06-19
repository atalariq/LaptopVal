import { z } from "zod";
import type { LaptopSpecs } from "$lib/scoring";

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
