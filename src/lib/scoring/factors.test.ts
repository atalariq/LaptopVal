import { describe, it, expect } from "vitest";
import { scoreQuality } from "./factors";
import { DEFAULT_SCORING_CONFIG as C } from "./config";
import type { LaptopSpecs } from "./types";

const base: LaptopSpecs = {
  cpuBenchmark: 9000,
  gpuBenchmark: 0,
  ramGb: 16,
  storageGb: 512,
  condition: 3,
  hasWarranty: false,
  releaseYear: 2021,
  price: 5000,
};
const pts = (specs: LaptopSpecs) =>
  Object.fromEntries(scoreQuality(specs, C).map((f) => [f.factor, f.points]));

describe("scoreQuality", () => {
  it("scores cpu by benchmark band", () => {
    expect(pts({ ...base, cpuBenchmark: 20000 }).cpu).toBe(30);
    expect(pts({ ...base, cpuBenchmark: 12000 }).cpu).toBe(25);
    expect(pts({ ...base, cpuBenchmark: 8000 }).cpu).toBe(20);
    expect(pts({ ...base, cpuBenchmark: 1000 }).cpu).toBe(4);
  });
  it("scores gpu by benchmark band; 0 = no points", () => {
    expect(pts({ ...base, gpuBenchmark: 0 }).gpu).toBe(0);
    expect(pts({ ...base, gpuBenchmark: 5000 }).gpu).toBe(4);
    expect(pts({ ...base, gpuBenchmark: 13000 }).gpu).toBe(10);
    expect(pts({ ...base, gpuBenchmark: 25000 }).gpu).toBe(13);
  });
  it("picks the highest matching ram band", () => {
    expect(pts({ ...base, ramGb: 32 }).ram).toBe(25);
    expect(pts({ ...base, ramGb: 4 }).ram).toBe(5);
  });
  it("picks the highest matching storage band", () => {
    expect(pts({ ...base, storageGb: 256 }).storage).toBe(7);
  });
  it("scores condition as (level-1)*perLevel", () => {
    expect(pts({ ...base, condition: 4 }).condition).toBe(15);
    expect(pts({ ...base, condition: 1 }).condition).toBe(0);
  });
  it("adds warranty bonus only when true", () => {
    expect(pts({ ...base, hasWarranty: true }).warranty).toBe(5);
    expect(pts({ ...base, hasWarranty: false }).warranty).toBe(0);
  });
  it("applies the first matching age penalty (negative points)", () => {
    expect(pts({ ...base, releaseYear: 2017 }).age).toBe(-5);
    expect(pts({ ...base, releaseYear: 2019 }).age).toBe(-2);
    expect(pts({ ...base, releaseYear: 2022 }).age).toBe(0);
  });
});
