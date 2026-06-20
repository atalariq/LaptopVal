import { fail, redirect } from "@sveltejs/kit";
import type { Actions, PageServerLoad } from "./$types";
import {
  getBrands,
  getActiveScoringConfig,
  createLaptop,
} from "$lib/server/queries";
import { laptopSchema, parseWith } from "$lib/schemas";

export const load: PageServerLoad = async () => ({
  brands: await getBrands(),
  config: await getActiveScoringConfig(),
});

export const actions: Actions = {
  default: async ({ request }) => {
    const p = parseWith(
      laptopSchema,
      Object.fromEntries(await request.formData()),
    );
    if (!p.ok) return fail(400, { errors: p.errors });
    await createLaptop(p.data);
    throw redirect(303, "/admin/laptops");
  },
};
