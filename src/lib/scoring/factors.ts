import type { LaptopSpecs, ScoringConfig, FactorScore, Band } from "./types";

function bandPoints(value: number, bands: Band[]): number {
  // bands listed high → low; first whose min <= value wins
  for (const b of [...bands].sort((a, z) => z.min - a.min)) {
    if (value >= b.min) return b.points;
  }
  return 0;
}

export function scoreQuality(
  specs: LaptopSpecs,
  config: ScoringConfig,
): FactorScore[] {
  const agePenalty = config.agePenalty
    .filter((a) => specs.releaseYear < a.beforeYear)
    .reduce((worst, a) => Math.max(worst, a.penalty), 0);

  return [
    {
      factor: "cpu",
      label: "CPU",
      points: bandPoints(specs.cpuBenchmark, config.cpu),
      max: Math.max(...config.cpu.map((b) => b.points)),
    },
    {
      factor: "gpu",
      label: "GPU",
      points: bandPoints(specs.gpuBenchmark, config.gpu),
      max: Math.max(...config.gpu.map((b) => b.points)),
    },
    {
      factor: "ram",
      label: "RAM",
      points: bandPoints(specs.ramGb, config.ram),
      max: Math.max(...config.ram.map((b) => b.points)),
    },
    {
      factor: "storage",
      label: "Storage",
      points: bandPoints(specs.storageGb, config.storage),
      max: Math.max(...config.storage.map((b) => b.points)),
    },
    {
      factor: "condition",
      label: "Kondisi",
      points: (specs.condition - 1) * config.conditionPerLevel,
      max: 3 * config.conditionPerLevel,
    },
    {
      factor: "warranty",
      label: "Garansi",
      points: specs.hasWarranty ? config.warrantyBonus : 0,
      max: config.warrantyBonus,
    },
    {
      factor: "age",
      label: "Umur",
      points: agePenalty === 0 ? 0 : -agePenalty,
      max: 0,
    },
  ];
}
