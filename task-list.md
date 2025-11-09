## Frontend API implementation (Vue 3, Pinia, Axios)

### 0) Prereqs
- [ ] Install deps: `vue@3`, `pinia`, `axios`
- [ ] Configure Vite alias `@` → `src`

### 1) Axios base instance
- [ ] Create `src/shared/api/http.ts`
  - [ ] `baseURL` from env (e.g., `import.meta.env.VITE_API_URL`)
  - [ ] JSON headers; timeout; response interceptor to unwrap `data` and standardize errors

```ts
// src/shared/api/http.ts
import axios from 'axios';

export const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? '/',
  timeout: 15000,
  headers: { 'Content-Type': 'application/json' },
});

http.interceptors.response.use(
  (res) => res,
  (err) => Promise.reject(
    err?.response?.data ?? { success: false, message: 'Network error', status: 0 }
  )
);
```

### 2) DTOs / types
- [ ] Create `src/shared/api/types.ts` with minimal types for endpoints
  - [ ] `ApiSuccess<T>`, `ApiError`
  - [ ] Entities: `Teacher`, `Discipline`, `Building`, `ClassRoom`, `Group`, `Lesson`, `TimeSlot`, `Institute`
  - [ ] Enums mirror backend values: `Course`, `EducationForm`, `WeekType`, `Day`

### 3) API clients per resource
- [ ] Create `src/shared/api/app.ts`
  - [ ] `getAlive(): Promise<ApiSuccess<string>>` → `GET /`

- [ ] Create `src/shared/api/teachers.ts`
  - [ ] `searchTeachers(params: { discipline_id: number; name?: string }): Promise<ApiSuccess<Teacher[]>>` → `GET /teachers/search`

- [ ] Create `src/shared/api/disciplines.ts`
  - [ ] `searchDisciplines(params?: { name?: string }): Promise<ApiSuccess<Discipline[]>>` → `GET /disciplines/search`

- [ ] Create `src/shared/api/buildings.ts`
  - [ ] `searchBuildings(params?: { name?: string }): Promise<ApiSuccess<Building[]>>` → `GET /buildings/search`

- [ ] Create `src/shared/api/classRooms.ts`
  - [ ] `searchClassRooms(params: { building_id: number; number?: string }): Promise<ApiSuccess<ClassRoom[]>>` → `GET /class-rooms/search`

- [ ] Create `src/shared/api/groups.ts`
  - [ ] `searchGroups(params: { course: Course; education_form: EducationForm; institute_id: number; discipline_id: number; name?: string }): Promise<ApiSuccess<Group[]>>` → `GET /groups/search`

- [ ] Create `src/shared/api/lessons.ts`
  - [ ] `createLesson(payload: { teacher_id: number; class_room_id: number; time_slot_id: number; discipline_id: number; group_id: number }): Promise<ApiSuccess<Lesson>>` → `POST /lessons/create`
  - [ ] `updateLesson(id: number, payload: Partial<{ teacher_id: number; class_room_id: number; time_slot_id: number; discipline_id: number; group_id: number }>): Promise<ApiSuccess<Lesson>>` → `PUT /lessons/update/{id}`

### 4) Pinia stores (one per domain)
- [ ] `src/stores/teachers.ts`
  - [ ] state: `items: Teacher[]`, `loading`, `error`
  - [ ] action: `search({ discipline_id, name })`

- [ ] `src/stores/disciplines.ts`
  - [ ] action: `search({ name? })`

- [ ] `src/stores/buildings.ts`
  - [ ] action: `search({ name? })`

- [ ] `src/stores/classRooms.ts`
  - [ ] action: `search({ building_id, number? })`

- [ ] `src/stores/groups.ts`
  - [ ] action: `search({ course, education_form, institute_id, discipline_id, name? })`

- [ ] `src/stores/lessons.ts`
  - [ ] actions: `create(payload)`, `update(id, payload)`

All actions:
- [ ] set `loading=true` before request, `false` on finally
- [ ] on API error: map `{ message, status, errors? }` to store `error`

### 5) UI components and forms
- [ ] Create search forms and result lists:
  - [ ] TeachersSearch: inputs `discipline_id` (select), `name` (text), results list
  - [ ] DisciplinesSearch: `name`
  - [ ] BuildingsSearch: `name`
  - [ ] ClassRoomsSearch: `building_id` (select), `number`
  - [ ] GroupsSearch: `course`, `education_form`, `institute_id`, `discipline_id`, `name`
- [ ] Create LessonCreateForm:
  - [ ] controlled selects for `teacher`, `classRoom`, `timeSlot`, `discipline`, `group`
  - [ ] submit → `lessonsStore.create`
  - [ ] show 409 error message “Такая пара уже существует”
- [ ] Create LessonUpdateForm:
  - [ ] loads existing `lesson` (optional if not provided)
  - [ ] submit → `lessonsStore.update(id, payload)`

Validation (client-side):
- [ ] Basic required checks mirroring backend (disable submit until valid)

### 6) Error and empty-state handling
- [ ] Show 404 “not found” messages for searches with empty results
- [ ] Display global/network errors from axios interceptor
- [ ] Loading spinners while `loading` is true

### 7) Routing/wiring
- [ ] Add Vue Router routes/pages for each search and lesson forms
- [ ] Use `keep-alive` for search pages (optional)

### 8) Env and configuration
- [ ] Add `.env` key `VITE_API_URL` pointing to backend base URL
- [ ] Document running both FE/BE in README

### 9) QA checklist per endpoint
- [ ] `GET /` shows alive banner
- [ ] `GET /teachers/search` returns list or 404 when empty; 422 when missing `discipline_id`
- [ ] `GET /disciplines/search` returns list or 404 when empty
- [ ] `GET /buildings/search` returns list or 404 when empty
- [ ] `GET /class-rooms/search` returns list or 404 when empty; 422 when missing `building_id`
- [ ] `GET /groups/search` returns list or 404 when empty; 422 when required fields missing
- [ ] `POST /lessons/create` returns 201 with created lesson; 409 on duplicate
- [ ] `PUT /lessons/update/{id}` returns 200 on valid update

### 10) Minimal usage examples
- [ ] Teachers search action

```ts
// teachers.ts store action example
import { defineStore } from 'pinia';
import { searchTeachers } from '@/shared/api/teachers';

export const useTeachersStore = defineStore('teachers', {
  state: () => ({ items: [], loading: false, error: null as null | string }),
  actions: {
    async search(params: { discipline_id: number; name?: string }) {
      this.loading = true; this.error = null;
      try {
        const res = await searchTeachers(params);
        this.items = res.data.data; // backend envelope
      } catch (e: any) {
        this.error = e.message ?? 'Ошибка';
        this.items = [];
      } finally {
        this.loading = false;
      }
    },
  },
});
```

### 11) Optional improvements
- [ ] Centralize API envelope parsing (`{ success, message, data, status }`)
- [ ] Add debounce to search inputs
- [ ] Cache last search results in Pinia (per key)
- [ ] E2E tests (Cypress) for flows: search, create lesson, duplicate error, update lesson


