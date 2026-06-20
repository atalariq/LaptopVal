import { error, fail, redirect } from "@sveltejs/kit";
import type { Actions, PageServerLoad } from "./$types";
import {
  getBrands,
  getActiveScoringConfig,
  getLaptopById,
  updateLaptop,
} from "$lib/server/queries";
import { laptopSchema, parseWith } from "$lib/schemas";

export const load: PageServerLoad = async ({ params }) => {
  const id = Number(params.id);
  if (!Number.isInteger(id) || id <= 0)
    throw error(404, "Laptop tidak ditemukan");
  const laptop = await getLaptopById(id);
  if (!laptop) throw error(404, "Laptop tidak ditemukan");
  return {
    laptop,
    brands: await getBrands(),
    config: await getActiveScoringConfig(),
  };
};

export const actions: Actions = {
  default: async ({ request, params }) => {
    const id = Number(params.id);
    const p = parseWith(
      laptopSchema,
      Object.fromEntries(await request.formData()),
    );
    if (!p.ok) return fail(400, { errors: p.errors });
    await updateLaptop(id, p.data);
    throw redirect(303, "/admin/laptops");
  },
};
