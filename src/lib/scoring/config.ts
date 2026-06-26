import type { ScoringConfig } from "./types";

export const DEFAULT_SCORING_CONFIG: ScoringConfig = {
  cpu: [
    { min: 18000, points: 30 },
    { min: 12000, points: 25 },
    { min: 8000, points: 20 },
    { min: 5000, points: 14 },
    { min: 3000, points: 8 },
    { min: 0, points: 4 },
  ],
  gpu: [
    { min: 18000, points: 13 },
    { min: 12000, points: 10 },
    { min: 7000, points: 7 },
    { min: 3000, points: 4 },
    { min: 0, points: 0 },
  ],
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
