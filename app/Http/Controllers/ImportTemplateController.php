<?php

namespace App\Http\Controllers;

use App\Exports\GradeTemplate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class ImportTemplateController extends Controller
{
    public function importTemplate(Request $request)
    {
        $teacherId = Auth::id();
        $teacherName = Auth::user()->name;

        $fileName = strtolower(str_replace(' ', '-', $teacherName)) . '-grades-template.csv';

        return Excel::download(new GradeTemplate($teacherId), $fileName, \Maatwebsite\Excel\Excel::CSV);
    }
}
