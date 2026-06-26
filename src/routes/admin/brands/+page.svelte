<script lang="ts">
  import type { PageData, ActionData } from './$types';
  let { data, form }: { data: PageData; form: ActionData } = $props();
</script>

<svelte:head><title>Brands — Admin</title></svelte:head>
<h1 class="mb-4 text-2xl font-bold">Brands</h1>

{#if form?.success}<p class="mb-3 rounded bg-success/15 p-2 text-sm text-success">{form.success}</p>{/if}
{#if form?.error}<p class="mb-3 rounded bg-error/15 p-2 text-sm text-error">{form.error}</p>{/if}

<form method="POST" action="?/create" class="mb-6 flex flex-wrap items-end gap-2">
  <label class="text-sm">Nama
    <input name="name" required class="mt-1 block rounded border border-overlay px-2 py-1" /></label>
  <label class="text-sm">Catatan
    <input name="notes" class="mt-1 block rounded border border-overlay px-2 py-1" /></label>
  <button class="rounded bg-accent px-3 py-1.5 text-sm font-medium text-bg">Tambah</button>
  {#if form?.action === 'create' && form?.errors}<span class="text-sm text-error">{Object.values(form.errors).join(', ')}</span>{/if}
</form>

<table class="w-full text-sm">
  <thead><tr class="text-left text-muted"><th class="py-1">Nama</th><th>Catatan</th><th></th></tr></thead>
  <tbody class="divide-y">
    {#each data.brands as b (b.id)}
      <tr>
        <td class="py-1.5">
          <form method="POST" action="?/update" class="flex items-center gap-2">
            <input type="hidden" name="id" value={b.id} />
            <input name="name" value={b.name} class="rounded border border-overlay px-2 py-1" />
            <input name="notes" value={b.notes ?? ''} class="rounded border border-overlay px-2 py-1" />
            <button class="rounded border border-overlay px-2 py-1 hover:bg-overlay">Simpan</button>
          </form>
        </td>
        <td></td>
        <td class="text-right">
          <form method="POST" action="?/delete" onsubmit={(e) => { if (!confirm('Hapus brand ini?')) e.preventDefault(); }}>
            <input type="hidden" name="id" value={b.id} />
            <button class="rounded border border-error/30 px-2 py-1 text-error hover:bg-error/15">Hapus</button>
          </form>
        </td>
      </tr>
    {/each}
  </tbody>
</table>
