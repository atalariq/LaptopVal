import { describe, it, expect } from "vitest";
import { score, verdictFor } from "./index";
import { DEFAULT_SCORING_CONFIG as C } from "./config";
import type { LaptopSpecs } from "./types";

describe("verdictFor", () => {
  it("maps totals to bands", () => {
    expect(verdictFor(80, C)).toBe("Great Deal");
    expect(verdictFor(60, C)).toBe("Fair");
    expect(verdictFor(40, C)).toBe("Overpriced");
    expect(verdictFor(10, C)).toBe("Avoid");
  });
});

describe("score", () => {
  const good: LaptopSpecs = {
    cpuBenchmark: 20000,
    gpuBenchmark: 14000,
    ramGb: 32,
    storageGb: 1000,
    condition: 4,
    hasWarranty: true,
    releaseYear: 2022,
    price: 3000,
    location: "jawa_barat",
  };

  it("clamps total into 0..100 and returns all 8 factors", () => {
    const r = score(good, C);
    expect(r.total).toBeGreaterThanOrEqual(0);
    expect(r.total).toBeLessThanOrEqual(100);
    expect(r.breakdown).toHaveLength(8);
    expect(r.breakdown.map((f) => f.factor)).toContain("price");
  });

  it("a cheap high-spec laptop is a Great Deal", () => {
    expect(score(good, C).verdict).toBe("Great Deal");
  });

  it("an overpriced weak laptop scores low", () => {
    const bad: LaptopSpecs = {
      cpuBenchmark: 2500,
      gpuBenchmark: 0,
      ramGb: 4,
      storageGb: 128,
      condition: 1,
      hasWarranty: false,
      releaseYear: 2016,
      price: 9000,
    };
    const r = score(bad, C);
    expect(r.total).toBeLessThan(35);
    expect(r.verdict).toBe("Avoid");
  });

  it("total equals sum of breakdown points (clamped)", () => {
    const r = score(good, C);
    const sum = r.breakdown.reduce((s, f) => s + f.points, 0);
    expect(r.total).toBe(Math.min(100, Math.max(0, sum)));
  });

  it("uses the default config when none is passed", () => {
    expect(score(good).verdict).toBe(score(good, C).verdict);
  });
});
