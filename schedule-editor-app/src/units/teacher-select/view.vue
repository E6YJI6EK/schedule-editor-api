<script setup lang="ts">
import {
  Combobox,
  ComboboxAnchor,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxInput,
  ComboboxItem,
  ComboboxList,
  ComboboxTrigger,
} from "@/core/ui/combobox";
import { searchTeachers } from "@/api/teachers";
import type { Teacher } from "@/api/types";
import { computed, ref, watch } from "vue";

const props = defineProps<{
  modelValue: Teacher | null;
  disciplineId?: number;
}>();

const emit = defineEmits<{
  "update:modelValue": [value: Teacher | null];
}>();

const options = ref<Teacher[]>();

watch(
  () => props.disciplineId,
  async (disciplineId) => {
    if (disciplineId) {
      options.value = (
        await searchTeachers({ discipline_id: disciplineId })
      ).data;
    }
  }
);

const selectedValue = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});
</script>

<template>
  <div v-if="disciplineId" class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">Преподаватель</label>
    <Combobox v-model="selectedValue">
      <ComboboxAnchor>
        <ComboboxTrigger class="w-full">
          <ComboboxInput
            :display-value="(val) => val.name"
            placeholder="Выберите или введите преподавателя"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </ComboboxTrigger>
      </ComboboxAnchor>
      <ComboboxList class="translate-y-[20px] w-[336px] max-h-[200px] overflow-auto">
        <ComboboxEmpty>Не найдено</ComboboxEmpty>
        <ComboboxGroup>
          <ComboboxItem
            v-for="teacher in options"
            :key="teacher.id"
            :value="teacher"
          >
            {{ teacher.name }}
          </ComboboxItem>
        </ComboboxGroup>
      </ComboboxList>
    </Combobox>
  </div>
</template>
