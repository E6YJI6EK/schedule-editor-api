import * as XLSX from 'xlsx';
import { saveAs } from 'file-saver';
import type { Schedule, WeekType } from '@/types/schedule';

export function exportToExcel(scheduleData: Schedule): void {
  // Create a new workbook
  const workbook = XLSX.utils.book_new();
  
  const groups = scheduleData.groups || ['Группа 1', 'Группа 2', 'Группа 3'];
  
  // Process each week
  (['upperWeek', 'lowerWeek'] as WeekType[]).forEach(week => {
    const weekData = scheduleData[week];
    const weekName = week === 'upperWeek' ? 'Верхняя неделя' : 'Нижняя неделя';
    
    // Prepare data for Excel
    const excelData: (string | number)[][] = [];
    
    // Add header row 1 (days)
    const headerRow1: string[] = ['Время'];
    ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'].forEach(day => {
      headerRow1.push(day);
      for (let i = 1; i < groups.length; i++) {
        headerRow1.push(''); // Empty cells for merge
      }
    });
    
    // Add header row 2 (groups)
    const headerRow2: string[] = [''];
    ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'].forEach(() => {
      groups.forEach(group => {
        headerRow2.push(group);
      });
    });
    
    excelData.push(headerRow1);
    excelData.push(headerRow2);
    
    // Get all unique time slots
    const allTimes: string[] = [];
    weekData.forEach(day => {
      day.timeslots.forEach(slot => {
        if (!allTimes.includes(slot.time)) {
          allTimes.push(slot.time);
        }
      });
    });
    
    // Sort times
    allTimes.sort();
    
    // Create rows for each time slot
    allTimes.forEach(time => {
      const row: string[] = [time];
      
      // Add data for each day and group
      ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'].forEach(dayName => {
        const dayData = weekData.find(d => d.day === dayName);
        const timeSlot = dayData?.timeslots.find(t => t.time === time);
        
        groups.forEach((_, groupIdx) => {
          const groupData = timeSlot?.groups?.[groupIdx];
          if (groupData && groupData.subject) {
            const cellData = `${groupData.subject}\n${groupData.teacher}\n${groupData.room} (${groupData.building})`;
            row.push(cellData);
          } else {
            row.push('');
          }
        });
      });
      
      excelData.push(row);
    });
    
    // Create worksheet
    const worksheet = XLSX.utils.aoa_to_sheet(excelData);
    
    // Set column widths
    const columnWidths: XLSX.ColInfo[] = [{ wch: 15 }]; // Time column
    for (let i = 0; i < 6; i++) { // 6 days
      for (let j = 0; j < groups.length; j++) { // groups per day
        columnWidths.push({ wch: 20 });
      }
    }
    worksheet['!cols'] = columnWidths;
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(workbook, worksheet, weekName);
  });
  
  // Generate Excel file
  const excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' });
  const data = new Blob([excelBuffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  
  // Save file
  const fileName = `Расписание_${new Date().toLocaleDateString('ru-RU').replace(/\./g, '_')}.xlsx`;
  saveAs(data, fileName);
}

