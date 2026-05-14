<script setup lang="ts">
import { useAuthStore } from "@/screens/auth/model/authStore";

const authStore = useAuthStore();

</script>

<template>
  <router-link
    v-if="authStore.isAdmin"
    to="/admin"
    class="px-3 py-1.5 text-sm text-purple-700 bg-purple-50 rounded-md hover:bg-purple-100 border border-purple-200 transition-colors"
  >
    Администрирование
  </router-link>
</template>
