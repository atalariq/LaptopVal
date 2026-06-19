import { describe, it, expect } from "vitest";
import { evaluateSchema } from "./schemas";

const valid = {
  cpuTier: 2,
  ramGb: 16,
  storageGb: 512,
  condition: 3,
  hasWarranty: false,
  releaseYear: 2020,
  price: 5000,
  location: "jabodetabek",
};

describe("evaluateSchema", () => {
  it("accepts valid specs", () => {
    expect(evaluateSchema.safeParse(valid).success).toBe(true);
  });
  it("rejects cpuTier out of 1..3", () => {
    expect(evaluateSchema.safeParse({ ...valid, cpuTier: 5 }).success).toBe(
      false,
    );
  });
  it("rejects non-positive price", () => {
    expect(evaluateSchema.safeParse({ ...valid, price: 0 }).success).toBe(
      false,
    );
  });
  it("rejects an implausible release year", () => {
    expect(
      evaluateSchema.safeParse({ ...valid, releaseYear: 1990 }).success,
    ).toBe(false);
  });
  it("allows omitted location", () => {
    const { location, ...noLoc } = valid;
    expect(evaluateSchema.safeParse(noLoc).success).toBe(true);
  });
});
