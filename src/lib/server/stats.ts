import { listLaptops, getBrands, getActiveScoringConfig } from "./queries";
import { toSpecs } from "$lib/laptop";
import { score } from "$lib/scoring";

export async function getDashboardStats() {
  const [rows, brandRows, config] = await Promise.all([
    listLaptops(),
    getBrands(),
    getActiveScoringConfig(),
  ]);
  const scored = rows.map((laptop) => ({
    laptop,
    result: score(toSpecs(laptop), config),
  }));

  const byBrand = new Map<string, { total: number; count: number }>();
  for (const { laptop, result } of scored) {
    const e = byBrand.get(laptop.brand) ?? { total: 0, count: 0 };
    e.total += result.total;
    e.count += 1;
    byBrand.set(laptop.brand, e);
  }
  const perBrand = [...byBrand.entries()]
    .map(([brand, { total, count }]) => ({
      brand,
      avg: Math.round(total / count),
      count,
    }))
    .sort((a, b) => b.avg - a.avg);

  const avgScore = scored.length
    ? Math.round(scored.reduce((s, x) => s + x.result.total, 0) / scored.length)
    : 0;
  const topDeals = [...scored]
    .sort((a, b) => b.result.total - a.result.total)
    .slice(0, 5);

  return {
    totalLaptops: scored.length,
    totalBrands: brandRows.length,
    avgScore,
    perBrand,
    topDeals,
  };
}
