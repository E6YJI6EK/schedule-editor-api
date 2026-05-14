# Vue Schedule Editor for University Teachers

A modern web application built with Vue 3 for editing university teacher schedules with upper/lower week functionality.

## Features

- 📅 **Dual Week System**: Separate schedules for upper and lower weeks
- ✏️ **Interactive Editing**: Click on any cell to edit class information
- 🎨 **Color-coded Rooms**: Different colors for different rooms/buildings
- 📊 **Export Functionality**: Export schedules to Excel and PDF formats
- 🔄 **Week Switching**: Easy toggle between upper and lower weeks
- 📱 **Responsive Design**: Works on desktop and mobile devices

## Technologies Used

- **Vue 3** with Composition API and `<script setup>`
- **Pinia** for state management
- **Vue Router** for navigation
- **TailwindCSS** for styling
- **Lucide Vue** for icons
- **XLSX** for Excel export
- **jsPDF** for PDF export

## Project Structure

```
src/
├── components/
│   ├── ScheduleTable.vue    # Main schedule grid component
│   ├── ScheduleCell.vue     # Individual cell with editing
│   ├── WeekSwitcher.vue     # Week toggle component
│   └── Toolbar.vue          # Top toolbar with actions
├── views/
│   └── ScheduleView.vue     # Main page view
├── stores/
│   └── scheduleStore.js     # Pinia store for state
├── utils/
│   ├── mockData.js          # Sample schedule data
│   ├── exportExcel.js       # Excel export functionality
│   └── exportPDF.js         # PDF export functionality
└── router/
    └── index.js             # Vue Router configuration
```

## Getting Started

1. Install dependencies:
   ```bash
   npm install
   ```

2. Start the development server:
   ```bash
   npm run dev
   ```

3. Open your browser and navigate to `http://localhost:5173`

## Usage

1. **View Schedule**: The application displays both upper and lower week schedules
2. **Edit Classes**: Click on any cell to edit class information (subject, teacher, group, room, building)
3. **Switch Weeks**: Use the week switcher in the toolbar to toggle between upper and lower weeks
4. **Export Data**: Use the Excel or PDF export buttons to download the schedule
5. **Add Classes**: Click "Добавить пару" to add new classes (functionality can be extended)

## Sample Data

The application comes with sample data including:
- Mathematics, Physics, Computer Science, History, English, Chemistry, Economics, Philosophy, Programming, Databases, and Physical Education classes
- Different teachers and groups (БИВТ-21, БИВТ-22)
- Various rooms and buildings with color coding

## Customization

- **Room Colors**: Modify `roomColors` in `src/utils/mockData.js`
- **Time Slots**: Adjust time slots in the mock data
- **Styling**: Customize colors and layout using TailwindCSS classes
- **Export Format**: Modify export functions in `src/utils/exportExcel.js` and `src/utils/exportPDF.js`

## Future Enhancements

- Database integration for persistent storage
- User authentication and role management
- Advanced filtering and search
- Drag and drop functionality
- Conflict detection
- Email notifications
- Mobile app version