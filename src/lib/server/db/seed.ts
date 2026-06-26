import argon2 from "argon2";
import { db } from "./index";
import {
  users,
  brands,
  useCases,
  scoringConfig,
  laptops,
  evaluations,
  cpus,
  gpus,
} from "./schema";
import { DEFAULT_SCORING_CONFIG } from "$lib/scoring/config";

// Hash with the same library the app verifies with (argon2id), so seeding works
// under Node too — not only under Bun.
const hash = await argon2.hash(process.env.ADMIN_PASSWORD ?? "admin123");

await db.delete(evaluations);
await db.delete(laptops);
await db.delete(scoringConfig);
await db.delete(useCases);
await db.delete(cpus);
await db.delete(gpus);
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

const cpuRows = await db
  .insert(cpus)
  .values([
    { name: "Intel Core i3-6100U", benchmark: 2400, vendor: "Intel" },
    { name: "Intel Core i5-6300U", benchmark: 3200, vendor: "Intel" },
    { name: "Intel Core i7-6600U", benchmark: 3700, vendor: "Intel" },
    { name: "Intel Core i5-7200U", benchmark: 3500, vendor: "Intel" },
    { name: "Intel Core i7-7700HQ", benchmark: 7200, vendor: "Intel" },
    { name: "Intel Core i5-8250U", benchmark: 6000, vendor: "Intel" },
    { name: "Intel Core i7-8550U", benchmark: 7100, vendor: "Intel" },
    { name: "Intel Core i5-8265U", benchmark: 6300, vendor: "Intel" },
    { name: "Intel Core i7-8750H", benchmark: 11000, vendor: "Intel" },
    { name: "Intel Core i5-10210U", benchmark: 6500, vendor: "Intel" },
    { name: "Intel Core i7-10510U", benchmark: 7300, vendor: "Intel" },
    { name: "Intel Core i7-10750H", benchmark: 12500, vendor: "Intel" },
    { name: "Intel Core i5-1135G7", benchmark: 10000, vendor: "Intel" },
    { name: "Intel Core i7-1165G7", benchmark: 10500, vendor: "Intel" },
    { name: "Intel Core i5-1235U", benchmark: 12500, vendor: "Intel" },
    { name: "Intel Core i7-1255U", benchmark: 14000, vendor: "Intel" },
    { name: "Intel Core i7-11800H", benchmark: 20500, vendor: "Intel" },
    { name: "Intel Core i9-12900H", benchmark: 27000, vendor: "Intel" },
    { name: "Intel Core i7-13700H", benchmark: 28000, vendor: "Intel" },
    { name: "AMD Ryzen 3 3250U", benchmark: 4200, vendor: "AMD" },
    { name: "AMD Ryzen 5 3500U", benchmark: 7000, vendor: "AMD" },
    { name: "AMD Ryzen 7 3700U", benchmark: 7600, vendor: "AMD" },
    { name: "AMD Ryzen 5 4500U", benchmark: 11500, vendor: "AMD" },
    { name: "AMD Ryzen 5 4600H", benchmark: 14500, vendor: "AMD" },
    { name: "AMD Ryzen 7 4700U", benchmark: 13500, vendor: "AMD" },
    { name: "AMD Ryzen 5 5500U", benchmark: 13000, vendor: "AMD" },
    { name: "AMD Ryzen 7 5800H", benchmark: 21000, vendor: "AMD" },
    { name: "AMD Ryzen 9 5900HX", benchmark: 24000, vendor: "AMD" },
    { name: "AMD Ryzen 7 6800H", benchmark: 24500, vendor: "AMD" },
    { name: "AMD Ryzen 9 7940HS", benchmark: 30000, vendor: "AMD" },
    { name: "Apple M1", benchmark: 15000, vendor: "Apple" },
    { name: "Apple M2", benchmark: 16500, vendor: "Apple" },
    { name: "Apple M3", benchmark: 19500, vendor: "Apple" },
    { name: "Intel Celeron N4020", benchmark: 1100, vendor: "Intel" },
    { name: "Intel Pentium Silver N5030", benchmark: 1700, vendor: "Intel" },
    { name: "Intel Celeron N4500", benchmark: 1500, vendor: "Intel" },
  ])
  .returning();
const cpuByName = Object.fromEntries(cpuRows.map((c) => [c.name, c.id]));

