# Laravel Backend Architecture

## Overview

- Framework: Laravel (PHP), REST API under `/api` [routes](file:///c:/OSPanel/home/schedule-editor-api/routes/api.php).
- Purpose: Serve schedule management for university groups, teachers, rooms, and buildings.
- Response format: Unified helpers for success/error JSON [ApiResponse.php](file:///c:/OSPanel/home/schedule-editor-api/app/Helpers/ApiResponse.php).

## Layering

- Routes → Controllers → Services → Eloquent Models.
- Validation via Form Request classes (per-endpoint).
- Enums model constrained values for week type, day, course, education form.

## Routes

- Lessons: POST `/lessons/create`, PUT `/lessons/update/{id}`, GET `/lessons/schedule`, GET `/lessons/time-slot` [api.php](file:///c:/OSPanel/home/schedule-editor-api/routes/api.php).
- Search: Teachers `/teachers/search`, Disciplines `/disciplines/search`, Buildings `/buildings/search`, ClassRooms `/class-rooms/search`, Groups `/groups/search`, `/groups/search-by-name` [api.php](file:///c:/OSPanel/home/schedule-editor-api/routes/api.php).

## Controllers

- Lessons [LessonController.php](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Controllers/LessonController.php)
  - create: validates, delegates to service, handles duplicate/error cases.
  - getSchedule: returns lessons for groups and week type.
  - getTimeSlot: resolves `time_slots.id` by week_type/day/day_partition_id.
- Teachers [TeacherController.php](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Controllers/TeacherController.php)
  - searchTeachers: requires discipline_id; optional name filter.
- Disciplines [DisciplineController.php](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Controllers/DisciplineController.php)
  - searchDisciplines: optional name filter.
- Buildings [BuildingController.php](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Controllers/BuildingController.php)
  - searchBuildings: optional name filter.
- ClassRooms [ClassRoomController.php](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Controllers/ClassRoomController.php)
  - searchClassRooms: requires building_id; optional number filter.
- Groups [GroupController.php](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Controllers/GroupController.php)
  - searchGroups: by institute/course/education_form/name.
  - searchGroupsByName: simple name-based lookup.

## Requests (Validation)

- Lessons: [CreateLessonRequest](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Requests/Lessons/CreateLessonRequest.php), [GetScheduleRequest](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Requests/Lessons/GetScheduleRequest.php).
- Teachers: [SearchTeachersRequest](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Requests/Teachers/SearchTeachersRequest.php).
- Disciplines: [SearchDisciplinesRequest](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Requests/Disciplines/SearchDisciplinesRequest.php).
- ClassRooms: [SearchClassRoomsRequest](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Requests/ClassRooms/SearchClassRoomsRequest.php).
- Groups: [SearchGroupsRequest](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Requests/Groups/SearchGroupsRequest.php), [SearchGroupsByNameRequest](file:///c:/OSPanel/home/schedule-editor-api/app/Http/Requests/Groups/SearchGroupsByNameRequest.php).

## Services (Business Logic)

- Lessons [LessonService.php](file:///c:/OSPanel/home/schedule-editor-api/app/Services/LessonService.php)
  - create: prevents duplicates; returns created lesson or error metadata.
  - getSchedule: filters by group IDs and week type; eager-loads relations.
- Teachers [TeacherService.php](file:///c:/OSPanel/home/schedule-editor-api/app/Services/TeacherService.php)
  - search by discipline, optional name.
- Disciplines [DisciplineService.php](file:///c:/OSPanel/home/schedule-editor-api/app/Services/DisciplineService.php)
  - search by optional name.
- Buildings [BuildingService.php](file:///c:/OSPanel/home/schedule-editor-api/app/Services/BuildingService.php)
  - search by optional name.
- ClassRooms [ClassRoomService.php](file:///c:/OSPanel/home/schedule-editor-api/app/Services/ClassRoomService.php)
  - filter by building and optional number.
- Groups [GroupService.php](file:///c:/OSPanel/home/schedule-editor-api/app/Services/GroupService.php)
  - search by institute/course/education_form/name; simple name search.

## Domain Model

- Lesson [Lesson.php](file:///c:/OSPanel/home/schedule-editor-api/app/Models/Lesson.php): belongsTo Teacher, ClassRoom, TimeSlot, Discipline, Group.
- TimeSlot [TimeSlot.php](file:///c:/OSPanel/home/schedule-editor-api/app/Models/TimeSlot.php): casts WeekType/Day, belongsTo DayPartition, hasMany Lesson.
- DayPartition [DayPartition.php](file:///c:/OSPanel/home/schedule-editor-api/app/Models/DayPartition.php): start/end times, hasMany TimeSlot.
- ClassRoom [ClassRoom.php](file:///c:/OSPanel/home/schedule-editor-api/app/Models/ClassRoom.php): belongsTo Building, hasMany Lesson.
- Building [Building.php](file:///c:/OSPanel/home/schedule-editor-api/app/Models/Building.php): hasMany ClassRoom.
- Teacher [Teacher.php](file:///c:/OSPanel/home/schedule-editor-api/app/Models/Teacher.php): belongsToMany Discipline, hasMany Lesson.
- Discipline [Discipline.php](file:///c:/OSPanel/home/schedule-editor-api/app/Models/Discipline.php): belongsToMany Teacher, hasMany Lesson.
- Group [Group.php](file:///c:/OSPanel/home/schedule-editor-api/app/Models/Group.php): casts Course/EducationForm, belongsTo Institute, hasMany Lesson.
- Institute [Institute.php](file:///c:/OSPanel/home/schedule-editor-api/app/Models/Institute.php): hasMany Group.

## Enums

- WeekType [WeekType.php](file:///c:/OSPanel/home/schedule-editor-api/app/Enums/WeekType.php): `upper` | `lower`.
- Day [Day.php](file:///c:/OSPanel/home/schedule-editor-api/app/Enums/Day.php): Monday–Saturday (1–6).
- Course [Course.php](file:///c:/OSPanel/home/schedule-editor-api/app/Enums/Course.php): 1–5.
- EducationForm [EducationForm.php](file:///c:/OSPanel/home/schedule-editor-api/app/Enums/EducationForm.php): intramural/extramural/hybrid.
- LessonType (reserved) [LessonType.php](file:///c:/OSPanel/home/schedule-editor-api/app/Enums/LessonType.php).

## Response Contracts

- success JSON: `{ success: true, message, data, status }`.
- error JSON: `{ success: false, message, status, errors? }`.
- Implemented in [ApiResponse.php](file:///c:/OSPanel/home/schedule-editor-api/app/Helpers/ApiResponse.php).

## Key Flows

- Fetch schedule:
  - Client calls `/lessons/schedule` with `group_ids[]` and `is_upper_week`.
  - Service applies `WeekType` filter and returns lessons with relations.
- Create lesson:
  - Client provides `teacher_id`, `class_room_id`, `time_slot_id`, `discipline_id`, `group_id`.
  - Service rejects duplicates; creates lesson or returns conflict.
- Resolve time slot:
  - Client calls `/lessons/time-slot` with `week_type`, `day` (1–7), `day_partition_id` (1–6) to get `time_slots.id`.

## Testing

- Feature tests assert validation, 404 cases, and happy paths:
  - Lessons routes tests [LessonsRoutesTest.php](file:///c:/OSPanel/home/schedule-editor-api/tests/Feature/LessonsRoutesTest.php).
  - Teachers/Disciplines/Groups routes tests validate searches and errors.

## Notes

- Validation centralizes business constraints in Request classes.
- Services encapsulate query composition and deduplication logic.
- Eager loading prevents N+1 when returning schedules with relations.

