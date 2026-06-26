import { describe, it, expect } from "vitest";
import {
  formatPrice,
  regionLabel,
  verdictColor,
  conditionLabel,
} from "./format";

describe("formatPrice", () => {
  it("multiplies thousands and formats as IDR", () => {
    expect(formatPrice(3500)).toBe("Rp 3.500.000");
    expect(formatPrice(0)).toBe("Rp 0");
  });
});
describe("regionLabel", () => {
  it("maps enum keys to human labels", () => {
    expect(regionLabel("jabodetabek")).toBe("Jabodetabek");
    expect(regionLabel("jawa_barat")).toBe("Jawa Barat");
  });
  it("falls back to the raw key when unknown", () => {
    expect(regionLabel("mars")).toBe("mars");
  });
});
describe("verdictColor", () => {
  it("maps each verdict to a semantic token class group", () => {
    expect(verdictColor("Great Deal")).toContain("success");
    expect(verdictColor("Fair")).toContain("info");
    expect(verdictColor("Overpriced")).toContain("warning");
    expect(verdictColor("Avoid")).toContain("error");
  });
});
describe("labels", () => {
  it("maps condition number to label", () => {
    expect(conditionLabel(4)).toBe("Mulus");
  });
});
