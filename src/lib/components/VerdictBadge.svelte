<script lang="ts">
  import type { Verdict } from '$lib/scoring';
  import { verdictColor } from '$lib/format';
  let { verdict }: { verdict: Verdict } = $props();
</script>

<span class="inline-block rounded-full border px-2.5 py-0.5 text-xs font-semibold {verdictColor(verdict)}">
  {verdict}
</span>
