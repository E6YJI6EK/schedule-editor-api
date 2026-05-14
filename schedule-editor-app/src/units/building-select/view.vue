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
import { searchBuildings } from "@/api/buildings";
import type { Building } from "@/api/types";
import { computed, onMounted, ref } from "vue";

const props = defineProps<{
  modelValue: Building | null;
}>();

const emit = defineEmits<{
  "update:modelValue": [value: Building | null];
}>();
const options = ref<Building[]>();
onMounted(async () => {
  options.value = (await searchBuildings()).data;
});

const selectedValue = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});
</script>

<template>
  <div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">Корпус</label>
    <Combobox v-model="selectedValue">
      <ComboboxAnchor class="w-full">
        <ComboboxTrigger class="w-full">
          <ComboboxInput
            :display-value="(val) => val.name"
            placeholder="Выберите или введите корпус"
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
            v-for="building in options"
            :key="building.id"
            :value="building"
          >
            {{ building.name }}
          </ComboboxItem>
        </ComboboxGroup>
      </ComboboxList>
    </Combobox>
  </div>
</template>
