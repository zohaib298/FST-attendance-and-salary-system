<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AttendanceImport;
use Illuminate\Support\Facades\DB;

class AttendanceImportController extends Controller
{
    public function import(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|mimes:xlsx,xls,csv|max:10240',
            ]);

            DB::beginTransaction();
            
            $import = new AttendanceImport();
            Excel::import($import, $request->file('excel_file'));
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'imported' => $import->getImportedCount(),
                'message' => 'Data imported successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}