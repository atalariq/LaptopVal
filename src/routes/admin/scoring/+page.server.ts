import { fail } from "@sveltejs/kit";
import type { Actions, PageServerLoad } from "./$types";
import {
  getActiveScoringConfig,
  updateActiveScoringConfig,
} from "$lib/server/queries";
import { scoringConfigSchema, parseWith } from "$lib/schemas";

export const load: PageServerLoad = async () => ({
  config: await getActiveScoringConfig(),
});

function parseJson(value: FormDataEntryValue | null): unknown {
  try {
    return JSON.parse(String(value ?? ""));
  } catch {
    return Symbol("invalid");
  }
}

export const actions: Actions = {
  default: async ({ request }) => {
    const f = await request.formData();
    const jsonFields = {
      cpu: parseJson(f.get("cpu")),
      gpu: parseJson(f.get("gpu")),
      ram: parseJson(f.get("ram")),
      storage: parseJson(f.get("storage")),
      agePenalty: parseJson(f.get("agePenalty")),
      bands: parseJson(f.get("priceBands")),
      regionMultipliers: parseJson(f.get("regionMultipliers")),
      verdictBands: parseJson(f.get("verdictBands")),
    };
    if (Object.values(jsonFields).some((v) => typeof v === "symbol")) {
      return fail(400, { error: "Ada field JSON yang tidak valid." });
    }
    const candidate = {
      cpu: jsonFields.cpu,
      gpu: jsonFields.gpu,
      conditionPerLevel: Number(f.get("conditionPerLevel")),
      warrantyBonus: Number(f.get("warrantyBonus")),
      ram: jsonFields.ram,
      storage: jsonFields.storage,
      agePenalty: jsonFields.agePenalty,
      price: {
        idrPerQualityPoint: Number(f.get("idrPerQualityPoint")),
        bands: jsonFields.bands,
        regionMultipliers: jsonFields.regionMultipliers,
      },
      verdictBands: jsonFields.verdictBands,
    };
    const p = parseWith(scoringConfigSchema, candidate);
    if (!p.ok)
      return fail(400, {
        error: "Konfigurasi tidak valid: " + Object.values(p.errors).join(", "),
      });
    await updateActiveScoringConfig(p.data);
    return { success: "Konfigurasi scoring disimpan." };
  },
};
