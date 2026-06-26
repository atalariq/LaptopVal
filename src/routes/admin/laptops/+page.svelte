<script lang="ts">
  import type { PageData, ActionData } from './$types';
  import VerdictBadge from '$lib/components/VerdictBadge.svelte';
  import { formatPrice } from '$lib/format';
  let { data, form }: { data: PageData; form: ActionData } = $props();
</script>

<svelte:head><title>Laptops — Admin</title></svelte:head>
<div class="mb-4 flex items-center justify-between">
  <h1 class="text-2xl font-bold">Laptops</h1>
  <a href="/admin/laptops/new" class="rounded bg-accent px-3 py-1.5 text-sm font-medium text-bg">+ Tambah Laptop</a>
</div>
{#if form?.success}<p class="mb-3 rounded bg-success/15 p-2 text-sm text-success">{form.success}</p>{/if}
{#if form?.error}<p class="mb-3 rounded bg-error/15 p-2 text-sm text-error">{form.error}</p>{/if}

<table class="w-full text-sm">
  <thead><tr class="text-left text-muted"><th class="py-1">Model</th><th>Brand</th><th>Harga</th><th>Skor</th><th>Verdict</th><th></th></tr></thead>
  <tbody class="divide-y">
    {#each data.items as { laptop, result } (laptop.id)}
      <tr>
        <td class="py-1.5">{laptop.model}</td>
        <td>{laptop.brand}</td>
        <td>{formatPrice(laptop.price)}</td>
        <td>{result.total}</td>
        <td><VerdictBadge verdict={result.verdict} /></td>
        <td class="text-right">
          <a href="/admin/laptops/{laptop.id}/edit" class="rounded border border-overlay px-2 py-1 hover:bg-overlay">Edit</a>
          <form method="POST" action="?/delete" class="inline" onsubmit={(e) => { if (!confirm('Hapus laptop ini?')) e.preventDefault(); }}>
            <input type="hidden" name="id" value={laptop.id} />
            <button class="rounded border border-error/30 px-2 py-1 text-error hover:bg-error/15">Hapus</button>
          </form>
        </td>
      </tr>
    {/each}
  </tbody>
</table>
