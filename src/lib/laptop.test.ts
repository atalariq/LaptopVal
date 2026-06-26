import { describe, it, expect } from "vitest";
import { toSpecs, type LaptopRow } from "./laptop";

const row: LaptopRow = {
  id: 1,
  brand: "Lenovo",
  brandId: 2,
  model: "T480",
  releaseYear: 2019,
  cpuId: 1,
  cpuName: "Intel Core i5-8250U",
  cpuBenchmark: 7200,
  gpuId: null,
  gpuName: null,
  gpuBenchmark: 0,
  ramGb: 16,
  storageGb: 512,
  condition: 3,
  hasWarranty: false,
  price: 4500,
  location: "jabodetabek",
  imagePath: null,
  sourceUrl: null,
};

describe("toSpecs", () => {
  it("extracts the engine spec fields including location", () => {
    expect(toSpecs(row)).toEqual({
      cpuBenchmark: 7200,
      gpuBenchmark: 0,
      ramGb: 16,
      storageGb: 512,
      condition: 3,
      hasWarranty: false,
      releaseYear: 2019,
      price: 4500,
      location: "jabodetabek",
    });
  });
});
