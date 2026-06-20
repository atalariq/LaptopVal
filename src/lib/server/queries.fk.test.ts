import { describe, it, expect } from "vitest";
import { isFkViolation } from "./queries";

describe("isFkViolation", () => {
  it("is true for a postgres 23503 error", () => {
    expect(isFkViolation({ code: "23503" })).toBe(true);
  });
  it("is false for other errors / non-objects", () => {
    expect(isFkViolation({ code: "23505" })).toBe(false);
    expect(isFkViolation(new Error("x"))).toBe(false);
    expect(isFkViolation(null)).toBe(false);
  });
});
