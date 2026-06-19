import type { PageServerLoad } from "./$types";
import { getDashboardStats } from "$lib/server/stats";

export const load: PageServerLoad = async () => getDashboardStats();
