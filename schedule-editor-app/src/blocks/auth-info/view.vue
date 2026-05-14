<script setup lang="ts">
import { useAuthStore } from "@/screens/auth/model/authStore";
import GoToEditSchedule from "@/units/go-to-edit-schedule/view.vue";
import GoToAdminPanel from "@/units/go-to-admin-panel/view.vue";
import { useRouter } from "vue-router";

const authStore = useAuthStore();
const router = useRouter();

const roleLabel: Record<string, string> = {
  ADMIN: "Администратор",
  EMPLOYEE: "Сотрудник",
};

async function handleLogout() {
  await authStore.logout();
  router.push("/portal");
}
</script>

<template>
  <div
    v-if="authStore.isAuthenticated && authStore.user"
    class="flex items-center gap-4"
  >
    <GoToAdminPanel />
    <GoToEditSchedule />
    <div class="text-right border-l border-gray-200 pl-3">
      <p class="text-sm font-medium text-gray-800">{{ authStore.user.name }}</p>
      <p class="text-xs text-gray-500">
        {{ roleLabel[authStore.user.role] ?? authStore.user.role }}
      </p>
    </div>
    <button
      class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
      @click="handleLogout"
    >
      Выход
    </button>
  </div>

  <div v-else>
    <router-link
      to="/login"
      class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
    >
      Вход для сотрудников
    </router-link>
  </div>
</template>
