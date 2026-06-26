<script lang="ts">
  import type { PageData } from './$types';
  import VerdictBadge from '$lib/components/VerdictBadge.svelte';
  import { formatPrice } from '$lib/format';
  let { data }: { data: PageData } = $props();
</script>

<svelte:head><title>Dashboard — LaptopVal Admin</title></svelte:head>
<h1 class="mb-4 text-2xl font-bold">Dashboard</h1>

<div class="grid grid-cols-3 gap-4">
  <div class="rounded-lg border border-overlay bg-surface p-4"><div class="text-sm text-muted">Total Laptop</div><div class="text-3xl font-bold">{data.totalLaptops}</div></div>
  <div class="rounded-lg border border-overlay bg-surface p-4"><div class="text-sm text-muted">Total Brand</div><div class="text-3xl font-bold">{data.totalBrands}</div></div>
  <div class="rounded-lg border border-overlay bg-surface p-4"><div class="text-sm text-muted">Rata-rata Skor</div><div class="text-3xl font-bold">{data.avgScore}</div></div>
</div>

<div class="mt-6 grid gap-6 md:grid-cols-2">
  <div class="rounded-lg border border-overlay bg-surface p-4">
    <h2 class="mb-2 font-semibold">Rata-rata skor per brand</h2>
    <table class="w-full text-sm">
      <thead><tr class="text-left text-muted"><th class="py-1">Brand</th><th>Avg</th><th>Listing</th></tr></thead>
      <tbody class="divide-y">
        {#each data.perBrand as b}<tr><td class="py-1">{b.brand}</td><td>{b.avg}</td><td>{b.count}</td></tr>{/each}
      </tbody>
    </table>
  </div>
  <div class="rounded-lg border border-overlay bg-surface p-4">
    <h2 class="mb-2 font-semibold">Top deals</h2>
    <ul class="divide-y text-sm">
      {#each data.topDeals as { laptop, result }}
        <li class="flex items-center justify-between py-1.5">
          <a href="/laptops/{laptop.id}" class="hover:underline">{laptop.brand} {laptop.model}</a>
          <span class="flex items-center gap-2">{formatPrice(laptop.price)} <VerdictBadge verdict={result.verdict} /></span>
        </li>
      {/each}
    </ul>
  </div>
</div>
