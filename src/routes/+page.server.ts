import type { PageServerLoad } from "./$types";
import {
  listLaptops,
  getUseCases,
  getActiveScoringConfig,
} from "$lib/server/queries";
import { toSpecs } from "$lib/laptop";
import { score } from "$lib/scoring";
import { REGION_KEYS } from "$lib/format";

const SORTS = {
  score_desc: (a: number, b: number) => b - a,
  score_asc: (a: number, b: number) => a - b,
} as const;
type SortKey = keyof typeof SORTS;

export const load: PageServerLoad = async ({ url }) => {
  const useCaseId = Number(url.searchParams.get("use_case")) || undefined;
  const location = url.searchParams.get("location") || undefined;
  const search = url.searchParams.get("q")?.trim() || undefined;
  const sortParam = url.searchParams.get("sort");
  const sort: SortKey =
    sortParam && sortParam in SORTS ? (sortParam as SortKey) : "score_desc";

  const [rows, useCases, config] = await Promise.all([
    listLaptops({ useCaseId, location, search }),
    getUseCases(),
    getActiveScoringConfig(),
  ]);

  const items = rows
    .map((laptop) => ({ laptop, result: score(toSpecs(laptop), config) }))
    .sort((x, y) => SORTS[sort](x.result.total, y.result.total));

  return {
    items,
    useCases,
    regions: REGION_KEYS,
    filters: {
      useCaseId: useCaseId ?? 0,
      location: location ?? "",
      search: search ?? "",
      sort,
    },
  };
};
