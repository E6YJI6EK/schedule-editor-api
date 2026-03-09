# Project Features and Use Cases

## Architecture Summary

- Routing: Single route “/schedule” renders the schedule screen [router](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/app/router/index.ts).
- State: Central Pinia store manages schedule state, editing, moving, loading, and errors [scheduleStore.ts](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/screens/schedule/model/scheduleStore.ts).
- Screen Composition:
  - Group selection panel [Schedule View](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/screens/schedule/view.vue) with multi-select component [GroupMultiSelect](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/units/group-multi-select/view.vue).
  - Schedule table/grid [ScheduleTable](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/blocks/schedule-table/view.vue) composed of editable cells [ScheduleCell](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/blocks/schedule-cell/view.vue).
  - Toolbar with week switcher and exports [Toolbar](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/blocks/schedule-toolbar/view/Toolbar.vue), [WeekSwitcher](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/blocks/schedule-toolbar/view/WeekSwitcher.vue).
- Editing Widgets:
  - Subject selector [SubjectSelect](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/units/subject-select/view.vue)
  - Teacher selector (filtered by discipline) [TeacherSelect](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/units/teacher-select/view.vue)
  - Building selector [BuildingSelect](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/units/building-select/view.vue)
  - Room selector (filtered by building) [RoomSelect](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/units/room-select/view.vue)
- Export:
  - Excel export [exportExcel.ts](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/blocks/schedule-toolbar/model/exportExcel.ts)
  - PDF export [exportPDF.ts](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/blocks/schedule-toolbar/model/exportPDF.ts)
- Backend Endpoints (Laravel routes) [api.php](file:///c:/OSPanel/home/schedule-editor-api/routes/api.php):
  - Lessons: create, update, schedule query, time-slot resolution
  - Reference data search: teachers, disciplines, buildings, class-rooms, groups (incl. search-by-name)

## Main Use Cases

- user can search groups by name and select multiple groups to work with.
- user can open the schedule for selected groups and display it in a grid.
- user can switch between upper and lower weeks to view schedules.
- user can edit a class cell by choosing subject, teacher, building, and room.
- user can search and select subjects from the catalog.
- user can search and select teachers filtered by chosen subject.
- user can search and select buildings for class locations.
- user can search and select rooms filtered by selected building.
- user can save changes for an existing lesson (update).
- user can create a new lesson in an empty cell (create).
- user can drag and drop a class to another time/day/week/group slot.
- user can persist moved classes by updating their time slots.
- user can export the visible schedule to Excel.
- user can export the visible schedule to PDF.
- user can see color-coded cells based on room numbers.
- user can view loading indicators while schedule data is retrieved.
- user can see errors when schedule loading or updates fail.
- user can re-open group selection to change the set of groups.

## Data Flow Highlights

- Initial Selection: user selects groups, then triggers loading of both weeks into an empty scaffold [createEmptySchedule.ts](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/screens/schedule/model/createEmptySchedule.ts) followed by backend fetch [useScheduleScreen.ts](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/screens/schedule/model/useScheduleScreen.ts).
- Schedule Fetch: frontend requests /lessons/schedule for upper and lower weeks and transforms results into grid format [scheduleTransform.ts](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/screens/schedule/model/scheduleTransform.ts).
- Edit & Save: cell edits call create/update lesson endpoints; new time_slot_id is resolved via /lessons/time-slot before create/update [scheduleStore.ts](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/screens/schedule/model/scheduleStore.ts).
- Move (DnD): drag-and-drop swaps cell contents optimistically, then updates lesson time slots; rolls back on errors [scheduleStore.ts](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/screens/schedule/model/scheduleStore.ts).

## Backend API Snapshot

- Lessons: POST /lessons/create, PUT /lessons/update/{id}, GET /lessons/schedule, GET /lessons/time-slot.
- Search: GET /teachers/search, GET /disciplines/search, GET /buildings/search, GET /class-rooms/search, GET /groups/search, GET /groups/search-by-name.

## Environment Notes

- Frontend HTTP base: VITE_API_BASE (defaults to /api) [http.ts](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/core/fetch-client/http.ts).
- Dev proxy: Vite proxies /api to http://localhost:3000 [vite.config.ts](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/vite.config.ts). Express server can also proxy to backend [server.js](file:///c:/OSPanel/home/schedule-editor-api/schedule-editor-app/src/server.js).

