<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TableLayout as Layout;

class TableLayoutController extends Controller
{
    public function index() 
    {
        return view('settings.table_layouts.index');
    }
    // ✅ SAVE / UPDATE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'data' => 'required|array'
        ]);

        $layout = Layout::updateOrCreate(
            [
                'branch_id' => current_branch_id(),
                'name' => $request->name
            ],
            [
                'data' => $request->data
            ]
        );

        return response()->json([
            'success' => true,
            'layout' => $layout
        ]);
    }

    // ✅ GET ALL (for dropdown)
    public function list()
    {
        return response()->json(
            Layout::where('branch_id', current_branch_id())
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
        );
    }

    // ✅ GET SINGLE (for loading layout)
    public function show($id)
    {
        $layout = Layout::where('branch_id', current_branch_id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $layout->data
        ]);
    }
}
