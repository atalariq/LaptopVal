import { describe, it, expect } from "vitest";
import { generateToken, hashToken } from "./auth";

describe("generateToken", () => {
  it("returns a 64-char hex string", () => {
    expect(generateToken()).toMatch(/^[0-9a-f]{64}$/);
  });
  it("returns a different token each call", () => {
    expect(generateToken()).not.toBe(generateToken());
  });
});

describe("hashToken", () => {
  it("is deterministic and 64-char hex", async () => {
    const a = await hashToken("abc");
    const b = await hashToken("abc");
    expect(a).toBe(b);
    expect(a).toMatch(/^[0-9a-f]{64}$/);
  });
  it("differs for different inputs", async () => {
    expect(await hashToken("abc")).not.toBe(await hashToken("abd"));
  });
});
