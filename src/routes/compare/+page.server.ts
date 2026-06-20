import type { PageServerLoad } from "./$types";
import {
  getLaptopsByIds,
  listLaptops,
  getActiveScoringConfig,
} from "$lib/server/queries";
import { toSpecs } from "$lib/laptop";
import { score } from "$lib/scoring";

export const load: PageServerLoad = async ({ url }) => {
  const ids = (url.searchParams.get("ids") ?? "")
    .split(",")
    .map(Number)
    .filter((id) => Number.isInteger(id) && id > 0)
    .filter((id, i, arr) => arr.indexOf(id) === i)
    .slice(0, 4);

  const [rows, allRows, config] = await Promise.all([
    getLaptopsByIds(ids),
    listLaptops(),
    getActiveScoringConfig(),
  ]);

  // Preserve URL order
  const rowMap = new Map(rows.map((r) => [r.id, r]));
  const ordered = ids.flatMap((id) =>
    rowMap.has(id) ? [rowMap.get(id)!] : [],
  );
  const items = ordered.map((laptop) => ({
    laptop,
    result: score(toSpecs(laptop), config),
  }));

  const inCompare = new Set(ids);
  const addable = allRows
    .filter((r) => !inCompare.has(r.id))
    .map((r) => ({ id: r.id, label: `${r.brand} ${r.model}` }));

  return { items, addable, ids };
};
