import { redirect, type Handle } from "@sveltejs/kit";
import { validateSession, SESSION_COOKIE } from "$lib/server/auth";

export const handle: Handle = async ({ event, resolve }) => {
  const token = event.cookies.get(SESSION_COOKIE);
  event.locals.user = token ? await validateSession(token) : null;

  // Guard the whole /admin surface here, not in +layout.server.ts: layout
  // loads do not run for form actions, so a layout-only guard leaves every
  // admin mutation (POST) reachable by unauthenticated requests.
  const { pathname } = event.url;
  if (
    (pathname === "/admin" || pathname.startsWith("/admin/")) &&
    !event.locals.user
  ) {
    throw redirect(303, "/login");
  }

  return resolve(event);
};
