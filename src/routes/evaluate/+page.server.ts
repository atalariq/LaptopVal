import { fail } from "@sveltejs/kit";
import type { Actions } from "./$types";
import { parseSpecs } from "$lib/schemas";
import { score } from "$lib/scoring";
import { getActiveScoringConfig } from "$lib/server/queries";

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
