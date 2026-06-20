<script lang="ts">
  import type { PageData, ActionData } from './$types';
  let { data, form }: { data: PageData; form: ActionData } = $props();
</script>

<svelte:head><title>Use Cases — Admin</title></svelte:head>
<h1 class="mb-4 text-2xl font-bold">Use Cases</h1>

{#if form?.success}<p class="mb-3 rounded bg-green-50 p-2 text-sm text-green-700">{form.success}</p>{/if}

<form method="POST" action="?/create" class="mb-6 flex flex-wrap items-end gap-2">
  <label class="text-sm">Nama<input name="name" required class="mt-1 block w-32 rounded border px-2 py-1" /></label>
  <label class="text-sm">Min RAM<input type="number" name="minRamGb" value="8" class="mt-1 block w-24 rounded border px-2 py-1" /></label>
  <label class="text-sm">Min CPU<input type="number" name="minCpuTier" value="1" min="1" max="3" class="mt-1 block w-20 rounded border px-2 py-1" /></label>
  <label class="text-sm">Min Storage<input type="number" name="minStorage" value="256" class="mt-1 block w-24 rounded border px-2 py-1" /></label>
  <button class="rounded bg-slate-900 px-3 py-1.5 text-sm font-medium text-white">Tambah</button>
  {#if form?.action === 'create' && form?.errors}<span class="text-sm text-red-600">{Object.values(form.errors).join(', ')}</span>{/if}
</form>

<table class="w-full text-sm">
  <thead><tr class="text-left text-slate-500"><th class="py-1">Nama</th><th>Min RAM</th><th>Min CPU</th><th>Min Storage</th><th></th></tr></thead>
  <tbody class="divide-y">
    {#each data.useCases as u (u.id)}
      <tr>
        <td colspan="4" class="py-1.5">
          <form method="POST" action="?/update" class="flex items-center gap-2">
            <input type="hidden" name="id" value={u.id} />
            <input name="name" value={u.name} class="w-32 rounded border px-2 py-1" />
            <input type="number" name="minRamGb" value={u.minRamGb} class="w-20 rounded border px-2 py-1" />
            <input type="number" name="minCpuTier" value={u.minCpuTier} min="1" max="3" class="w-16 rounded border px-2 py-1" />
            <input type="number" name="minStorage" value={u.minStorage} class="w-24 rounded border px-2 py-1" />
            <button class="rounded border px-2 py-1 hover:bg-slate-100">Simpan</button>
          </form>
        </td>
        <td class="text-right">
          <form method="POST" action="?/delete" onsubmit={(e) => { if (!confirm('Hapus use case ini?')) e.preventDefault(); }}>
            <input type="hidden" name="id" value={u.id} />
            <button class="rounded border border-red-300 px-2 py-1 text-red-700 hover:bg-red-50">Hapus</button>
          </form>
        </td>
      </tr>
    {/each}
  </tbody>
</table>
