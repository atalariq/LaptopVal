<script lang="ts">
  import type { PageData, ActionData } from './$types';
  let { data, form }: { data: PageData; form: ActionData } = $props();
</script>

<svelte:head><title>Brands — Admin</title></svelte:head>
<h1 class="mb-4 text-2xl font-bold">Brands</h1>

{#if form?.success}<p class="mb-3 rounded bg-green-50 p-2 text-sm text-green-700">{form.success}</p>{/if}
{#if form?.error}<p class="mb-3 rounded bg-red-50 p-2 text-sm text-red-700">{form.error}</p>{/if}

<form method="POST" action="?/create" class="mb-6 flex flex-wrap items-end gap-2">
  <label class="text-sm">Nama
    <input name="name" required class="mt-1 block rounded border px-2 py-1" /></label>
  <label class="text-sm">Catatan
    <input name="notes" class="mt-1 block rounded border px-2 py-1" /></label>
  <button class="rounded bg-slate-900 px-3 py-1.5 text-sm font-medium text-white">Tambah</button>
  {#if form?.action === 'create' && form?.errors}<span class="text-sm text-red-600">{Object.values(form.errors).join(', ')}</span>{/if}
</form>

<table class="w-full text-sm">
  <thead><tr class="text-left text-slate-500"><th class="py-1">Nama</th><th>Catatan</th><th></th></tr></thead>
  <tbody class="divide-y">
    {#each data.brands as b (b.id)}
      <tr>
        <td class="py-1.5">
          <form method="POST" action="?/update" class="flex items-center gap-2">
            <input type="hidden" name="id" value={b.id} />
            <input name="name" value={b.name} class="rounded border px-2 py-1" />
            <input name="notes" value={b.notes ?? ''} class="rounded border px-2 py-1" />
            <button class="rounded border px-2 py-1 hover:bg-slate-100">Simpan</button>
          </form>
        </td>
        <td></td>
        <td class="text-right">
          <form method="POST" action="?/delete" onsubmit={(e) => { if (!confirm('Hapus brand ini?')) e.preventDefault(); }}>
            <input type="hidden" name="id" value={b.id} />
            <button class="rounded border border-red-300 px-2 py-1 text-red-700 hover:bg-red-50">Hapus</button>
          </form>
        </td>
      </tr>
    {/each}
  </tbody>
</table>
