import { ref, computed } from 'vue'
import type { Ref } from 'vue'

export function usePagination<T>(items: Ref<T[]>, perPage = 15) {
  const page = ref(1)

  const paginatedItems = computed(() => {
    const start = (page.value - 1) * perPage
    return items.value.slice(start, start + perPage)
  })

  const resetPage = () => { page.value = 1 }

  return { page, perPage, paginatedItems, resetPage }
}
