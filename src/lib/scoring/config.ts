import type { ScoringConfig } from "./types";

export const DEFAULT_SCORING_CONFIG: ScoringConfig = {
  cpuPerTier: 10, // max 30
  ram: [
    { min: 32, points: 25 },
    { min: 16, points: 20 },
    { min: 8, points: 10 },
    { min: 0, points: 5 },
  ],
  storage: [
    { min: 1000, points: 15 },
    { min: 512, points: 12 },
    { min: 256, points: 7 },
    { min: 0, points: 3 },
  ],
  conditionPerLevel: 5, // max 15
  warrantyBonus: 5,
  agePenalty: [
    { beforeYear: 2020, penalty: 2 },
    { beforeYear: 2018, penalty: 5 },
  ],
  price: {
    idrPerQualityPoint: 60, // ~Rp60k per quality point
    bands: [
      { maxRatio: 0.8, points: 10 },
      { maxRatio: 1.0, points: 7 },
      { maxRatio: 1.2, points: 4 },
      { maxRatio: 1.5, points: 1 },
      { maxRatio: Infinity, points: 0 },
    ],
    regionMultipliers: { jabodetabek: 1.05, bali_nusra: 1.05 },
  },
  verdictBands: [
    { min: 75, verdict: "Great Deal" },
    { min: 55, verdict: "Fair" },
    { min: 35, verdict: "Overpriced" },
    { min: 0, verdict: "Avoid" },
  ],
};