const gpuRows = await db
  .insert(gpus)
  .values([
    { name: "Intel UHD Graphics 620", benchmark: 900, kind: "integrated" },
    { name: "Intel Iris Xe Graphics", benchmark: 2300, kind: "integrated" },
    { name: "AMD Radeon Vega 8", benchmark: 1700, kind: "integrated" },
    { name: "AMD Radeon 680M", benchmark: 4200, kind: "integrated" },
    { name: "Apple M1 GPU", benchmark: 5200, kind: "integrated" },
    { name: "Apple M3 GPU", benchmark: 7800, kind: "integrated" },
    { name: "NVIDIA GeForce MX350", benchmark: 3000, kind: "discrete" },
    { name: "NVIDIA GeForce MX450", benchmark: 3600, kind: "discrete" },
    {
      name: "NVIDIA GeForce GTX 1650 Laptop",
      benchmark: 7600,
      kind: "discrete",
    },
    {
      name: "NVIDIA GeForce GTX 1660 Ti Laptop",
      benchmark: 10500,
      kind: "discrete",
    },
    {
      name: "NVIDIA GeForce RTX 3050 Laptop",
      benchmark: 9000,
      kind: "discrete",
    },
    {
      name: "NVIDIA GeForce RTX 3060 Laptop",
      benchmark: 13500,
      kind: "discrete",
    },
    {
      name: "NVIDIA GeForce RTX 3070 Laptop",
      benchmark: 16500,
      kind: "discrete",
    },
    {
      name: "NVIDIA GeForce RTX 4060 Laptop",
      benchmark: 18500,
      kind: "discrete",
    },
    {
      name: "NVIDIA GeForce RTX 4070 Laptop",
      benchmark: 21500,
      kind: "discrete",
    },
    { name: "AMD Radeon RX 6600M", benchmark: 14000, kind: "discrete" },
    { name: "AMD Radeon RX 6700M", benchmark: 16000, kind: "discrete" },
  ])
  .returning();
const gpuByName = Object.fromEntries(gpuRows.map((g) => [g.name, g.id]));

await db.insert(useCases).values([
  {
    name: "Office",
    minRamGb: 8,
    minCpuBenchmark: 3000,
    minGpuBenchmark: 0,
    minStorage: 256,
  },
  {
    name: "Kuliah",
    minRamGb: 8,
    minCpuBenchmark: 6000,
    minGpuBenchmark: 0,
    minStorage: 256,
  },
  {
    name: "Editing",
    minRamGb: 16,
    minCpuBenchmark: 10000,
    minGpuBenchmark: 7000,
    minStorage: 512,
  },
  {
    name: "Gaming",
    minRamGb: 16,
    minCpuBenchmark: 10000,
    minGpuBenchmark: 10000,
    minStorage: 512,
  },
]);

await db
  .insert(scoringConfig)
  .values({ config: DEFAULT_SCORING_CONFIG, active: true });

