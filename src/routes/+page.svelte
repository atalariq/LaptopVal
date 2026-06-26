<script lang="ts">
  import type { PageData } from './$types';
  import LaptopCard from '$lib/components/LaptopCard.svelte';
  import { regionLabel } from '$lib/format';
  let { data }: { data: PageData } = $props();
</script>

<svelte:head><title>LaptopVal — Katalog</title></svelte:head>

<section class="mb-6">
  <h1 class="text-2xl font-bold">Katalog Laptop Bekas</h1>
  <p class="text-muted">Skor & verdict transparan berdasarkan spesifikasi dan harga.</p>
</section>

<form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
  <label class="text-sm">Cari
    <input name="q" value={data.filters.search} placeholder="model / brand"
      class="mt-1 block rounded border border-overlay px-2 py-1" />
  </label>
  <label class="text-sm">Use case
    <select name="use_case" class="mt-1 block rounded border border-overlay px-2 py-1">
      <option value="0">Semua</option>
      {#each data.useCases as uc}
        <option value={uc.id} selected={uc.id === data.filters.useCaseId}>{uc.name}</option>
      {/each}
    </select>
  </label>
  <label class="text-sm">Lokasi
    <select name="location" class="mt-1 block rounded border border-overlay px-2 py-1">
      <option value="">Semua</option>
      {#each data.regions as r}
        <option value={r} selected={r === data.filters.location}>{regionLabel(r)}</option>
      {/each}
    </select>
  </label>
  <label class="text-sm">Urut
    <select name="sort" class="mt-1 block rounded border border-overlay px-2 py-1">
      <option value="score_desc" selected={data.filters.sort === 'score_desc'}>Skor tertinggi</option>
      <option value="score_asc" selected={data.filters.sort === 'score_asc'}>Skor terendah</option>
    </select>
  </label>
  <button class="rounded bg-accent px-3 py-1.5 text-sm font-medium text-bg">Terapkan</button>
</form>

{#if data.items.length === 0}
  <p class="text-muted">Tidak ada laptop yang cocok dengan filter.</p>
{:else}
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    {#each data.items as { laptop, result } (laptop.id)}
      <LaptopCard {laptop} {result} />
    {/each}
  </div>
{/if}
