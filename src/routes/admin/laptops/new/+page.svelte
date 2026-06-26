<script lang="ts">
  import type { PageData, ActionData } from './$types';
  import LaptopForm from '$lib/components/LaptopForm.svelte';
  let { data, form }: { data: PageData; form: ActionData } = $props();
</script>

<svelte:head><title>Tambah Laptop — Admin</title></svelte:head>
<a href="/admin/laptops" class="text-sm text-muted hover:underline">← Kembali</a>
<h1 class="mb-4 mt-1 text-2xl font-bold">Tambah Laptop</h1>
<LaptopForm brands={data.brands} config={data.config} errors={form?.errors} submitLabel="Simpan" />
