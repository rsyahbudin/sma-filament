<?php

namespace App\Http\Controllers;

use App\Exports\GradesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class GradeExportController extends Controller
{
    public function exportGradesCsv()
    {
        $user = Auth::user();

        $isAdmin = $user->role && $user->role->name === 'Admin';

        if ($isAdmin) {
            $fileName = 'admin-grades-export.xlsx';
        } else {
            $fileName = strtolower(str_replace(' ', '-', $user->name)) . '-grades-export.xlsx';
        }

        return Excel::download(new GradesExport, $fileName);
    }
}
