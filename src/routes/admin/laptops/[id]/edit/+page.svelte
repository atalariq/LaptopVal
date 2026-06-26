<script lang="ts">
  import type { PageData, ActionData } from './$types';
  import LaptopForm from '$lib/components/LaptopForm.svelte';
  let { data, form }: { data: PageData; form: ActionData } = $props();
  const l = $derived(data.laptop);
  const initial = $derived({
    brandId: l.brandId, model: l.model, releaseYear: l.releaseYear, cpuTier: l.cpuTier,
    ramGb: l.ramGb, storageGb: l.storageGb, condition: l.condition, hasWarranty: l.hasWarranty,
    price: l.price, location: l.location, imagePath: l.imagePath ?? '', sourceUrl: l.sourceUrl ?? ''
  });
</script>

<svelte:head><title>Edit {l.model} — Admin</title></svelte:head>
<a href="/admin/laptops" class="text-sm text-muted hover:underline">← Kembali</a>
<h1 class="mb-4 mt-1 text-2xl font-bold">Edit Laptop</h1>
<LaptopForm brands={data.brands} config={data.config} {initial} errors={form?.errors} submitLabel="Update" />
