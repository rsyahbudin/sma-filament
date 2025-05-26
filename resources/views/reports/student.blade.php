<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Student Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .student-info {
            margin-bottom: 20px;
        }

        .academic-year {
            margin-bottom: 30px;
        }

        .semester {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
        }

        .status-passed {
            color: green;
        }

        .status-failed {
            color: red;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Student Report</h1>
        <h2>{{ $student->name }}</h2>
    </div>

    <div class="student-info">
        <p><strong>Email:</strong> {{ $student->email }}</p>
        <p><strong>Phone:</strong> {{ $student->phone }}</p>
        <p><strong>Address:</strong> {{ $student->address }}</p>
    </div>

    @foreach($academicYears as $yearData)
    <div class="academic-year">
        <h3>Academic Year: {{ $yearData['year']->name }}</h3>

        @foreach($yearData['semesters'] as $semesterData)
        <div class="semester">
            <h4>Semester {{ $semesterData['semester'] }}</h4>

            <table>
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
                        <td class="{{ $grade->score >= $grade->subject->minimum_score ? 'status-passed' : 'status-failed' }}">
                            {{ $grade->score >= $grade->subject->minimum_score ? 'Passed' : 'Failed' }}
                        </td>
                        <td>{{ $grade->notes }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
    @endforeach

    <div class="footer">
        <p>Generated on: {{ now()->format('d F Y H:i:s') }}</p>
    </div>
</body>

</html>