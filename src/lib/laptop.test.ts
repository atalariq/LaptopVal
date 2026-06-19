import { describe, it, expect } from "vitest";
import { toSpecs, type LaptopRow } from "./laptop";

const row: LaptopRow = {
  id: 1,
  brand: "Lenovo",
  brandId: 2,
  model: "T480",
  releaseYear: 2019,
  cpuTier: 2,
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
      cpuTier: 2,
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
