<script lang="ts">
  import type { PageData, ActionData } from './$types';
  let { data, form }: { data: PageData; form: ActionData } = $props();
  const c = $derived(data.config);
  const j = (v: unknown) => JSON.stringify(v, null, 2);
</script>

<svelte:head><title>Scoring — Admin</title></svelte:head>
<h1 class="mb-2 text-2xl font-bold">Editor Scoring</h1>
<p class="mb-4 text-sm text-muted">Bobot & threshold ini dipakai engine untuk menghitung skor & verdict.</p>

{#if form?.success}<p class="mb-3 rounded bg-success/15 p-2 text-sm text-success">{form.success}</p>{/if}
{#if form?.error}<p class="mb-3 rounded bg-error/15 p-2 text-sm text-error">{form.error}</p>{/if}

<form method="POST" class="space-y-4">
  <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
    <label class="text-sm">CPU / tier<input type="number" name="cpuPerTier" value={c.cpuPerTier} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
    <label class="text-sm">Kondisi / level<input type="number" name="conditionPerLevel" value={c.conditionPerLevel} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
    <label class="text-sm">Bonus garansi<input type="number" name="warrantyBonus" value={c.warrantyBonus} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
    <label class="text-sm">IDR / poin kualitas<input type="number" name="idrPerQualityPoint" value={c.price.idrPerQualityPoint} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
  </div>

  <div class="grid gap-3 md:grid-cols-2">
    <label class="text-sm">RAM bands (JSON)<textarea name="ram" rows="5" class="mt-1 block w-full rounded border border-overlay px-2 py-1 font-mono text-xs">{j(c.ram)}</textarea></label>
    <label class="text-sm">Storage bands (JSON)<textarea name="storage" rows="5" class="mt-1 block w-full rounded border border-overlay px-2 py-1 font-mono text-xs">{j(c.storage)}</textarea></label>
    <label class="text-sm">Age penalty (JSON)<textarea name="agePenalty" rows="4" class="mt-1 block w-full rounded border border-overlay px-2 py-1 font-mono text-xs">{j(c.agePenalty)}</textarea></label>
    <label class="text-sm">Price bands (JSON)<textarea name="priceBands" rows="5" class="mt-1 block w-full rounded border border-overlay px-2 py-1 font-mono text-xs">{j(c.price.bands)}</textarea></label>
    <label class="text-sm">Region multipliers (JSON)<textarea name="regionMultipliers" rows="4" class="mt-1 block w-full rounded border border-overlay px-2 py-1 font-mono text-xs">{j(c.price.regionMultipliers)}</textarea></label>
    <label class="text-sm">Verdict bands (JSON)<textarea name="verdictBands" rows="5" class="mt-1 block w-full rounded border border-overlay px-2 py-1 font-mono text-xs">{j(c.verdictBands)}</textarea></label>
  </div>

  <button class="rounded bg-accent px-4 py-2 text-sm font-medium text-bg">Simpan konfigurasi</button>
</form>
