<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;

class AdminStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && ($user->role->name === 'Admin' || $user->role->name === 'Teacher');
    }

    protected function getStats(): array
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        $totalTeachers = User::whereHas('role', function ($query) {
            $query->where('name', 'Teacher');
        })->count();

        $totalStudents = User::whereHas('role', function ($query) {
            $query->where('name', 'Student');
        })->count();

        $totalClasses = SchoolClass::when($activeYear, function ($query) use ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        })->count();

        $totalSubjects = Subject::count();

        return [
            Stat::make('Total Teachers', $totalTeachers)
                ->description('Total number of teachers in the system')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Total Students', $totalStudents)
                ->description('Total number of students in the system')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Total Classes', $totalClasses)
                ->description($activeYear ? "Total classes for {$activeYear->name}" : 'Total classes')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning'),

            Stat::make('Total Subjects', $totalSubjects)
                ->description('Total number of subjects')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
        ];
    }
}
