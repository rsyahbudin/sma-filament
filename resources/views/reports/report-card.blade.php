<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Student Report Card</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .student-info {
            margin-bottom: 20px;
        }

        .student-info p {
            margin: 5px 0;
        }

        .grade-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .grade-table th,
        .grade-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .grade-table th {
            background-color: #f5f5f5;
        }

        .status {
            font-weight: bold;
        }

        .status.passed {
            color: green;
        }

        .status.failed {
            color: red;
        }

        .semester-header {
            background-color: #e5e7eb;
            font-weight: bold;
            padding: 10px;
            margin-top: 20px;
        }

        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Student Report Card</h1>
    </div>

    <div class="student-info">
        <p><strong>Student Name:</strong> {{ $student->name }}</p>
        <p><strong>Email:</strong> {{ $student->email }}</p>
        <p><strong>Phone:</strong> {{ $student->phone }}</p>
    </div>

    @foreach($grades as $academicYearId => $semesters)
    @php
    $academicYear = $semesters->first()->first()->academicYear;
    @endphp
    <h2>Academic Year: {{ $academicYear->name }}</h2>

    @foreach($semesters as $semester => $semesterGrades)
    <div class="semester-header">
        Semester {{ $semester }}
    </div>

    <table class="grade-table">
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
            @foreach($semesterGrades as $grade)
            <tr>
                <td>{{ $grade->subject->name }}</td>
                <td>{{ $grade->class->name }}</td>
                <td>{{ $grade->score }}</td>
                <td>
                    <span class="status {{ $grade->isPassed() ? 'passed' : 'failed' }}">
                        {{ $grade->isPassed() ? 'Passed' : 'Failed' }}
                    </span>
                </td>
                <td>{{ $grade->notes ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Semester Average:</strong> {{ number_format($semesterGrades->avg('score'), 2) }}</p>
        <p><strong>Passed Subjects:</strong> {{ $semesterGrades->filter(fn($grade) => $grade->isPassed())->count() }} of {{ $semesterGrades->count() }}</p>
    </div>
    @endforeach
    @endforeach
</body>

</html>