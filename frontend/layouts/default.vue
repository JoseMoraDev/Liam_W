<!-- layouts/default.vue -->
<template>
  <div>
    <!-- Navbar global -->
    <GlassNav />

    <main class="pt-10">
      <slot />
    </main>
  </div>
</template>

<script setup>
import GlassNav from "~/components/GlassNav.vue";
</script>
