import { fail } from "@sveltejs/kit";
import type { Actions, PageServerLoad } from "./$types";
import {
  getUseCases,
  createUseCase,
  updateUseCase,
  deleteUseCase,
} from "$lib/server/queries";
import { useCaseSchema, parseWith } from "$lib/schemas";

export const load: PageServerLoad = async () => ({
  useCases: await getUseCases(),
});

export const actions: Actions = {
  create: async ({ request }) => {
    const p = parseWith(
      useCaseSchema,
      Object.fromEntries(await request.formData()),
    );
    if (!p.ok) return fail(400, { errors: p.errors, action: "create" });
    await createUseCase(p.data);
    return { success: "Use case ditambahkan." };
  },
  update: async ({ request }) => {
    const form = Object.fromEntries(await request.formData());
    const id = Number(form.id);
    const p = parseWith(useCaseSchema, form);
    if (!p.ok) return fail(400, { errors: p.errors, action: "update", id });
    await updateUseCase(id, p.data);
    return { success: "Use case diperbarui." };
  },
  delete: async ({ request }) => {
    await deleteUseCase(Number((await request.formData()).get("id")));
    return { success: "Use case dihapus." };
  },
};
