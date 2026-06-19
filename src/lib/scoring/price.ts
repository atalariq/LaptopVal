import type { LaptopSpecs, ScoringConfig, FactorScore } from "./types";

export function estimateFairPrice(
  qualityPoints: number,
  location: string | undefined,
  config: ScoringConfig,
): number {
  const mult = location
    ? (config.price.regionMultipliers[location] ?? 1.0)
    : 1.0;
  // Clamp to 0 — extreme age penalties can drive qualityPoints negative.
  return Math.max(
    0,
    Math.round(qualityPoints * config.price.idrPerQualityPoint * mult),
  );
}

export function scorePrice(
  specs: LaptopSpecs,
  qualityPoints: number,
  config: ScoringConfig,
): { factor: FactorScore; fairPrice: number } {
  const fairPrice = estimateFairPrice(qualityPoints, specs.location, config);
  const ratio = fairPrice > 0 ? specs.price / fairPrice : Infinity;
  const band = [...config.price.bands]
    .sort((a, b) => a.maxRatio - b.maxRatio)
    .find((b) => ratio <= b.maxRatio);
  const points = band ? band.points : 0;
  const max = Math.max(...config.price.bands.map((b) => b.points));
  return {
    factor: { factor: "price", label: "Harga", points, max },
    fairPrice,
  };
}
