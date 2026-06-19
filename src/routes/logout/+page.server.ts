import { redirect } from "@sveltejs/kit";
import type { Actions, PageServerLoad } from "./$types";
import { deleteSession, SESSION_COOKIE } from "$lib/server/auth";

export const load: PageServerLoad = async () => {
  throw redirect(303, "/");
};

export const actions: Actions = {
  default: async ({ cookies }) => {
    const token = cookies.get(SESSION_COOKIE);
    if (token) await deleteSession(token);
    cookies.delete(SESSION_COOKIE, { path: "/" });
    throw redirect(303, "/login");
  },
};
