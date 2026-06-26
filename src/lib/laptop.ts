import type { LaptopSpecs, Condition } from "$lib/scoring";

export interface LaptopRow {
  id: number;
  brand: string;
  brandId: number;
  model: string;
  releaseYear: number;
  cpuId: number;
  cpuName: string;
  cpuBenchmark: number;
  gpuId: number | null;
  gpuName: string | null;
  gpuBenchmark: number;
  ramGb: number;
  storageGb: number;
  condition: number;
  hasWarranty: boolean;
  price: number;
  location: string;
  imagePath: string | null;
  sourceUrl: string | null;
}

export function toSpecs(row: LaptopRow): LaptopSpecs {
  return {
    cpuBenchmark: row.cpuBenchmark,
    gpuBenchmark: row.gpuBenchmark,
    ramGb: row.ramGb,
    storageGb: row.storageGb,
    condition: row.condition as Condition,
    hasWarranty: row.hasWarranty,
    releaseYear: row.releaseYear,
    price: row.price,
    location: row.location,
  };
}
