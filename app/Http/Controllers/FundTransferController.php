<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashEquivalent;
use App\Models\FundTransfer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FundTransferController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        // Fund transfers query
        $query = FundTransfer::with([
            'createdBy', 
            'methodOfTransfer', 
            'fromCashEquivalent', 
            'toCashEquivalent'
        ])->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $fundTransfers = $query->paginate(25);

        // Load cash equivalents and payments for the Add & Edit modals
        $cashEquivalents = CashEquivalent::all();
        $payments = Payment::all();
        $users = User::where('status', 'active')->get();

        $branches = Branch::all();
        $currentBranchId = current_branch_id();

        return view('fund-transfers.index', compact(
            'fundTransfers', 
            'status', 
            'cashEquivalents', 
            'payments', 
            'users',
            'branches', 
            'currentBranchId',
        ));
    }

    public function create()
    {
        $cashEquivalents = CashEquivalent::all();
        $payments = Payment::all();
        $users = User::where('status', 'active')->get();

        return view('fund_transfers.create', compact('cashEquivalents', 'payments', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string|max:255',
            'method_of_transfer_id' => 'nullable|exists:payments,id',
            'from_cash_equivalent_id' => 'required|exists:cash_equivalents,id',
            'to_cash_equivalent_id' => 'required|exists:cash_equivalents,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        // Case-insensitive check for duplicate reference numbers
        $reference = $request->input('reference_number');
        $exists = FundTransfer::whereRaw('LOWER(reference_number) = ?', [strtolower($reference)])->exists();

        if ($exists) {
            return back()->withErrors([
                'reference_number' => 'A fund transfer with that reference number already exists.',
            ])->withInput();
        }

        FundTransfer::create([
            'reference_number' => $request->reference_number,
            // 'branch_id' => $request->branch_id, // ✅ Add this
            'method_of_transfer_id' => $request->method_of_transfer_id,
            'from_cash_equivalent_id' => $request->from_cash_equivalent_id,
            'to_cash_equivalent_id' => $request->to_cash_equivalent_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'status' => 'pending',
        ]);

        return redirect()->route('fund-transfers.index', ['status' => $request->get('status', 'pending')])
            ->with('success', 'Fund transfer created successfully.');
    }

    public function show($id)
    {
        $fundTransfer = FundTransfer::with(['createdBy', 'actionBy', 'methodOfTransfer', 'fromCashEquivalent', 'toCashEquivalent'])->findOrFail($id);
        return view('fund_transfers.show', compact('fundTransfer'));
    }

    public function edit($id)
    {
        $fundTransfer = FundTransfer::findOrFail($id);
        $cashEquivalents = CashEquivalent::all();
        $payments = Payment::all();

        return view('fund_transfers.edit', compact('fundTransfer', 'cashEquivalents', 'payments'));
    }

    public function update(Request $request, $id)
    {
        $fundTransfer = FundTransfer::findOrFail($id);

        $validated = $request->validate([
            'reference_number' => 'required|string|unique:fund_transfers,reference_number,' . $fundTransfer->id,
            'method_of_transfer_id' => 'nullable|exists:payments,id',
            'from_cash_equivalent_id' => 'required|exists:cash_equivalents,id',
            'to_cash_equivalent_id' => 'required|exists:cash_equivalents,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        $fundTransfer->update($validated);

        return redirect()->route('fund-transfers.index')->with('success', 'Fund transfer updated.');
    }

    /**
     * Approve the fund transfer
     */
    public function approve($id)
    {
        $ft = FundTransfer::findOrFail($id);
        $ft->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_datetime' => now(),
        ]);

        return redirect()->back()->with('success', 'Fund transfer approved.');
    }

    /**
     * Archive the fund transfer
     */
    public function archive($id)
    {
        $ft = FundTransfer::findOrFail($id);
        $ft->update([
            'status' => 'archived',
            'archived_by' => Auth::id(),
            'archived_datetime' => now(),
        ]);

        return redirect()->back()->with('success', 'Fund transfer archived.');
    }

    public function uploadAttachments(Request $request)
    {
        $validated = $request->validate([
            'fund_transfer_id' => 'required|exists:fund_transfers,id',
            'attachments.*' => 'required|file|max:5120', // 5MB
        ]);

        $ft = FundTransfer::findOrFail($validated['fund_transfer_id']);
        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('fund_transfer_attachments', 'public');
                $uploadedFiles[] = $path;
            }
        }

        $existing = $ft->attachments ?? [];
        $allFiles = array_merge($existing, $uploadedFiles);

        $ft->update([
            'attachments' => $allFiles,
        ]);

        return back()->with('success', 'Files attached successfully.');
    }


    public function getAttachments($id)
    {
        $ft = FundTransfer::findOrFail($id);
        return response()->json([
            'attachments' => $ft->attachments ?? []
        ]);
    }
}
