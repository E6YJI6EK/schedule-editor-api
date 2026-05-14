<script setup lang="ts">
import { TabsList, type TabsListProps } from 'reka-ui'
import { cn } from '@/core/lib/utils'
import type { HTMLAttributes } from 'vue'

interface Props extends TabsListProps {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <TabsList
    :class="cn('inline-flex items-center justify-center rounded-lg bg-gray-100 p-1 text-gray-500', props.class)"
    v-bind="props"
  >
    <slot />
  </TabsList>
</template>
