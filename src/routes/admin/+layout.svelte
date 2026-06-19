<script lang="ts">
  import { page } from '$app/state';
  let { data, children } = $props();
  const links = [
    { href: '/admin', label: 'Dashboard' },
    { href: '/admin/laptops', label: 'Laptops' },
    { href: '/admin/brands', label: 'Brands' },
    { href: '/admin/use-cases', label: 'Use Cases' },
    { href: '/admin/scoring', label: 'Scoring' }
  ];
</script>

<div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b pb-3">
  <nav class="flex flex-wrap gap-3 text-sm">
    {#each links as l}
      <a href={l.href} class="rounded px-2 py-1 {page.url.pathname === l.href ? 'bg-slate-900 text-white' : 'hover:bg-slate-100'}">{l.label}</a>
    {/each}
  </nav>
  <form method="POST" action="/logout" class="flex items-center gap-2 text-sm">
    <span class="text-slate-500">{data.user.username}</span>
    <button class="rounded border px-2 py-1 hover:bg-slate-100">Logout</button>
  </form>
</div>

{@render children()}
