<script setup lang="ts">
import { useRoute } from "vue-router";
import { ref, watch } from "vue";
const route = useRoute();

const isScheduleEditing = ref(false);

watch(
  () => route.path,
  (newPath) => {
    isScheduleEditing.value = newPath.includes("/schedule");
  },
  { immediate: true },
);
</script>

<template>
  <router-link
    v-if="!isScheduleEditing"
    to="/schedule"
    class="px-3 py-1.5 text-sm text-white rounded-md bg-blue-600 hover:bg-blue-700 border-blue-600 transition-colors"
  >
    Редактировать расписание
  </router-link>
</template>
