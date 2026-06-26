<script lang="ts">
  import type { PageData } from './$types';
  import VerdictBadge from '$lib/components/VerdictBadge.svelte';
  import { formatPrice, regionLabel, gpuLabel, conditionLabel } from '$lib/format';
  let { data }: { data: PageData } = $props();

  const best = $derived(
    data.items.length > 0
      ? data.items.reduce((b: typeof data.items[0], x: typeof data.items[0]) =>
          x.result.total > b.result.total ? x : b, data.items[0])
      : null
  );

  function removeUrl(id: number): string {
    return `/compare?ids=${data.ids.filter((x: number) => x !== id).join(',')}`;
  }
</script>

<svelte:head><title>Bandingkan Laptop — LaptopVal</title></svelte:head>

<div class="mb-4 flex items-center justify-between">
  <div>
    <a href="/" class="text-sm text-muted hover:underline">← Katalog</a>
    <h1 class="mt-1 text-2xl font-bold">Bandingkan Laptop</h1>
  </div>
  {#if data.addable.length > 0 && data.ids.length < 4}
    <div class="flex items-center gap-2 text-sm">
      <select
        class="rounded border border-overlay px-2 py-1"
        onchange={(e) => {
          const v = (e.currentTarget as HTMLSelectElement).value;
          if (v) window.location.href = `/compare?ids=${[...data.ids, Number(v)].join(',')}`;
        }}
      >
        <option value="">+ Tambah laptop</option>
        {#each data.addable as a (a.id)}
          <option value={a.id}>{a.label}</option>
        {/each}
      </select>
    </div>
  {/if}
</div>

{#if data.items.length === 0}
  <div class="rounded-lg border border-overlay bg-surface p-8 text-center">
    <p class="text-muted">Belum ada laptop dipilih untuk dibandingkan.</p>
    <a href="/" class="mt-3 inline-block text-sm text-accent hover:underline">Pilih dari katalog →</a>
  </div>
{:else}
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr>
          <th class="w-32 py-2 text-left text-muted">Spesifikasi</th>
          {#each data.items as { laptop, result } (laptop.id)}
            <th class="px-3 py-2 text-left {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">
              <div class="font-semibold">{laptop.brand} {laptop.model}</div>
              <div class="text-lg font-bold">{formatPrice(laptop.price)}</div>
              <a href={removeUrl(laptop.id)} class="text-xs text-error hover:underline">Hapus</a>
            </th>
          {/each}
        </tr>
      </thead>
      <tbody class="divide-y">
        <tr class="bg-bg font-semibold">
          <td class="py-2 text-muted">Skor</td>
          {#each data.items as { laptop, result } (laptop.id)}
            <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">
              <span class="text-2xl">{result.total}</span><span class="text-muted">/100</span>
            </td>
          {/each}
        </tr>
        <tr>
          <td class="py-2 text-muted">Verdict</td>
          {#each data.items as { laptop, result } (laptop.id)}
            <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">
              <VerdictBadge verdict={result.verdict} />
            </td>
          {/each}
        </tr>
        <tr>
          <td class="py-2 text-muted">Harga wajar</td>
          {#each data.items as { laptop, result } (laptop.id)}
            <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">{formatPrice(result.fairPrice)}</td>
          {/each}
        </tr>
        <tr>
          <td class="py-2 text-muted">CPU</td>
          {#each data.items as { laptop, result } (laptop.id)}
            <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">{laptop.cpuName}</td>
          {/each}
        </tr>
        <tr>
          <td class="py-2 text-muted">GPU</td>
          {#each data.items as { laptop, result } (laptop.id)}
            <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">{gpuLabel(laptop.gpuName)}</td>
          {/each}
        </tr>
        <tr>
          <td class="py-2 text-muted">RAM</td>
          {#each data.items as { laptop, result } (laptop.id)}
            <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">{laptop.ramGb} GB</td>
          {/each}
        </tr>
        <tr>
          <td class="py-2 text-muted">Storage</td>
          {#each data.items as { laptop, result } (laptop.id)}
            <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">{laptop.storageGb} GB</td>
          {/each}
        </tr>
        <tr>
          <td class="py-2 text-muted">Kondisi</td>
          {#each data.items as { laptop, result } (laptop.id)}
            <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">{conditionLabel(laptop.condition)}</td>
          {/each}
        </tr>
        <tr>
          <td class="py-2 text-muted">Garansi</td>
          {#each data.items as { laptop, result } (laptop.id)}
            <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">{laptop.hasWarranty ? 'Ya' : 'Tidak'}</td>
          {/each}
        </tr>
        <tr>
          <td class="py-2 text-muted">Tahun</td>
          {#each data.items as { laptop, result } (laptop.id)}
            <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">{laptop.releaseYear}</td>
          {/each}
        </tr>
        <tr>
          <td class="py-2 text-muted">Lokasi</td>
          {#each data.items as { laptop, result } (laptop.id)}
            <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">{regionLabel(laptop.location)}</td>
          {/each}
        </tr>
        <tr class="bg-bg">
          <td class="py-2 font-semibold text-muted" colspan={1 + data.items.length}>Per-faktor</td>
        </tr>
        {#each (data.items[0]?.result.breakdown ?? []) as factor, i (factor.label)}
          <tr>
            <td class="py-2 text-muted">{factor.label}</td>
            {#each data.items as { laptop, result } (laptop.id)}
              <td class="px-3 py-2 {best?.laptop.id === laptop.id ? 'bg-success/10' : ''}">
                {result.breakdown.find(f => f.label === factor.label)?.points ?? 0}{#if factor.max > 0}<span class="text-muted">/{factor.max}</span>{/if}
              </td>
            {/each}
          </tr>
        {/each}
      </tbody>
    </table>
  </div>
{/if}
