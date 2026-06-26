<script lang="ts">
  import type { ScoreResult } from '$lib/scoring';
  import type { LaptopRow } from '$lib/laptop';
  import VerdictBadge from './VerdictBadge.svelte';
  import { formatPrice, regionLabel, cpuLabel } from '$lib/format';
  let { laptop, result }: { laptop: LaptopRow; result: ScoreResult } = $props();
</script>

<div class="relative rounded-lg border border-overlay bg-surface p-4 transition hover:shadow-md">
  <!-- full-card link sits behind content -->
  <a href="/laptops/{laptop.id}" class="absolute inset-0 rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-accent focus-visible:outline-offset-2" aria-label="{laptop.brand} {laptop.model}"></a>
  <div class="relative">
    <div class="flex items-start justify-between gap-2">
      <div>
        <div class="text-xs text-muted">{laptop.brand}</div>
        <div class="font-semibold">{laptop.model}</div>
      </div>
      <VerdictBadge verdict={result.verdict} />
    </div>
    <div class="mt-3 text-lg font-bold">{formatPrice(laptop.price)}</div>
    <div class="mt-2 flex flex-wrap gap-1 text-xs text-muted">
      <span class="rounded bg-overlay px-1.5 py-0.5">{laptop.ramGb}GB RAM</span>
      <span class="rounded bg-overlay px-1.5 py-0.5">{laptop.storageGb}GB</span>
      <span class="rounded bg-overlay px-1.5 py-0.5">CPU {cpuLabel(laptop.cpuTier)}</span>
      <span class="rounded bg-overlay px-1.5 py-0.5">{laptop.releaseYear}</span>
      <span class="rounded bg-overlay px-1.5 py-0.5">{regionLabel(laptop.location)}</span>
    </div>
    <div class="mt-2 flex items-center justify-between text-sm">
      <span class="font-medium text-text">Skor {result.total}</span>
      <a href="/compare?ids={laptop.id}" class="relative text-accent hover:underline">Bandingkan</a>
    </div>
  </div>
</div>
