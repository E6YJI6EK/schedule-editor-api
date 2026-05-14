<script setup lang="ts">
import Logo from "@/units/logo/view.vue";
import AuthInfo from "@/blocks/auth-info/view.vue";


</script>

<template>
  <header class="bg-white border-b border-gray-200 shadow-sm">
    <div
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
    >
      <Logo />
      <AuthInfo />
    </div>
  </header>
</template>
