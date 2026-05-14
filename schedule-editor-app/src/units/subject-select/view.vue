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
import { searchDisciplines } from "@/api/disciplines";
import type { Discipline } from "@/api/types";
import { computed, onMounted, ref } from "vue";

const props = defineProps<{
  modelValue: Discipline | null;
}>();

const emit = defineEmits<{
  "update:modelValue": [value: Discipline | null];
}>();

const options = ref<Discipline[]>();

onMounted(async () => {
  options.value = (await searchDisciplines()).data;
});

const selectedValue = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});
</script>

<template>
  <div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">Предмет</label>
    <Combobox v-model="selectedValue">
      <ComboboxAnchor class="w-full">
        <ComboboxTrigger class="w-full">
          <ComboboxInput
            :display-value="(val) => val.name"
            placeholder="Выберите или введите предмет"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </ComboboxTrigger>
      </ComboboxAnchor>
      <ComboboxList
        class="translate-y-[20px] w-[336px] max-h-[200px] overflow-auto"
      >
        <ComboboxEmpty>Не найдено</ComboboxEmpty>
        <ComboboxGroup>
          <ComboboxItem
            v-for="subject in options"
            :key="subject.id"
            :value="subject"
          >
            {{ subject.name }}
          </ComboboxItem>
        </ComboboxGroup>
      </ComboboxList>
    </Combobox>
  </div>
</template>
