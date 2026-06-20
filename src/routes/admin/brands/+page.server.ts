import { fail } from "@sveltejs/kit";
import type { Actions, PageServerLoad } from "./$types";
import {
  getBrands,
  createBrand,
  updateBrand,
  deleteBrand,
  isFkViolation,
} from "$lib/server/queries";
import { brandSchema, parseWith } from "$lib/schemas";

export const load: PageServerLoad = async () => ({ brands: await getBrands() });

export const actions: Actions = {
  create: async ({ request }) => {
    const p = parseWith(
      brandSchema,
      Object.fromEntries(await request.formData()),
    );
    if (!p.ok) return fail(400, { errors: p.errors, action: "create" });
    await createBrand(p.data);
    return { success: "Brand ditambahkan." };
  },
  update: async ({ request }) => {
    const form = Object.fromEntries(await request.formData());
    const id = Number(form.id);
    const p = parseWith(brandSchema, form);
    if (!p.ok) return fail(400, { errors: p.errors, action: "update", id });
    await updateBrand(id, p.data);
    return { success: "Brand diperbarui." };
  },
  delete: async ({ request }) => {
    const id = Number((await request.formData()).get("id"));
    try {
      await deleteBrand(id);
    } catch (e) {
      if (isFkViolation(e))
        return fail(409, { error: "Brand masih dipakai oleh data laptop." });
      throw e;
    }
    return { success: "Brand dihapus." };
  },
};
