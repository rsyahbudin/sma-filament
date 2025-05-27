<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Subject;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StudentStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->role->name === 'Student';
    }

    protected function getStats(): array
    {
        $totalTeachers = User::whereHas('role', function ($query) {
            $query->where('name', 'Teacher');
        })->count();

        $totalStudents = User::whereHas('role', function ($query) {
            $query->where('name', 'Student');
        })->count();

        // Get current student's subjects
        $currentStudent = Auth::user();
        $currentSubjects = $currentStudent->classes()
            ->with('subjects')
            ->get()
            ->pluck('subjects')
            ->flatten()
            ->unique('id')
            ->count();

        return [
            Stat::make('Total Teachers', $totalTeachers)
                ->description('Total number of teachers in the school')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Total Students', $totalStudents)
                ->description('Total number of students in the school')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Current Subjects', $currentSubjects)
                ->description('Number of subjects you are currently taking')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
        ];
    }
}
