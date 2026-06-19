import { error } from "@sveltejs/kit";
import type { PageServerLoad } from "./$types";
import {
  getLaptopById,
  priceRangeForModel,
  getActiveScoringConfig,
} from "$lib/server/queries";
import { toSpecs } from "$lib/laptop";
import { score } from "$lib/scoring";

export const load: PageServerLoad = async ({ params }) => {
  const id = Number(params.id);
  if (!Number.isInteger(id) || id <= 0)
    throw error(404, "Laptop tidak ditemukan");

  const laptop = await getLaptopById(id);
  if (!laptop) throw error(404, "Laptop tidak ditemukan");

  const config = await getActiveScoringConfig();
  const priceRange = await priceRangeForModel(laptop.model, laptop.brandId);
  return { laptop, result: score(toSpecs(laptop), config), priceRange };
};
