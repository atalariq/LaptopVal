import type { LaptopSpecs, ScoringConfig, ScoreResult, Verdict } from "./types";
import { DEFAULT_SCORING_CONFIG } from "./config";
import { scoreQuality } from "./factors";
import { scorePrice } from "./price";

export * from "./types";
export { DEFAULT_SCORING_CONFIG } from "./config";

export function verdictFor(total: number, config: ScoringConfig): Verdict {
  for (const b of [...config.verdictBands].sort((a, z) => z.min - a.min)) {
    if (total >= b.min) return b.verdict;
  }
  return "Avoid";
}

export function score(
  specs: LaptopSpecs,
  config: ScoringConfig = DEFAULT_SCORING_CONFIG,
): ScoreResult {
  const quality = scoreQuality(specs, config);
  const qualityPoints = quality.reduce((s, f) => s + f.points, 0);
  const { factor: priceFactor, fairPrice } = scorePrice(
    specs,
    qualityPoints,
    config,
  );
  const breakdown = [...quality, priceFactor];
  const raw = breakdown.reduce((s, f) => s + f.points, 0);
  const total = Math.min(100, Math.max(0, raw));
  return { total, verdict: verdictFor(total, config), fairPrice, breakdown };
}
