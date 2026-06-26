import { fail } from "@sveltejs/kit";
import type { Actions, PageServerLoad } from "./$types";
import {
  getCpus,
  createCpu,
  updateCpu,
  deleteCpu,
  isFkViolation,
} from "$lib/server/queries";
import { cpuSchema, parseWith } from "$lib/schemas";

export const load: PageServerLoad = async () => ({ cpus: await getCpus() });

export const actions: Actions = {
  create: async ({ request }) => {
    const p = parseWith(
      cpuSchema,
      Object.fromEntries(await request.formData()),
    );
    if (!p.ok) return fail(400, { errors: p.errors, action: "create" });
    await createCpu(p.data);
    return { success: "CPU ditambahkan." };
  },
  update: async ({ request }) => {
    const form = Object.fromEntries(await request.formData());
    const id = Number(form.id);
    if (!Number.isInteger(id) || id <= 0)
      return fail(400, { error: "ID tidak valid." });
    const p = parseWith(cpuSchema, form);
    if (!p.ok) return fail(400, { errors: p.errors, action: "update", id });
    await updateCpu(id, p.data);
    return { success: "CPU diperbarui." };
  },
  delete: async ({ request }) => {
    const id = Number((await request.formData()).get("id"));
    if (!Number.isInteger(id) || id <= 0)
      return fail(400, { error: "ID tidak valid." });
    try {
      await deleteCpu(id);
    } catch (e) {
      if (isFkViolation(e))
        return fail(409, { error: "CPU masih dipakai oleh data laptop." });
      throw e;
    }
    return { success: "CPU dihapus." };
  },
};
