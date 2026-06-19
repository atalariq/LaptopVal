import { fail, redirect } from "@sveltejs/kit";
import { dev } from "$app/environment";
import type { Actions, PageServerLoad } from "./$types";
import { verifyLogin, createSession, SESSION_COOKIE } from "$lib/server/auth";

export const load: PageServerLoad = async ({ locals }) => {
  if (locals.user) throw redirect(303, "/admin");
  return {};
};

export const actions: Actions = {
  default: async ({ request, cookies }) => {
    const form = await request.formData();
    const username = String(form.get("username") ?? "");
    const password = String(form.get("password") ?? "");
    if (!username || !password)
      return fail(400, {
        error: "Username dan password wajib diisi.",
        username,
      });

    const user = await verifyLogin(username, password);
    if (!user)
      return fail(401, { error: "Username atau password salah.", username });

    const { token, expiresAt } = await createSession(user.id);
    cookies.set(SESSION_COOKIE, token, {
      path: "/",
      httpOnly: true,
      sameSite: "lax",
      secure: !dev,
      expires: expiresAt,
    });
    throw redirect(303, "/admin");
  },
};
