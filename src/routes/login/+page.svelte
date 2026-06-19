<script lang="ts">
  import type { ActionData } from './$types';
  let { form }: { form: ActionData } = $props();
</script>

<svelte:head><title>Login Admin — LaptopVal</title></svelte:head>

<div class="mx-auto mt-10 max-w-sm rounded-lg border bg-white p-6">
  <h1 class="text-xl font-bold">Login Admin</h1>
  {#if form?.error}<p class="mt-2 rounded bg-red-50 p-2 text-sm text-red-700">{form.error}</p>{/if}
  <form method="POST" class="mt-4 space-y-3">
    <label class="block text-sm">Username
      <input name="username" value={form?.username ?? ''} autocomplete="username"
        class="mt-1 block w-full rounded border px-2 py-1" /></label>
    <label class="block text-sm">Password
      <input type="password" name="password" autocomplete="current-password"
        class="mt-1 block w-full rounded border px-2 py-1" /></label>
    <button class="w-full rounded bg-slate-900 px-3 py-2 text-sm font-medium text-white">Masuk</button>
  </form>
</div>
