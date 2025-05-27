<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Grade Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .grade-info {
            margin-bottom: 20px;
        }

        .grade-info p {
            margin: 5px 0;
        }

        .grade-details {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .grade-details th,
        .grade-details td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .grade-details th {
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
    </style>
</head>

<body>
    <div class="header">
        <h1>Grade Report</h1>
    </div>

    <div class="grade-info">
        <p><strong>Student:</strong> {{ $grade->student->name }}</p>
        <p><strong>Subject:</strong> {{ $grade->subject->name }}</p>
        <p><strong>Class:</strong> {{ $grade->class->name }}</p>
        <p><strong>Academic Year:</strong> {{ $grade->academicYear->name }}</p>
        <p><strong>Semester:</strong> {{ $grade->semester }}</p>
    </div>

    <table class="grade-details">
        <tr>
            <th>Score</th>
            <th>Status</th>
            <th>Notes</th>
        </tr>
        <tr>
            <td>{{ $grade->score }}</td>
            <td>
                <span class="status {{ $grade->isPassed() ? 'passed' : 'failed' }}">
                    {{ $grade->isPassed() ? 'Passed' : 'Failed' }}
                </span>
            </td>
            <td>{{ $grade->notes ?? '-' }}</td>
        </tr>
    </table>
</body>

</html>