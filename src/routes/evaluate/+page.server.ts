import { fail } from "@sveltejs/kit";
import type { Actions, PageServerLoad } from "./$types";
import { parseSpecs } from "$lib/schemas";
import { score } from "$lib/scoring";
import { getActiveScoringConfig, getCpus, getGpus } from "$lib/server/queries";

// Expose the active scoring config so the client live preview matches the
// server's submitted result (instead of falling back to the engine default).
export const load: PageServerLoad = async () => ({
  config: await getActiveScoringConfig(),
  cpus: await getCpus(),
  gpus: await getGpus(),
});

export const actions: Actions = {
  default: async ({ request }) => {
    const form = Object.fromEntries(await request.formData());
    form.hasWarranty = String(
      form.hasWarranty === "on" || form.hasWarranty === "true",
    );
    const parsed = parseSpecs(form);
    if (!parsed.ok) return fail(400, { errors: parsed.errors, values: form });
    const config = await getActiveScoringConfig();
    return { result: score(parsed.specs, config), values: form };
  },
};
