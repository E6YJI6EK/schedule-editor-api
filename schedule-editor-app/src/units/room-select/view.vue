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
import { searchClassRooms } from "@/api/classRooms";
import type { ClassRoom } from "@/api/types";
import { computed, ref, watch } from "vue";

const props = defineProps<{
  buildingId?: number;
  modelValue: ClassRoom | null;
}>();

const emit = defineEmits<{
  "update:modelValue": [value: ClassRoom | null];
}>();

const options = ref<ClassRoom[]>();

watch(
  () => props.buildingId,
  async (buildingId) => {
    if (buildingId) {
      options.value = (
        await searchClassRooms({ building_id: buildingId })
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
  <div v-if="buildingId" class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">Аудитория</label>
    <Combobox v-model="selectedValue">
      <ComboboxAnchor class="w-full">
        <ComboboxTrigger class="w-full">
          <ComboboxInput
            :display-value="(val) => val.number"
            placeholder="Выберите или введите аудиторию"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </ComboboxTrigger>
      </ComboboxAnchor>
      <ComboboxList
        class="translate-y-[20px] w-[336px] max-h-[200px] overflow-auto"
      >
        <ComboboxEmpty>Не найдено</ComboboxEmpty>
        <ComboboxGroup>
          <ComboboxItem v-for="room in options" :key="room.id" :value="room">
            {{ room.number }}
          </ComboboxItem>
        </ComboboxGroup>
      </ComboboxList>
    </Combobox>
  </div>
</template>
