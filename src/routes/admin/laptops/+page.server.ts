import { fail } from "@sveltejs/kit";
import type { Actions, PageServerLoad } from "./$types";
import {
  listLaptops,
  getActiveScoringConfig,
  deleteLaptop,
} from "$lib/server/queries";
import { toSpecs } from "$lib/laptop";
import { score } from "$lib/scoring";

export const load: PageServerLoad = async () => {
  const [rows, config] = await Promise.all([
    listLaptops(),
    getActiveScoringConfig(),
  ]);
  return {
    items: rows
      .map((laptop) => ({ laptop, result: score(toSpecs(laptop), config) }))
      .sort((a, b) => b.result.total - a.result.total),
  };
};

export const actions: Actions = {
  delete: async ({ request }) => {
    const id = Number((await request.formData()).get("id"));
    if (!Number.isInteger(id) || id <= 0)
      return fail(400, { error: "ID tidak valid." });
    await deleteLaptop(id);
    return { success: "Laptop dihapus." };
  },
};
