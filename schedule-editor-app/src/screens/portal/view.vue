<script setup lang="ts">
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/core/ui/tabs'
import PortalHeader from './components/PortalHeader.vue'
import GroupSearchTab from './components/tabs/GroupSearchTab.vue'
import TeacherSearchTab from './components/tabs/TeacherSearchTab.vue'
import ClassroomSearchTab from './components/tabs/ClassroomSearchTab.vue'
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <PortalHeader />

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Расписание занятий</h1>
        <p class="text-gray-500 text-sm mt-1">Найдите расписание группы, преподавателя или аудитории</p>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <Tabs default-value="group">
          <TabsList class="w-full sm:w-auto mb-2">
            <TabsTrigger value="group">По группе</TabsTrigger>
            <TabsTrigger value="teacher">По преподавателю</TabsTrigger>
            <TabsTrigger value="classroom">По аудитории</TabsTrigger>
          </TabsList>

          <TabsContent value="group">
            <GroupSearchTab />
          </TabsContent>

          <TabsContent value="teacher">
            <TeacherSearchTab />
          </TabsContent>

          <TabsContent value="classroom">
            <ClassroomSearchTab />
          </TabsContent>
        </Tabs>
      </div>
    </main>
  </div>
</template>