await db.insert(laptops).values([
  {
    brandId: byName["Lenovo"],
    model: "ThinkPad T480",
    releaseYear: 2019,
    cpuId: cpuByName["Intel Core i5-8250U"],
    gpuId: null,
    ramGb: 16,
    storageGb: 512,
    condition: 3,
    hasWarranty: false,
    price: 4500,
    location: "jabodetabek",
  },
  {
    brandId: byName["Lenovo"],
    model: "ThinkPad X1 Carbon Gen 9",
    releaseYear: 2021,
    cpuId: cpuByName["Intel Core i7-1165G7"],
    gpuId: null,
    ramGb: 16,
    storageGb: 1000,
    condition: 4,
    hasWarranty: true,
    price: 11000,
    location: "jabodetabek",
  },
  {
    brandId: byName["Lenovo"],
    model: "ThinkPad T490",
    releaseYear: 2020,
    cpuId: cpuByName["Intel Core i5-10210U"],
    gpuId: null,
    ramGb: 8,
    storageGb: 256,
    condition: 3,
    hasWarranty: false,
    price: 5200,
    location: "jawa_tengah",
  },
  {
    brandId: byName["Dell"],
    model: "Latitude 7490",
    releaseYear: 2018,
    cpuId: cpuByName["Intel Core i5-8250U"],
    gpuId: null,
    ramGb: 8,
    storageGb: 256,
    condition: 2,
    hasWarranty: false,
    price: 3200,
    location: "jawa_barat",
  },
  {
    brandId: byName["Dell"],
    model: "XPS 15 9500",
    releaseYear: 2020,
    cpuId: cpuByName["Intel Core i7-10750H"],
    gpuId: gpuByName["NVIDIA GeForce GTX 1650 Laptop"],
    ramGb: 16,
    storageGb: 512,
    condition: 3,
    hasWarranty: false,
    price: 13500,
    location: "jabodetabek",
  },
  {
    brandId: byName["Dell"],
    model: "Inspiron 14 5410",
    releaseYear: 2021,
    cpuId: cpuByName["Intel Core i5-1135G7"],
    gpuId: null,
    ramGb: 8,
    storageGb: 512,
    condition: 3,
    hasWarranty: false,
    price: 6000,
    location: "sumatera",
  },
  {
    brandId: byName["HP"],
    model: "EliteBook 840 G6",
    releaseYear: 2019,
    cpuId: cpuByName["Intel Core i5-8265U"],
    gpuId: null,
    ramGb: 8,
    storageGb: 256,
    condition: 3,
    hasWarranty: false,
    price: 4200,
    location: "jawa_timur",
  },
  {
    brandId: byName["HP"],
    model: "Pavilion Gaming 15",
    releaseYear: 2020,
    cpuId: cpuByName["AMD Ryzen 5 4600H"],
    gpuId: gpuByName["NVIDIA GeForce GTX 1650 Laptop"],
    ramGb: 8,
    storageGb: 512,
    condition: 2,
    hasWarranty: false,
    price: 7000,
    location: "jawa_barat",
  },
  {
    brandId: byName["HP"],
    model: "Victus 16",
    releaseYear: 2022,
    cpuId: cpuByName["AMD Ryzen 7 5800H"],
    gpuId: gpuByName["NVIDIA GeForce RTX 3060 Laptop"],
    ramGb: 16,
    storageGb: 512,
    condition: 4,
    hasWarranty: true,
    price: 13000,
    location: "jabodetabek",
  },
  {
    brandId: byName["Asus"],
    model: "ROG Zephyrus G14",
    releaseYear: 2021,
    cpuId: cpuByName["AMD Ryzen 9 5900HX"],
    gpuId: gpuByName["NVIDIA GeForce RTX 3060 Laptop"],
    ramGb: 16,
    storageGb: 1000,
    condition: 4,
    hasWarranty: true,
    price: 12000,
    location: "jawa_timur",
  },
  {
    brandId: byName["Asus"],
    model: "VivoBook 14",
    releaseYear: 2020,
    cpuId: cpuByName["AMD Ryzen 5 4500U"],
    gpuId: null,
    ramGb: 8,
    storageGb: 512,
    condition: 3,
    hasWarranty: false,
    price: 5000,
    location: "kalimantan",
  },
  {
    brandId: byName["Asus"],
    model: "TUF Gaming A15",
    releaseYear: 2022,
    cpuId: cpuByName["AMD Ryzen 7 6800H"],
    gpuId: gpuByName["NVIDIA GeForce RTX 4060 Laptop"],
    ramGb: 16,
    storageGb: 1000,
    condition: 4,
    hasWarranty: true,
    price: 15500,
    location: "jabodetabek",
  },
  {
    brandId: byName["Acer"],
    model: "Aspire 5",
    releaseYear: 2021,
    cpuId: cpuByName["Intel Core i5-1135G7"],
    gpuId: null,
    ramGb: 8,
    storageGb: 512,
    condition: 3,
    hasWarranty: false,
    price: 5500,
    location: "sulawesi",
  },
  {
    brandId: byName["Acer"],
    model: "Swift 3",
    releaseYear: 2020,
    cpuId: cpuByName["AMD Ryzen 7 4700U"],
    gpuId: null,
    ramGb: 8,
    storageGb: 512,
    condition: 3,
    hasWarranty: false,
    price: 5800,
    location: "bali_nusra",
  },
  {
    brandId: byName["Acer"],
    model: "Nitro 5",
    releaseYear: 2021,
    cpuId: cpuByName["Intel Core i7-11800H"],
    gpuId: gpuByName["NVIDIA GeForce RTX 3050 Laptop"],
    ramGb: 16,
    storageGb: 512,
    condition: 3,
    hasWarranty: false,
    price: 11000,
    location: "jawa_barat",
  },
  {
    brandId: byName["Lenovo"],
    model: "IdeaPad Slim 3",
    releaseYear: 2019,
    cpuId: cpuByName["Intel Core i3-6100U"],
    gpuId: null,
    ramGb: 4,
    storageGb: 256,
    condition: 2,
    hasWarranty: false,
    price: 2800,
    location: "lainnya",
  },
  {
    brandId: byName["HP"],
    model: "Stream 14",
    releaseYear: 2020,
    cpuId: cpuByName["Intel Celeron N4020"],
    gpuId: null,
    ramGb: 4,
    storageGb: 128,
    condition: 2,
    hasWarranty: false,
    price: 2200,
    location: "sumatera",
  },
  {
    brandId: byName["Asus"],
    model: "ZenBook 14",
    releaseYear: 2022,
    cpuId: cpuByName["Intel Core i7-1255U"],
    gpuId: null,
    ramGb: 16,
    storageGb: 512,
    condition: 4,
    hasWarranty: true,
    price: 9500,
    location: "jabodetabek",
  },
]);

console.log(
  `Seeded: admin=${admin.username}, ${brandRows.length} brands, ${cpuRows.length} cpus, ${gpuRows.length} gpus, 18 laptops`,
);
process.exit(0);
