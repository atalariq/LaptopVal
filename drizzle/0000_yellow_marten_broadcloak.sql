CREATE TYPE "public"."region" AS ENUM('jabodetabek', 'jawa_barat', 'jawa_tengah', 'jawa_timur', 'sumatera', 'kalimantan', 'sulawesi', 'bali_nusra', 'lainnya');--> statement-breakpoint
CREATE TABLE "brands" (
	"id" serial PRIMARY KEY NOT NULL,
	"name" varchar(50) NOT NULL,
	"notes" text,
	CONSTRAINT "brands_name_unique" UNIQUE("name")
);
--> statement-breakpoint
CREATE TABLE "evaluations" (
	"id" serial PRIMARY KEY NOT NULL,
	"laptop_id" integer NOT NULL,
	"value_score" smallint NOT NULL,
	"verdict" varchar(20) NOT NULL,
	"fair_price" integer NOT NULL,
	"breakdown" jsonb NOT NULL,
	"evaluated_at" timestamp DEFAULT now() NOT NULL,
	CONSTRAINT "evaluations_laptop_id_unique" UNIQUE("laptop_id")
);
--> statement-breakpoint
CREATE TABLE "laptops" (
	"id" serial PRIMARY KEY NOT NULL,
	"brand_id" integer NOT NULL,
	"model" varchar(100) NOT NULL,
	"release_year" smallint NOT NULL,
	"cpu_tier" smallint NOT NULL,
	"ram_gb" smallint NOT NULL,
	"storage_gb" integer NOT NULL,
	"condition" smallint NOT NULL,
	"has_warranty" boolean DEFAULT false NOT NULL,
	"price" integer NOT NULL,
	"location" "region" NOT NULL,
	"image_path" varchar(255),
	"source_url" varchar(255),
	"created_by" integer,
	"created_at" timestamp DEFAULT now() NOT NULL,
	"updated_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "scoring_config" (
	"id" serial PRIMARY KEY NOT NULL,
	"config" jsonb NOT NULL,
	"active" boolean DEFAULT true NOT NULL,
	"updated_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "use_cases" (
	"id" serial PRIMARY KEY NOT NULL,
	"name" varchar(50) NOT NULL,
	"min_ram_gb" smallint NOT NULL,
	"min_cpu_tier" smallint NOT NULL,
	"min_storage" integer NOT NULL
);
--> statement-breakpoint
CREATE TABLE "users" (
	"id" serial PRIMARY KEY NOT NULL,
	"username" varchar(50) NOT NULL,
	"password_hash" text NOT NULL,
	"created_at" timestamp DEFAULT now() NOT NULL,
	CONSTRAINT "users_username_unique" UNIQUE("username")
);
--> statement-breakpoint
ALTER TABLE "evaluations" ADD CONSTRAINT "evaluations_laptop_id_laptops_id_fk" FOREIGN KEY ("laptop_id") REFERENCES "public"."laptops"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "laptops" ADD CONSTRAINT "laptops_brand_id_brands_id_fk" FOREIGN KEY ("brand_id") REFERENCES "public"."brands"("id") ON DELETE no action ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "laptops" ADD CONSTRAINT "laptops_created_by_users_id_fk" FOREIGN KEY ("created_by") REFERENCES "public"."users"("id") ON DELETE set null ON UPDATE no action;