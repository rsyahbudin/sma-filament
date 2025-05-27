<x-filament-panels::page>
    <style>
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .report-table th,
        .report-table td {
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            text-align: left;
            color: #111827;
        }

        .report-table th {
            background-color: #f9fafb;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .report-table td {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-passed {
            color: #059669;
            background-color: #d1fae5;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-failed {
            color: #dc2626;
            background-color: #fee2e2;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .promotion-info {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .promotion-info h3 {
            color: #0369a1;
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .promotion-info p {
            color: #0c4a6e;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .average-score {
            font-size: 1.25rem;
            font-weight: 600;
            color: #0369a1;
        }
    </style>

    <div class="mt-6">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-600">
                    Student Report - {{ $record->name }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Email: {{ $record->email }} | Phone: {{ $record->phone }}
                </p>

                @foreach($academicYears as $yearData)
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900">
                        Academic Year: {{ $yearData['year']->name }}
                    </h3>

                    @php
                    $currentClass = $record->classes->first();
                    $isPromoted = $currentClass?->pivot->is_promoted ?? false;
                    $averageScore = $record->grades()
                    ->whereHas('academicYear', function ($query) use ($yearData) {
                    $query->where('id', $yearData['year']->id);
                    })
                    ->avg('score');
                    @endphp

                    @if($isPromoted)
                    <div class="promotion-info">
                        <h3>Promotion Status</h3>
                        <p>Student has been promoted to the next class.</p>
                        <p>Current Class: {{ $currentClass->name }}</p>
                        <p>Average Score: <span class="average-score">{{ number_format($averageScore, 2) }}</span></p>
                    </div>
                    @endif

                    @foreach($yearData['semesters'] as $semesterData)
                    <div class="mt-4">
                        <h4 class="text-md font-medium text-gray-700">
                            Semester {{ $semesterData['semester'] }}
                        </h4>

                        <div class="mt-2 overflow-x-auto">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Class</th>
                                        <th>Score</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($semesterData['grades'] as $grade)
                                    <tr>
                                        <td>{{ $grade->subject->name }}</td>
                                        <td>{{ $grade->class->name }}</td>
                                        <td>{{ $grade->score }}</td>
                                        <td>
                                            @if($grade->score >= $grade->subject->minimum_score)
                                            <span class="status-passed">Passed</span>
                                            @else
                                            <span class="status-failed">Failed</span>
                                            @endif
                                        </td>
                                        <td>{{ $grade->notes }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>