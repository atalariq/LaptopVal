<script lang="ts">
  import type { PageData, ActionData } from './$types';
  import VerdictBadge from '$lib/components/VerdictBadge.svelte';
  import { formatPrice } from '$lib/format';
  let { data, form }: { data: PageData; form: ActionData } = $props();
</script>

<svelte:head><title>Laptops — Admin</title></svelte:head>
<div class="mb-4 flex items-center justify-between">
  <h1 class="text-2xl font-bold">Laptops</h1>
  <a href="/admin/laptops/new" class="rounded bg-slate-900 px-3 py-1.5 text-sm font-medium text-white">+ Tambah Laptop</a>
</div>
{#if form?.success}<p class="mb-3 rounded bg-green-50 p-2 text-sm text-green-700">{form.success}</p>{/if}
{#if form?.error}<p class="mb-3 rounded bg-red-50 p-2 text-sm text-red-700">{form.error}</p>{/if}

<table class="w-full text-sm">
  <thead><tr class="text-left text-slate-500"><th class="py-1">Model</th><th>Brand</th><th>Harga</th><th>Skor</th><th>Verdict</th><th></th></tr></thead>
  <tbody class="divide-y">
    {#each data.items as { laptop, result } (laptop.id)}
      <tr>
        <td class="py-1.5">{laptop.model}</td>
        <td>{laptop.brand}</td>
        <td>{formatPrice(laptop.price)}</td>
        <td>{result.total}</td>
        <td><VerdictBadge verdict={result.verdict} /></td>
        <td class="text-right">
          <a href="/admin/laptops/{laptop.id}/edit" class="rounded border px-2 py-1 hover:bg-slate-100">Edit</a>
          <form method="POST" action="?/delete" class="inline" onsubmit={(e) => { if (!confirm('Hapus laptop ini?')) e.preventDefault(); }}>
            <input type="hidden" name="id" value={laptop.id} />
            <button class="rounded border border-red-300 px-2 py-1 text-red-700 hover:bg-red-50">Hapus</button>
          </form>
        </td>
      </tr>
    {/each}
  </tbody>
</table>
