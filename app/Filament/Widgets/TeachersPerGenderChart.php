<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class TeachersPerGenderChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Guru per Gender';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && ($user->role->name === 'Admin' || $user->role->name === 'Teacher');
    }

    protected function getData(): array
    {
        $maleCount = User::whereHas('role', function ($query) {
            $query->where('name', 'Teacher');
        })->where('gender', 'male')->count();

        $femaleCount = User::whereHas('role', function ($query) {
            $query->where('name', 'Teacher');
        })->where('gender', 'female')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Guru',
                    'data' => [$maleCount, $femaleCount],
                    'backgroundColor' => ['#3B82F6', '#EC4899'],
                ],
            ],
            'labels' => ['Guru Laki-laki', 'Guru Perempuan'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
