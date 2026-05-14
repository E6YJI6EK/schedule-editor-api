<script setup lang="ts">
import type { Schedule, WeekTypeShort } from "@/types/schedule";
import { FileSpreadsheet, FileText } from "lucide-vue-next";
import WeekSwitcher from "./WeekSwitcher.vue";
import Logo from "@/units/logo/view.vue";
import AuthInfo from "@/blocks/auth-info/view.vue";
import { http } from "@/core/fetch-client/http";

interface Props {
  schedule: Schedule;
  currentWeek: WeekTypeShort;
  scheduleShown: boolean;
  groupIds: number[];
  switchWeek: () => void;
}

const { groupIds, switchWeek } = defineProps<Props>();

function buildGroupParams(): string {
  return groupIds.map((id) => `group_ids[]=${id}`).join("&");
}

async function downloadFile(url: string, filename: string): Promise<void> {
  const response = await http.get(url, { responseType: "blob" });
  const blob = new Blob([response.data as BlobPart]);
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = filename;
  link.click();
  URL.revokeObjectURL(link.href);
}

function exportToExcel(): void {
  const date = new Date().toLocaleDateString("ru-RU").replace(/\./g, "_");
  downloadFile(
    `/lessons/schedule/export/excel?${buildGroupParams()}`,
    `Расписание_${date}.xlsx`
  );
}

function exportToPDF(): void {
  const date = new Date().toLocaleDateString("ru-RU").replace(/\./g, "_");
  downloadFile(
    `/lessons/schedule/export/pdf?${buildGroupParams()}`,
    `Расписание_${date}.pdf`
  );
}
</script>

<template>
  <header class="bg-white border-b border-gray-200 shadow-sm">
    <div
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
    >
      <Logo />

      <div class="flex items-center gap-3">
        <div v-if="scheduleShown" class="flex items-center space-x-3">
          <button
            @click="exportToExcel"
            class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <FileSpreadsheet class="w-4 h-4 mr-2" />
            Excel
          </button>

          <button
            @click="exportToPDF"
            class="flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            <FileText class="w-4 h-4 mr-2" />
            PDF
          </button>

          <WeekSwitcher :currentWeek="currentWeek" :switchWeek="switchWeek" />
        </div>

        <!-- Auth section -->
        <AuthInfo />
      </div>
    </div>
  </header>
</template>
