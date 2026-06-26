import { eq, lt } from "drizzle-orm";
import { verify as argon2Verify } from "argon2";
import { db } from "./db";
import { sessions, users } from "./db/schema";

export const SESSION_COOKIE = "session";
const SESSION_TTL_MS = 1000 * 60 * 60 * 24 * 30; // 30 days

export type SessionUser = { id: number; username: string };

function toHex(bytes: Uint8Array): string {
  return [...bytes].map((b) => b.toString(16).padStart(2, "0")).join("");
}

export function generateToken(): string {
  return toHex(crypto.getRandomValues(new Uint8Array(32)));
}

export async function hashToken(token: string): Promise<string> {
  const digest = await crypto.subtle.digest(
    "SHA-256",
    new TextEncoder().encode(token),
  );
  return toHex(new Uint8Array(digest));
}

/** Remove sessions past their expiry. Without this, sessions that are never
 *  revisited (so validateSession never prunes them) accumulate indefinitely. */
export async function deleteExpiredSessions(): Promise<void> {
  await db.delete(sessions).where(lt(sessions.expiresAt, new Date()));
}

export async function createSession(
  userId: number,
): Promise<{ token: string; expiresAt: Date }> {
  // Opportunistic cleanup; cheap and naturally rate-limited by login frequency.
  await deleteExpiredSessions();
  const token = generateToken();
  const id = await hashToken(token);
  const expiresAt = new Date(Date.now() + SESSION_TTL_MS);
  await db.insert(sessions).values({ id, userId, expiresAt });
  return { token, expiresAt };
}

export async function validateSession(
  token: string,
): Promise<SessionUser | null> {
  const id = await hashToken(token);
  const [row] = await db
    .select({
      userId: sessions.userId,
      username: users.username,
      expiresAt: sessions.expiresAt,
    })
    .from(sessions)
    .innerJoin(users, eq(sessions.userId, users.id))
    .where(eq(sessions.id, id))
    .limit(1);
  if (!row) return null;
  if (row.expiresAt.getTime() < Date.now()) {
    await db.delete(sessions).where(eq(sessions.id, id));
    return null;
  }
  return { id: row.userId, username: row.username };
}

export async function deleteSession(token: string): Promise<void> {
  const id = await hashToken(token);
  await db.delete(sessions).where(eq(sessions.id, id));
}

export async function verifyLogin(
  username: string,
  password: string,
): Promise<SessionUser | null> {
  const [u] = await db
    .select()
    .from(users)
    .where(eq(users.username, username))
    .limit(1);
  if (!u) return null;
  const ok = await argon2Verify(u.passwordHash, password);
  return ok ? { id: u.id, username: u.username } : null;
}
