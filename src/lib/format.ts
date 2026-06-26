import type { Verdict } from "$lib/scoring";

export function formatPrice(thousands: number): string {
  return "Rp " + new Intl.NumberFormat("id-ID").format(thousands * 1000);
}

const REGIONS: Record<string, string> = {
  jabodetabek: "Jabodetabek",
  jawa_barat: "Jawa Barat",
  jawa_tengah: "Jawa Tengah",
  jawa_timur: "Jawa Timur",
  sumatera: "Sumatera",
  kalimantan: "Kalimantan",
  sulawesi: "Sulawesi",
  bali_nusra: "Bali & Nusra",
  lainnya: "Lainnya",
};
export function regionLabel(key: string): string {
  return REGIONS[key] ?? key;
}
export const REGION_KEYS = Object.keys(REGIONS);

export function verdictColor(verdict: Verdict): string {
  switch (verdict) {
    case "Great Deal":
      return "bg-success/15 text-success border-success/30";
    case "Fair":
      return "bg-info/15 text-info border-info/30";
    case "Overpriced":
      return "bg-warning/15 text-warning border-warning/30";
    case "Avoid":
      return "bg-error/15 text-error border-error/30";
  }
}

export function gpuLabel(name: string | null): string {
  return name ?? "Integrated";
}
export function conditionLabel(c: number): string {
  return (
    (
      { 1: "Buruk", 2: "Cukup", 3: "Baik", 4: "Mulus" } as Record<
        number,
        string
      >
    )[c] ?? "-"
  );
}
