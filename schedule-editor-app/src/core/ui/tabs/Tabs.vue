<script setup lang="ts">
import { TabsRoot, type TabsRootProps } from 'reka-ui'
import { cn } from '@/core/lib/utils'
import type { HTMLAttributes } from 'vue'

interface Props extends TabsRootProps {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <TabsRoot
    :class="cn('flex flex-col', props.class)"
    v-bind="props"
  >
    <slot />
  </TabsRoot>
</template>
