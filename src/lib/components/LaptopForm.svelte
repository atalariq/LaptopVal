<script lang="ts">
  import type { ScoringConfig, LaptopSpecs } from '$lib/scoring';
  import { score } from '$lib/scoring';
  import ScoreBreakdown from './ScoreBreakdown.svelte';
  import { REGION_KEYS, regionLabel } from '$lib/format';

  type Vals = {
    brandId: number; model: string; releaseYear: number; cpuTier: number; ramGb: number;
    storageGb: number; condition: number; hasWarranty: boolean; price: number;
    location: string; imagePath: string; sourceUrl: string;
  };
  let {
    brands, config, initial, errors, submitLabel
  }: {
    brands: { id: number; name: string }[]; config: ScoringConfig;
    initial?: Partial<Vals>; errors?: Record<string, string>; submitLabel: string;
  } = $props();

  let v = $state<Vals>({
    brandId: initial?.brandId ?? brands[0]?.id ?? 0,
    model: initial?.model ?? '', releaseYear: initial?.releaseYear ?? 2020,
    cpuTier: initial?.cpuTier ?? 2, ramGb: initial?.ramGb ?? 8, storageGb: initial?.storageGb ?? 256,
    condition: initial?.condition ?? 3, hasWarranty: initial?.hasWarranty ?? false,
    price: initial?.price ?? 4000, location: initial?.location ?? REGION_KEYS[0],
    imagePath: initial?.imagePath ?? '', sourceUrl: initial?.sourceUrl ?? ''
  });

  const specs = $derived<LaptopSpecs>({
    cpuTier: v.cpuTier as LaptopSpecs['cpuTier'], ramGb: v.ramGb, storageGb: v.storageGb,
    condition: v.condition as LaptopSpecs['condition'], hasWarranty: v.hasWarranty,
    releaseYear: v.releaseYear, price: v.price, location: v.location
  });
  const preview = $derived(score(specs, config));
</script>

<div class="grid gap-6 md:grid-cols-2">
  <form method="POST" class="space-y-3">
    {#if errors?._}<p class="rounded bg-error/15 p-2 text-sm text-error">{errors._}</p>{/if}
    <label class="block text-sm">Brand
      <select name="brandId" bind:value={v.brandId} class="mt-1 block w-full rounded border border-overlay px-2 py-1">
        {#each brands as b}<option value={b.id}>{b.name}</option>{/each}
      </select></label>
    <label class="block text-sm">Model<input name="model" bind:value={v.model} class="mt-1 block w-full rounded border border-overlay px-2 py-1" />
      {#if errors?.model}<span class="text-xs text-error">{errors.model}</span>{/if}</label>
    <div class="grid grid-cols-2 gap-2">
      <label class="block text-sm">Tahun<input type="number" name="releaseYear" bind:value={v.releaseYear} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
      <label class="block text-sm">Harga (ribuan IDR)<input type="number" name="price" bind:value={v.price} class="mt-1 block w-full rounded border border-overlay px-2 py-1" />
        {#if errors?.price}<span class="text-xs text-error">{errors.price}</span>{/if}</label>
      <label class="block text-sm">CPU tier
        <select name="cpuTier" bind:value={v.cpuTier} class="mt-1 block w-full rounded border border-overlay px-2 py-1">
          <option value={1}>Low</option><option value={2}>Mid</option><option value={3}>High</option></select></label>
      <label class="block text-sm">Kondisi
        <select name="condition" bind:value={v.condition} class="mt-1 block w-full rounded border border-overlay px-2 py-1">
          <option value={1}>Buruk</option><option value={2}>Cukup</option><option value={3}>Baik</option><option value={4}>Mulus</option></select></label>
      <label class="block text-sm">RAM (GB)<input type="number" name="ramGb" bind:value={v.ramGb} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
      <label class="block text-sm">Storage (GB)<input type="number" name="storageGb" bind:value={v.storageGb} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
    </div>
    <label class="block text-sm">Lokasi
      <select name="location" bind:value={v.location} class="mt-1 block w-full rounded border border-overlay px-2 py-1">
        {#each REGION_KEYS as r}<option value={r}>{regionLabel(r)}</option>{/each}</select></label>
    <label class="block text-sm">Image URL (opsional)<input name="imagePath" bind:value={v.imagePath} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
    <label class="block text-sm">Source URL (opsional)<input name="sourceUrl" bind:value={v.sourceUrl} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="hasWarranty" bind:checked={v.hasWarranty} /> Bergaransi</label>
    <button class="rounded bg-accent px-3 py-2 text-sm font-medium text-bg">{submitLabel}</button>
  </form>

  <div class="md:sticky md:top-4 md:self-start">
    <h2 class="mb-2 text-sm font-semibold text-muted">Preview skor</h2>
    <ScoreBreakdown result={preview} />
  </div>
</div>
