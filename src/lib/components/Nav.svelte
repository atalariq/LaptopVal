<script lang="ts">
  import ThemeToggle from './ThemeToggle.svelte';
</script>

<nav class="border-b border-overlay bg-surface">
  <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
    <a href="/" class="text-lg font-bold">LaptopVal</a>
    <div class="flex items-center gap-4 text-sm">
      <a href="/" class="hover:underline">Katalog</a>
      <a href="/evaluate" class="hover:underline">Cek Laptop</a>
      <a href="/login" class="text-muted hover:underline">Admin</a>
      <ThemeToggle />
    </div>
  </div>
</nav>
