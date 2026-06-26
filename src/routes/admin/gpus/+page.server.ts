import { fail } from "@sveltejs/kit";
import type { Actions, PageServerLoad } from "./$types";
import {
  getGpus,
  createGpu,
  updateGpu,
  deleteGpu,
  isFkViolation,
} from "$lib/server/queries";
import { gpuSchema, parseWith } from "$lib/schemas";

export const load: PageServerLoad = async () => ({ gpus: await getGpus() });

export const actions: Actions = {
  create: async ({ request }) => {
    const p = parseWith(
      gpuSchema,
      Object.fromEntries(await request.formData()),
    );
    if (!p.ok) return fail(400, { errors: p.errors, action: "create" });
    await createGpu(p.data);
    return { success: "GPU ditambahkan." };
  },
  update: async ({ request }) => {
    const form = Object.fromEntries(await request.formData());
    const id = Number(form.id);
    if (!Number.isInteger(id) || id <= 0)
      return fail(400, { error: "ID tidak valid." });
    const p = parseWith(gpuSchema, form);
    if (!p.ok) return fail(400, { errors: p.errors, action: "update", id });
    await updateGpu(id, p.data);
    return { success: "GPU diperbarui." };
  },
  delete: async ({ request }) => {
    const id = Number((await request.formData()).get("id"));
    if (!Number.isInteger(id) || id <= 0)
      return fail(400, { error: "ID tidak valid." });
    try {
      await deleteGpu(id);
    } catch (e) {
      if (isFkViolation(e))
        return fail(409, { error: "GPU masih dipakai oleh data laptop." });
      throw e;
    }
    return { success: "GPU dihapus." };
  },
};
