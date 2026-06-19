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
      return "bg-green-100 text-green-800 border-green-300";
    case "Fair":
      return "bg-sky-100 text-sky-800 border-sky-300";
    case "Overpriced":
      return "bg-amber-100 text-amber-800 border-amber-300";
    case "Avoid":
      return "bg-red-100 text-red-800 border-red-300";
  }
}

export function cpuLabel(t: number): string {
  return (
    ({ 1: "Low", 2: "Mid", 3: "High" } as Record<number, string>)[t] ?? "-"
  );
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
