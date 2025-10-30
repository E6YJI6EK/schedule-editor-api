<?php
namespace App\Enums;

enum LessonType: string
{
    case Lecture = 'Lecture';
    case Seminar = 'Seminar';
    case Exam = 'Exam';
    case Test = 'Test';
}