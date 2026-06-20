<script lang="ts">
  import type { PageData } from './$types';
  import type { LaptopRow } from '$lib/laptop';
  import type { ScoreResult } from '$lib/scoring/types';
  import VerdictBadge from '$lib/components/VerdictBadge.svelte';
  import { formatPrice } from '$lib/format';
  let { data }: { data: PageData } = $props();

  type TierKey = 's' | 'a' | 'b' | 'c' | 'd';
  const TIER_KEYS: TierKey[] = ['s', 'a', 'b', 'c', 'd'];
  const TIER_LABELS: Record<TierKey, string> = { s: 'S', a: 'A', b: 'B', c: 'C', d: 'D' };
  const TIER_COLORS: Record<TierKey, string> = {
    s: 'bg-yellow-100 border-yellow-300 text-yellow-900',
    a: 'bg-green-100 border-green-300 text-green-900',
    b: 'bg-sky-100 border-sky-300 text-sky-900',
    c: 'bg-orange-100 border-orange-300 text-orange-900',
    d: 'bg-red-100 border-red-300 text-red-900',
  };

  type Entry = { laptop: LaptopRow; result: ScoreResult };

  const entries = data.entries;
  const entryMap = new Map<number, Entry>(
    entries.map((e) => [e.laptop.id, e as Entry])
  );

  const initialTiers = data.tiers;
  let tiers = $state<Record<TierKey, number[]>>({
    s: [...(initialTiers.s ?? [])],
    a: [...(initialTiers.a ?? [])],
    b: [...(initialTiers.b ?? [])],
    c: [...(initialTiers.c ?? [])],
    d: [...(initialTiers.d ?? [])],
  });
  let dragging = $state<{ id: number; fromTier: TierKey } | null>(null);
  let copied = $state(false);

  function getEntries(tier: TierKey) {
    return tiers[tier].flatMap((id: number) => {
      const e = entryMap.get(id);
      return e ? [e] : [];
    });
  }

  function syncUrl() {
    const params = new URLSearchParams(
      TIER_KEYS.map((k) => [k, tiers[k].join(',')])
    );
    history.replaceState(null, '', `?${params}`);
  }

  function onDragStart(e: DragEvent, id: number, fromTier: TierKey) {
    dragging = { id, fromTier };
    e.dataTransfer!.effectAllowed = 'move';
    e.dataTransfer!.setData('text/plain', String(id));
  }

  function onDragOver(e: DragEvent) {
    e.preventDefault();
    e.dataTransfer!.dropEffect = 'move';
  }

  function onDrop(e: DragEvent, toTier: TierKey) {
    e.preventDefault();
    if (!dragging || dragging.fromTier === toTier) { dragging = null; return; }
    const { id, fromTier } = dragging;
    tiers[fromTier] = tiers[fromTier].filter((x: number) => x !== id);
    tiers[toTier] = [...tiers[toTier], id];
    syncUrl();
    dragging = null;
  }

  function onDragEnd() {
    dragging = null;
  }

  async function copyUrl() {
    await navigator.clipboard.writeText(window.location.href);
    copied = true;
    setTimeout(() => { copied = false; }, 2000);
  }
</script>

<svelte:head><title>Tier List — LaptopVal</title></svelte:head>

<div class="mb-4 flex items-center justify-between">
  <div>
    <a href="/" class="text-sm text-slate-500 hover:underline">← Katalog</a>
    <h1 class="mt-1 text-2xl font-bold">Tier List Laptop</h1>
    <p class="text-sm text-slate-500">Drag & drop laptop antar tier. S = terbaik, D = hindari.</p>
  </div>
  <div class="flex gap-2">
    <a href="/tier-list" class="rounded border px-3 py-1.5 text-sm hover:bg-slate-100">Reset</a>
    <button onclick={copyUrl} class="rounded bg-slate-900 px-3 py-1.5 text-sm font-medium text-white">
      {copied ? 'Tersalin!' : 'Salin URL'}
    </button>
  </div>
</div>

<div class="space-y-3">
  {#each TIER_KEYS as tier (tier)}
    <div class="flex gap-3">
      <div class="flex w-10 flex-shrink-0 items-center justify-center rounded border text-xl font-bold {TIER_COLORS[tier]}">
        {TIER_LABELS[tier]}
      </div>
      <!-- svelte-ignore a11y_no_static_element_interactions -->
      <div
        class="flex min-h-20 flex-1 flex-wrap gap-2 rounded border border-dashed p-2 transition
          {dragging ? 'border-sky-400 bg-sky-50' : 'border-slate-300'}"
        ondragover={onDragOver}
        ondrop={(e) => onDrop(e, tier)}
      >
        {#each getEntries(tier) as { laptop, result } (laptop.id)}
          <!-- svelte-ignore a11y_no_static_element_interactions -->
          <div
            draggable="true"
            class="cursor-grab rounded border bg-white p-2 text-xs shadow-sm select-none active:cursor-grabbing"
            ondragstart={(e) => onDragStart(e, laptop.id, tier)}
            ondragend={onDragEnd}
          >
            <div class="font-semibold">{laptop.brand} {laptop.model}</div>
            <div class="text-slate-500">{formatPrice(laptop.price)}</div>
            <div class="mt-1 flex items-center gap-1">
              <span class="text-slate-700">Skor {result.total}</span>
              <VerdictBadge verdict={result.verdict} />
            </div>
          </div>
        {/each}
        {#if getEntries(tier).length === 0}
          <p class="self-center text-xs text-slate-400">Drop laptop ke sini</p>
        {/if}
      </div>
    </div>
  {/each}
</div>
