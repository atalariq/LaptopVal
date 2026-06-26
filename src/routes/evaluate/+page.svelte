<script lang="ts">
  import type { ActionData, PageData } from './$types';
  import { score, type LaptopSpecs } from '$lib/scoring';
  import ScoreBreakdown from '$lib/components/ScoreBreakdown.svelte';
  import { REGION_KEYS, regionLabel } from '$lib/format';
  let { form, data }: { form: ActionData; data: PageData } = $props();

  let specs = $state<LaptopSpecs>({
    cpuTier: 2, ramGb: 8, storageGb: 256, condition: 3,
    hasWarranty: false, releaseYear: 2020, price: 4000, location: ''
  });
  // Use the DB active config (from load) so the live preview matches the server result.
  const live = $derived(score({ ...specs, location: specs.location || undefined }, data.config));
</script>

<svelte:head><title>Cek Laptop — LaptopVal</title></svelte:head>

<h1 class="text-2xl font-bold">Cek Laptop Bekas</h1>
<p class="text-muted">Masukkan spesifikasi & harga, lihat verdict-nya langsung.</p>

<div class="mt-4 grid gap-6 md:grid-cols-2">
  <form method="POST" class="space-y-3">
    <label class="block text-sm">CPU tier
      <select name="cpuTier" bind:value={specs.cpuTier} class="mt-1 block w-full rounded border border-overlay px-2 py-1">
        <option value={1}>Low</option><option value={2}>Mid</option><option value={3}>High</option>
      </select>
    </label>
    <label class="block text-sm">RAM (GB)
      <input type="number" name="ramGb" bind:value={specs.ramGb} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
    <label class="block text-sm">Storage (GB)
      <input type="number" name="storageGb" bind:value={specs.storageGb} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
    <label class="block text-sm">Kondisi
      <select name="condition" bind:value={specs.condition} class="mt-1 block w-full rounded border border-overlay px-2 py-1">
        <option value={1}>Buruk</option><option value={2}>Cukup</option><option value={3}>Baik</option><option value={4}>Mulus</option>
      </select>
    </label>
    <label class="block text-sm">Tahun rilis
      <input type="number" name="releaseYear" bind:value={specs.releaseYear} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
    <label class="block text-sm">Harga (ribuan IDR, mis. 4000 = Rp 4jt)
      <input type="number" name="price" bind:value={specs.price} class="mt-1 block w-full rounded border border-overlay px-2 py-1" /></label>
    <label class="block text-sm">Lokasi
      <select name="location" bind:value={specs.location} class="mt-1 block w-full rounded border border-overlay px-2 py-1">
        <option value="">— pilih —</option>
        {#each REGION_KEYS as r}<option value={r}>{regionLabel(r)}</option>{/each}
      </select>
    </label>
    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="hasWarranty" bind:checked={specs.hasWarranty} /> Bergaransi</label>
    <button class="rounded bg-accent px-3 py-1.5 text-sm font-medium text-bg">Hitung (tanpa JS)</button>
    {#if form?.errors}<p class="text-sm text-error">Periksa kembali input: {Object.values(form.errors).join(', ')}</p>{/if}
  </form>

  <div>
    <ScoreBreakdown result={form?.result ?? live} />
    <p class="mt-2 text-xs text-muted">Verdict diperbarui otomatis saat kamu mengetik (JS aktif).</p>
  </div>
</div>
