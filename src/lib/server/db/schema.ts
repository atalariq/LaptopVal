import {
  pgTable,
  serial,
  integer,
  smallint,
  varchar,
  text,
  boolean,
  timestamp,
  jsonb,
  pgEnum,
} from "drizzle-orm/pg-core";

// Indonesian regions — fixed enum for filtering + price-range grouping
export const regionEnum = pgEnum("region", [
  "jabodetabek",
  "jawa_barat",
  "jawa_tengah",
  "jawa_timur",
  "sumatera",
  "kalimantan",
  "sulawesi",
  "bali_nusra",
  "lainnya",
]);

export const users = pgTable("users", {
  id: serial("id").primaryKey(),
  username: varchar("username", { length: 50 }).notNull().unique(),
  passwordHash: text("password_hash").notNull(),
  createdAt: timestamp("created_at").defaultNow().notNull(),
});

export const brands = pgTable("brands", {
  id: serial("id").primaryKey(),
  name: varchar("name", { length: 50 }).notNull().unique(),
  notes: text("notes"),
});

export const cpus = pgTable("cpus", {
  id: serial("id").primaryKey(),
  name: varchar("name", { length: 100 }).notNull().unique(),
  benchmark: integer("benchmark").notNull(), // PassMark CPU Mark
  vendor: varchar("vendor", { length: 20 }).notNull(), // Intel | AMD | Apple
});

export const gpus = pgTable("gpus", {
  id: serial("id").primaryKey(),
  name: varchar("name", { length: 100 }).notNull().unique(),
  benchmark: integer("benchmark").notNull(), // PassMark G3D Mark
  kind: varchar("kind", { length: 20 }).notNull(), // integrated | discrete
});

export const laptops = pgTable("laptops", {
  id: serial("id").primaryKey(),
  brandId: integer("brand_id")
    .notNull()
    .references(() => brands.id),
  model: varchar("model", { length: 100 }).notNull(),
  releaseYear: smallint("release_year").notNull(),
  cpuId: integer("cpu_id")
    .notNull()
    .references(() => cpus.id),
  gpuId: integer("gpu_id").references(() => gpus.id), // nullable: null = integrated/none
  ramGb: smallint("ram_gb").notNull(),
  storageGb: integer("storage_gb").notNull(),
  condition: smallint("condition").notNull(), // 1=buruk..4=mulus
  hasWarranty: boolean("has_warranty").default(false).notNull(),
  price: integer("price").notNull(), // thousands IDR
  location: regionEnum("location").notNull(),
  imagePath: varchar("image_path", { length: 255 }),
  sourceUrl: varchar("source_url", { length: 255 }),
  createdBy: integer("created_by").references(() => users.id, {
    onDelete: "set null",
  }),
  createdAt: timestamp("created_at").defaultNow().notNull(),
  updatedAt: timestamp("updated_at").defaultNow().notNull(),
});

export const useCases = pgTable("use_cases", {
  id: serial("id").primaryKey(),
  name: varchar("name", { length: 50 }).notNull(),
  minRamGb: smallint("min_ram_gb").notNull(),
  minCpuBenchmark: integer("min_cpu_benchmark").notNull(),
  minGpuBenchmark: integer("min_gpu_benchmark").notNull().default(0),
  minStorage: integer("min_storage").notNull(),
});

// Data-driven scoring weights/thresholds (admin-editable in a later phase).
// One row holds the full active config as JSONB for simplicity + atomic edits.
export const scoringConfig = pgTable("scoring_config", {
  id: serial("id").primaryKey(),
  config: jsonb("config").notNull(),
  active: boolean("active").default(true).notNull(),
  updatedAt: timestamp("updated_at").defaultNow().notNull(),
});

export const sessions = pgTable("sessions", {
  id: text("id").primaryKey(), // sha256(token) hex — never the raw token
  userId: integer("user_id")
    .notNull()
    .references(() => users.id, { onDelete: "cascade" }),
  expiresAt: timestamp("expires_at").notNull(),
});

// Cache of engine output (source of truth = engine). Populated in admin phase.
export const evaluations = pgTable("evaluations", {
  id: serial("id").primaryKey(),
  laptopId: integer("laptop_id")
    .notNull()
    .unique()
    .references(() => laptops.id, { onDelete: "cascade" }),
  valueScore: smallint("value_score").notNull(),
  verdict: varchar("verdict", { length: 20 }).notNull(),
  fairPrice: integer("fair_price").notNull(),
  breakdown: jsonb("breakdown").notNull(),
  evaluatedAt: timestamp("evaluated_at").defaultNow().notNull(),
});
