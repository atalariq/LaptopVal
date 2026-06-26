import { describe, it, expect } from "vitest";
import {
  evaluateSchema,
  brandSchema,
  useCaseSchema,
  laptopSchema,
  scoringConfigSchema,
  parseWith,
} from "./schemas";
import { DEFAULT_SCORING_CONFIG } from "$lib/scoring";

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

describe("brandSchema", () => {
  it("rejects empty name", () => {
    expect(brandSchema.safeParse({ name: "", notes: "" }).success).toBe(false);
  });
  it("coerces empty notes to null", () => {
    const r = brandSchema.parse({ name: "Lenovo", notes: "" });
    expect(r.notes).toBeNull();
  });
});

describe("laptopSchema", () => {
  const validLaptop = {
    brandId: 1,
    model: "T480",
    releaseYear: 2019,
    cpuTier: 2,
    ramGb: 16,
    storageGb: 512,
    condition: 3,
    hasWarranty: "on",
    price: 4500,
    location: "jabodetabek",
    imagePath: "",
    sourceUrl: "",
  };
  it("accepts valid input and coerces warranty", () => {
    const r = laptopSchema.parse(validLaptop);
    expect(r.hasWarranty).toBe(true);
    expect(r.imagePath).toBeNull();
  });
  it("rejects an unknown location", () => {
    expect(
      laptopSchema.safeParse({ ...validLaptop, location: "mars" }).success,
    ).toBe(false);
  });
  it("rejects brandId 0", () => {
    expect(laptopSchema.safeParse({ ...validLaptop, brandId: 0 }).success).toBe(
      false,
    );
  });
  it("accepts an http(s) sourceUrl", () => {
    const r = laptopSchema.parse({
      ...validLaptop,
      sourceUrl: "https://example.com/listing",
    });
    expect(r.sourceUrl).toBe("https://example.com/listing");
  });
  it("rejects a javascript: sourceUrl (XSS via href)", () => {
    expect(
      laptopSchema.safeParse({
        ...validLaptop,
        // eslint-disable-next-line no-script-url
        sourceUrl: "javascript:alert(1)",
      }).success,
    ).toBe(false);
  });
  it("rejects a non-http imagePath", () => {
    expect(
      laptopSchema.safeParse({
        ...validLaptop,
        imagePath: "data:text/html,<script>alert(1)</script>",
      }).success,
    ).toBe(false);
  });
});

describe("parseWith", () => {
  it("returns a field->message error map on failure", () => {
    const r = parseWith(useCaseSchema, {
      name: "",
      minRamGb: 8,
      minCpuTier: 2,
      minStorage: 256,
    });
    expect(r.ok).toBe(false);
    if (!r.ok) expect(r.errors.name).toBeTruthy();
  });
  it("keeps the full path for nested field errors", () => {
    const bad = structuredClone(DEFAULT_SCORING_CONFIG);
    bad.price.idrPerQualityPoint = -1; // must be positive
    const r = parseWith(scoringConfigSchema, bad);
    expect(r.ok).toBe(false);
    if (!r.ok) expect(r.errors["price.idrPerQualityPoint"]).toBeTruthy();
  });
});
