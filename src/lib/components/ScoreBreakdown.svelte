<script lang="ts">
  import type { ScoreResult } from '$lib/scoring';
  import VerdictBadge from './VerdictBadge.svelte';
  import { formatPrice } from '$lib/format';
  let { result }: { result: ScoreResult } = $props();
</script>

<div class="rounded-lg border border-overlay bg-surface p-4">
  <div class="flex items-baseline justify-between">
    <div class="text-4xl font-bold">{result.total}<span class="text-base text-muted">/100</span></div>
    <VerdictBadge verdict={result.verdict} />
  </div>
  <p class="mt-1 text-sm text-muted">Estimasi harga wajar: {formatPrice(result.fairPrice)}</p>
  <ul class="mt-4 space-y-2">
    {#each result.breakdown as f}
      <li class="text-sm">
        <div class="flex justify-between">
          <span>{f.label}</span>
          <span class="font-medium">{f.points}{#if f.max > 0}<span class="text-muted"> / {f.max}</span>{/if}</span>
        </div>
        {#if f.max > 0}
          <div class="mt-1 h-1.5 rounded bg-overlay">
            <div class="h-1.5 rounded bg-accent" style="width: {Math.max(0, Math.min(100, (f.points / f.max) * 100))}%"></div>
          </div>
        {/if}
      </li>
    {/each}
  </ul>
</div>
