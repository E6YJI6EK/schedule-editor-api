import { defineStore } from 'pinia';
import { listDayPartitions } from '@/api/dayPartitions';
import type { DayPartition } from '@/api/types';

export function formatPartitionLabel(p: DayPartition): string {
  return `${p.start_time.slice(0, 5)}–${p.end_time.slice(0, 5)}`;
}

export const useDayPartitionsStore = defineStore('dayPartitions', {
  state: () => ({
    partitions: [] as DayPartition[],
    loaded: false,
  }),
  actions: {
    async load() {
      if (this.loaded) return;
      const res = await listDayPartitions();
      this.partitions = [...res.data].sort((a, b) =>
        a.start_time.localeCompare(b.start_time)
      );
      this.loaded = true;
    },
    getPartitionIdByLabel(label: string): number {
      const found = this.partitions.find(p => formatPartitionLabel(p) === label);
      return found?.id ?? 1;
    },
  },
});
