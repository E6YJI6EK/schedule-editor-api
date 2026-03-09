## Frontend Architecture Methodology

This architecture defines a **layered and compositional frontend structure** where UI, application logic, and business logic are strictly separated.
The goal is **predictability, scalability, and independent evolution of UI and domain logic**.

---

# 1. Architecture Overview

Application structure:

```
app
├── core
├── api
├── units
├── composites
├── blocks
└── screens
```

Hierarchy of responsibility (bottom → top):

```
core → api → units → composites → blocks → screens → app
```

Dependency direction **must always go upward**.
Lower layers must never depend on higher layers.

---

# 2. Layers

---

## 2.1 core

**Purpose:** foundational building material of the application.

Contains reusable, domain-agnostic utilities and infrastructure.

### Responsibilities

* UI primitives
* shared hooks
* utilities
* global constants
* infrastructure wrappers

### Includes

* UI components (Button, Input, Modal, Typography)
* data fetching client wrapper
* state manager configuration
* router client
* theme system
* global helpers

### Rules

* Must not contain business logic.
* Must not know about API contracts or domain entities.
* Fully reusable between projects.

### Example

```
core/
  ui/
    Button.tsx
    Select.tsx
  hooks/
    useDebounce.ts
  lib/
    formatDate.ts
  providers/
    QueryClientProvider.tsx
```

Example:

`Button` knows only visual behavior — not registration, users, or backend data.

---

## 2.2 api

**Purpose:** communication boundary between frontend and backend.

Frontend operates only with **frontend data models**.
This layer translates backend data into frontend structures and back.

### Responsibilities

* queries and mutations
* DTO mapping
* adapters
* validation
* contracts
* serialization/deserialization

### Concept

```
Backend DTO ⇄ Adapter ⇄ Frontend Model
```

### Rules

* UI must never consume backend responses directly.
* All transformations happen here.

### Example

Backend response:

```json
{
  "user_id": 10,
  "first_name": "John"
}
```

Adapter:

```ts
export const mapUser = (dto: UserDto): User => ({
  id: dto.user_id,
  name: dto.first_name,
});
```

Frontend model:

```ts
type User = {
  id: number;
  name: string;
};
```

---

## 2.3 units

**Purpose:** smallest indivisible UI + minimal logic component.

A unit encapsulates a single interaction or visualization.

### Characteristics

* minimal logic
* isolated responsibility
* no business knowledge
* may use `api` and `core`

### Example

City Select:

* fetches cities
* renders select
* manages loading/error state

```
units/city-select/
  view/
  model/
  constants/
  lib/
```

Unit knows **how to select a city**, not **why** it is selected.

---

## 2.4 composites

**Purpose:** composition of multiple units with shared state or interaction logic.

A composite extends units with coordination logic.

### Characteristics

* combines units
* introduces shared state
* still domain-agnostic

### Example

City Select + Choos City Modal:

* Select unit
* Modal unit
* shared open state

Behavior:

```
Click "Choose city" → modal opens → city choosed → select updates
```

Composite combines units with shared state or extends one unit.

---

## 2.5 blocks

**Purpose:** domain-aware UI components.

Blocks represent meaningful business functionality.

### Characteristics

* contain business logic
* orchestrate units and composites
* operate on domain concepts

### Example

Registration Form block:

* email input unit
* password input unit
* validation logic
* registration mutation

Block understands:

```
User registration exists as a business action
```

Structure:

```
blocks/registration-form/
```

---

## 2.6 screens

**Purpose:** describe business processes and application flows.

Screens combine blocks into user workflows.

### Characteristics

* highest concentration of domain logic
* navigation control
* process orchestration
* may represent single or multiple pages

### Example

Registration flow:

```
RegistrationScreen
  → CodeVerificationScreen
  → SuccessScreen
  → ErrorScreen
```

Screens coordinate:

* transitions
* shared process state
* business scenarios

---

## 2.7 app

**Purpose:** application entry point.

### Responsibilities

* global providers
* routing setup
* global styles
* screen mounting

### Example

```
app/
  providers/
  router/
  App.tsx
  main.tsx
```

Contains:

* QueryClientProvider
* ThemeProvider
* Router
* global CSS

---

# 3. Internal Module Structure

Each **unit**, **composite**, **block**, and **screen** must follow the same internal structure:

```
entity/
  view/
  model/
  constants/
  lib/
  types/
  __storybook__/
  __test__/unit
  __test__/ui
```

---

## 3.1 view

Presentation layer.

### Responsibilities

* rendering
* visual states
* layouts
* styles

Includes:

* loader state
* error state
* default state
* other state if neccessary

Rules:

* minimal logic
* receives prepared data from model

Optional platform separation:

```
view/
  desktop.tsx
  mobile.tsx
```

---

## 3.2 model

Logic layer.

### Responsibilities

* hooks
* stores
* state orchestration
* interaction logic

Examples:

* `useRegistrationForm`
* Redux/Zustand/MobX/Effector/etc store
* query usage

View consumes model output.

---

## 3.3 constants

Static configuration:

```
FORM_FIELDS
VALIDATION_LIMITS
DEFAULT_VALUES
```

---

## 3.4 lib

Helper functions local to the entity.

Examples:

```
formatPhone.ts
normalizeEmail.ts
```

Not shared globally (otherwise belongs to `core`).

---

## 3.5 types

Used types.

Examples:

```
types.ts
```

May be shared globally.

---

## 3.6 __storybook__

Storybook stories.

Examples:

```
login-form.stories.ts
register-form.stories.ts
```

---

## 3.7 __test__/unit

Tests for logic. Need to test **model**

Examples:

```
validate.test.ts
create-user.test.ts
```

---

## 3.8 __test__/ui

Tests for UI. Need to test **view**. In this tests need to use render() from testing library.

Examples:

```
login-form.test.ts
register-form.test.ts
```

---

# 4. Dependency Rules

Allowed dependencies:

```
core → (none)
api → core
units → api, core
composites → units, api, core
blocks → composites, units, api, core
screens → blocks, composites, units, api, core
app → screens + providers
```

Forbidden:

* units importing blocks
* core importing api
* api importing UI
* lower layers depending on higher layers

---

# 5. Data Flow

```
User Interaction
      ↓
View
      ↓
Model
      ↓
API Layer
      ↓
Backend
      ↓
Adapter (API)
      ↓
Model
      ↓
View Update
```

Frontend always works with **frontend models**, never backend DTOs.

---

# 6. Conceptual Example

### Feature: User Registration

**Unit**

* EmailInput
* PasswordInput

**Composite**

* PasswordInput + StrengthIndicator

**Block**

* RegistrationForm

  * validation
  * submit logic

**Screens**

* RegistrationScreen
* VerifyCodeScreen
* SuccessScreen

**API**

* registerUser mutation
* DTO adapters

**Core**

* Input
* Button
* Form layout

**App**

* router mounts Registration flow

---

# 7. Architectural Principles

1. Separation of concerns by abstraction level.
2. UI composition grows from simple → complex.
3. Business logic appears only from `blocks` level and above.
4. Backend format is isolated inside `api`.
5. Each entity encapsulates view + logic + helpers.
6. Predictable dependency direction.
7. Reusability increases toward lower layers.

---

End of specification.
