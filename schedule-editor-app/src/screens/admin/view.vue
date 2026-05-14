<script setup lang="ts">
import { useRouter } from 'vue-router'
import { ArrowLeft } from 'lucide-vue-next'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/core/ui/tabs'
import EmployeesTab from './components/tabs/EmployeesTab.vue'
import BuildingsTab from './components/tabs/BuildingsTab.vue'
import DisciplinesTab from './components/tabs/DisciplinesTab.vue'
import TeachersTab from './components/tabs/TeachersTab.vue'
import ClassRoomsTab from './components/tabs/ClassRoomsTab.vue'
import DayPartitionsTab from './components/tabs/DayPartitionsTab.vue'

const router = useRouter()
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center gap-3">
      <button
        @click="router.back()"
        class="text-gray-500 hover:text-gray-800 transition-colors"
      >
        <ArrowLeft class="w-5 h-5" />
      </button>
      <h1 class="text-xl font-bold text-gray-800">Панель администратора</h1>
    </div>

    <div class="container mx-auto px-4 py-6 max-w-5xl">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <Tabs default-value="employees">
          <TabsList class="w-full sm:w-auto mb-6">
            <TabsTrigger value="employees">Сотрудники</TabsTrigger>
            <TabsTrigger value="buildings">Корпуса</TabsTrigger>
            <TabsTrigger value="classrooms">Аудитории</TabsTrigger>
            <TabsTrigger value="disciplines">Дисциплины</TabsTrigger>
            <TabsTrigger value="teachers">Преподаватели</TabsTrigger>
            <TabsTrigger value="timeslots">Расписание звонков</TabsTrigger>
          </TabsList>

          <TabsContent value="employees">
            <EmployeesTab />
          </TabsContent>

          <TabsContent value="buildings">
            <BuildingsTab />
          </TabsContent>

          <TabsContent value="classrooms">
            <ClassRoomsTab />
          </TabsContent>

          <TabsContent value="disciplines">
            <DisciplinesTab />
          </TabsContent>

          <TabsContent value="teachers">
            <TeachersTab />
          </TabsContent>

          <TabsContent value="timeslots">
            <DayPartitionsTab />
          </TabsContent>
        </Tabs>
      </div>
    </div>
  </div>
</template>
