import type { PageServerLoad } from "./$types";
import { listLaptops, getActiveScoringConfig } from "$lib/server/queries";
import { toSpecs } from "$lib/laptop";
import { score } from "$lib/scoring";

const TIER_KEYS = ["s", "a", "b", "c", "d"] as const;
type TierKey = (typeof TIER_KEYS)[number];

const VERDICT_TIER: Record<string, TierKey> = {
  "Great Deal": "s",
  Fair: "a",
  Overpriced: "b",
  Avoid: "c",
};

export const load: PageServerLoad = async ({ url }) => {
  const [rows, config] = await Promise.all([
    listLaptops(),
    getActiveScoringConfig(),
  ]);

  const entries = rows.map((laptop) => ({
    laptop,
    result: score(toSpecs(laptop), config),
  }));

  const hasParams = TIER_KEYS.some((k) => url.searchParams.has(k));
  let tiers: Record<TierKey, number[]>;

  if (hasParams) {
    tiers = Object.fromEntries(
      TIER_KEYS.map((k) => [
        k,
        (url.searchParams.get(k) ?? "")
          .split(",")
          .map(Number)
          .filter((id) => Number.isInteger(id) && id > 0),
      ]),
    ) as Record<TierKey, number[]>;
  } else {
    tiers = { s: [], a: [], b: [], c: [], d: [] };
    for (const { laptop, result } of entries) {
      const tier = VERDICT_TIER[result.verdict] ?? "c";
      tiers[tier].push(laptop.id);
    }
  }

  return { entries, tiers };
};
