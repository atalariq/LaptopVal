/// <reference types="bun" />
import { db } from "./index";
import {
  users,
  brands,
  useCases,
  scoringConfig,
  laptops,
  evaluations,
} from "./schema";
import { DEFAULT_SCORING_CONFIG } from "$lib/scoring/config";

const hash = await Bun.password.hash(process.env.ADMIN_PASSWORD ?? "admin123");

await db.delete(evaluations);
await db.delete(laptops);
await db.delete(scoringConfig);
await db.delete(useCases);
await db.delete(brands);
await db.delete(users);

const [admin] = await db
  .insert(users)
  .values({
    username: process.env.ADMIN_USERNAME ?? "admin",
    passwordHash: hash,
  })
  .returning();

const brandRows = await db
  .insert(brands)
  .values([
    { name: "Lenovo", notes: "ThinkPad lini bekas populer" },
    { name: "Dell", notes: "Latitude/XPS" },
    { name: "HP", notes: "EliteBook" },
    { name: "Asus", notes: null },
    { name: "Acer", notes: null },
  ])
  .returning();
const byName = Object.fromEntries(brandRows.map((b) => [b.name, b.id]));

await db.insert(useCases).values([
  { name: "Office", minRamGb: 8, minCpuTier: 1, minStorage: 256 },
  { name: "Kuliah", minRamGb: 8, minCpuTier: 2, minStorage: 256 },
  { name: "Editing", minRamGb: 16, minCpuTier: 3, minStorage: 512 },
  { name: "Gaming", minRamGb: 16, minCpuTier: 3, minStorage: 512 },
]);

await db
  .insert(scoringConfig)
  .values({ config: DEFAULT_SCORING_CONFIG, active: true });

await db.insert(laptops).values([
  {
    brandId: byName["Lenovo"],
    model: "ThinkPad T480",
    releaseYear: 2019,
    cpuTier: 2,
    ramGb: 16,
    storageGb: 512,
    condition: 3,
    hasWarranty: false,
    price: 4500,
    location: "jabodetabek",
  },
  {
    brandId: byName["Dell"],
    model: "Latitude 7490",
    releaseYear: 2018,
    cpuTier: 2,
    ramGb: 8,
    storageGb: 256,
    condition: 2,
    hasWarranty: false,
    price: 3200,
    location: "jawa_barat",
  },
  {
    brandId: byName["Asus"],
    model: "ROG Zephyrus G14",
    releaseYear: 2021,
    cpuTier: 3,
    ramGb: 16,
    storageGb: 1000,
    condition: 4,
    hasWarranty: true,
    price: 12000,
    location: "jawa_timur",
  },
]);

console.log(
  `Seeded: admin=${admin.username}, ${brandRows.length} brands, 3 laptops`,
);
process.exit(0);
