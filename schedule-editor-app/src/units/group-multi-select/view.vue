<script setup lang="ts">
import { searchGroupsByName } from "@/api/groups";
import type { Group, Course, EducationForm } from "@/api/types";
import { computed, ref, watch } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { X } from "lucide-vue-next";

const props = defineProps<{
  modelValue: Group[];
  course?: Course;
  educationForm?: EducationForm;
  instituteId?: number;
  disciplineId?: number;
}>();

const emit = defineEmits<{
  "update:modelValue": [value: Group[]];
}>();

const searchQuery = ref("");
const options = ref<Group[]>([]);
const isOpen = ref(false);
const selectedGroups = ref<Group[]>(props.modelValue || []);

watch(
  () => props.modelValue,
  (newValue) => {
    selectedGroups.value = newValue || [];
  },
  { immediate: true }
);

const performSearch = async (query: string) => {
  if (query.length >= 2) {
    try {
      const response = await searchGroupsByName({
        name: query,
      });
      options.value = response.data || [];
    } catch (error) {
      options.value = [];
    }
  } else {
    options.value = [];
  }
};

const debouncedSearch = useDebounceFn(performSearch, 300);

watch(
  searchQuery,
  (query) => {
    debouncedSearch(query);
  },
  { immediate: false }
);

const filteredOptions = computed(() => {
  const selectedIds = selectedGroups.value.map((g) => g.id);
  return options.value.filter((group) => !selectedIds.includes(group.id));
});

const addGroup = (group: Group) => {
  if (!selectedGroups.value.find((g) => g.id === group.id)) {
    selectedGroups.value = [...selectedGroups.value, group];
    emit("update:modelValue", selectedGroups.value);
  }
  searchQuery.value = "";
  isOpen.value = false;
};

const removeGroup = (groupId: number) => {
  selectedGroups.value = selectedGroups.value.filter((g) => g.id !== groupId);
  emit("update:modelValue", selectedGroups.value);
};

const handleBlur = () => {
  window.setTimeout(() => {
    isOpen.value = false;
  }, 200);
};
</script>

<template>
  <div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">Выбрать группы</label>
    <div v-if="selectedGroups.length > 0" class="flex flex-wrap gap-2 mb-2">
      <div
        v-for="group in selectedGroups"
        :key="group.id"
        class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-md text-sm"
      >
        <span>{{ group.name }}</span>
        <button
          @click="removeGroup(group.id)"
          class="ml-1 hover:bg-blue-200 rounded-full p-0.5 transition-colors"
          type="button"
        >
          <X class="w-3 h-3" />
        </button>
      </div>
    </div>
    <div class="relative">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Введите название группы для поиска"
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        @focus="isOpen = true"
        @blur="handleBlur"
      />
      <div
        v-if="isOpen && filteredOptions.length > 0"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-[200px] overflow-auto"
      >
        <div
          v-for="group in filteredOptions"
          :key="group.id"
          @click="addGroup(group)"
          class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
        >
          {{ group.name }}
        </div>
      </div>
      <div
        v-else-if="isOpen && searchQuery.length >= 2 && filteredOptions.length === 0"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg"
      >
        <div class="px-3 py-2 text-gray-500">Не найдено</div>
      </div>
    </div>
  </div>
</template>
