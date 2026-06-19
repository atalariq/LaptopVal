<script lang="ts">
  import type { PageData } from './$types';
  import ScoreBreakdown from '$lib/components/ScoreBreakdown.svelte';
  import { formatPrice, regionLabel, cpuLabel, conditionLabel } from '$lib/format';
  let { data }: { data: PageData } = $props();
  const l = $derived(data.laptop);
</script>

<svelte:head><title>{l.brand} {l.model} — LaptopVal</title></svelte:head>

<a href="/" class="text-sm text-slate-500 hover:underline">← Kembali ke katalog</a>

<div class="mt-3 grid gap-6 md:grid-cols-2">
  <div>
    <div class="text-sm text-slate-500">{l.brand}</div>
    <h1 class="text-2xl font-bold">{l.model}</h1>
    <div class="mt-2 text-2xl font-bold">{formatPrice(l.price)}</div>
    {#if data.priceRange.count > 1}
      <p class="text-sm text-slate-500">
        Kisaran model ini: {formatPrice(data.priceRange.min)} – {formatPrice(data.priceRange.max)}
        ({data.priceRange.count} listing)
      </p>
    {/if}
    <table class="mt-4 w-full text-sm">
      <tbody class="divide-y">
        <tr><td class="py-1.5 text-slate-500">CPU</td><td class="text-right">{cpuLabel(l.cpuTier)}</td></tr>
        <tr><td class="py-1.5 text-slate-500">RAM</td><td class="text-right">{l.ramGb} GB</td></tr>
        <tr><td class="py-1.5 text-slate-500">Storage</td><td class="text-right">{l.storageGb} GB</td></tr>
        <tr><td class="py-1.5 text-slate-500">Kondisi</td><td class="text-right">{conditionLabel(l.condition)}</td></tr>
        <tr><td class="py-1.5 text-slate-500">Garansi</td><td class="text-right">{l.hasWarranty ? 'Ya' : 'Tidak'}</td></tr>
        <tr><td class="py-1.5 text-slate-500">Tahun</td><td class="text-right">{l.releaseYear}</td></tr>
        <tr><td class="py-1.5 text-slate-500">Lokasi</td><td class="text-right">{regionLabel(l.location)}</td></tr>
      </tbody>
    </table>
    {#if l.sourceUrl}<a href={l.sourceUrl} class="mt-3 inline-block text-sm text-sky-600 hover:underline">Lihat sumber listing →</a>{/if}
  </div>
  <ScoreBreakdown result={data.result} />
</div>
