import { describe, it, expect } from "vitest";
import { estimateFairPrice, scorePrice } from "./price";
import { DEFAULT_SCORING_CONFIG as C } from "./config";
import type { LaptopSpecs } from "./types";

const base: LaptopSpecs = {
  cpuTier: 2,
  ramGb: 16,
  storageGb: 512,
  condition: 3,
  hasWarranty: false,
  releaseYear: 2021,
  price: 0,
};

describe("estimateFairPrice", () => {
  it("is qualityPoints * idrPerQualityPoint with no region", () => {
    expect(estimateFairPrice(70, undefined, C)).toBe(70 * 60);
  });
  it("applies the region multiplier", () => {
    expect(estimateFairPrice(70, "jabodetabek", C)).toBe(
      Math.round(70 * 60 * 1.05),
    );
  });
  it("defaults unknown region multiplier to 1.0", () => {
    expect(estimateFairPrice(70, "kalimantan", C)).toBe(70 * 60);
  });
});

describe("scorePrice", () => {
  it("awards max points when well under fair price", () => {
    const fair = 70 * 60;
    const r = scorePrice({ ...base, price: Math.round(fair * 0.7) }, 70, C);
    expect(r.factor.points).toBe(10);
    expect(r.fairPrice).toBe(fair);
  });
  it("awards zero when far over fair price", () => {
    const fair = 70 * 60;
    const r = scorePrice({ ...base, price: Math.round(fair * 2) }, 70, C);
    expect(r.factor.points).toBe(0);
  });
  it("awards a middle band near fair price", () => {
    const fair = 70 * 60;
    const r = scorePrice({ ...base, price: Math.round(fair * 1.1) }, 70, C);
    expect(r.factor.points).toBe(4);
  });
});
