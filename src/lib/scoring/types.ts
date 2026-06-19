export type CpuTier = 1 | 2 | 3;
export type Condition = 1 | 2 | 3 | 4;
export type Verdict = "Great Deal" | "Fair" | "Overpriced" | "Avoid";

export interface LaptopSpecs {
  cpuTier: CpuTier;
  ramGb: number;
  storageGb: number;
  condition: Condition;
  hasWarranty: boolean;
  releaseYear: number;
  price: number; // thousands IDR
  location?: string; // region key; affects fair price only
}

export interface FactorScore {
  factor:
    | "cpu"
    | "ram"
    | "storage"
    | "condition"
    | "warranty"
    | "age"
    | "price";
  label: string;
  points: number;
  max: number;
}

export interface ScoreResult {
  total: number; // clamped 0..100
  verdict: Verdict;
  fairPrice: number; // thousands IDR
  breakdown: FactorScore[];
}

/** A threshold band: applies when value >= `min` (bands listed high → low). */
export interface Band {
  min: number;
  points: number;
}

export interface ScoringConfig {
  cpuPerTier: number; // points = cpuTier * cpuPerTier
  ram: Band[]; // by ramGb
  storage: Band[]; // by storageGb
  conditionPerLevel: number; // points = (condition - 1) * this
  warrantyBonus: number;
  agePenalty: { beforeYear: number; penalty: number }[]; // first match (newest year first)
  price: {
    idrPerQualityPoint: number; // fairPrice = qualityPoints * this * regionMultiplier
    bands: { maxRatio: number; points: number }[]; // by price/fairPrice (low → high)
    regionMultipliers: Record<string, number>; // default 1.0
  };
  verdictBands: { min: number; verdict: Verdict }[]; // high → low
}
