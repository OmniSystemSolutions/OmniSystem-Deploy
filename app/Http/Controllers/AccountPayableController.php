<?php

namespace App\Http\Controllers;

use App\Models\AccountPayable;
use App\Models\AccountPayableDetail;
use App\Models\AccountingCategory;
use App\Models\Branch;
use App\Models\ChartAccount;
use App\Models\PaymentDetail;
use App\Models\Tax;
use Illuminate\Http\Request;

class AccountPayableController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $accountPayables = AccountPayable::with('details.category')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('accounts-payables.index', compact('accountPayables', 'status'));
    }

    public function create()
    {
        $categories = ChartAccount::with(['category', 'subCategory'])
    // Filter by the 'classification' column in the 'accounting_categories' table
    ->whereHas('category', function($query) {
        $query->where('classification', 'debit');
    })
    ->get();

        $branches = Branch::all();
        $currentBranch = auth()->check() ? auth()->user()->branches()->first() : null;
        $currentBranchId = $currentBranch->id ?? ($branches->first()->id ?? null);

        $taxes = Tax::all();
        $isEdit = false;

        return view('accounts-payables.form', compact('categories', 'branches', 'currentBranchId', 'taxes','isEdit'));
    }

    public function store(Request $request)
    {
        // dd($request->all()); // 👈 ADD THIS FIRST
        // Convert JSON string to array
        $details = json_decode($request->details, true);

        if (!$details || count($details) < 1) {
            return back()->withErrors(['details' => 'Please add at least 1 summary item.']);
        }

        $validated = $request->validate([
            'reference_number' => 'required|unique:account_payables,reference_number',
            'payor_details' => 'nullable|string',
            'payer_name' => 'nullable|string',
            'payer_company' => 'nullable|string',
            'payer_address' => 'nullable|string',
            'payer_mobile_number' => 'nullable|string',
            'payer_email_address' => 'nullable|string',
            'payer_tin' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        // Create main AP record
        $payable = AccountPayable::create($validated);

        // Insert EACH detail item
        foreach ($details as $row) {
            $chartAccount = ChartAccount::find($row['chart_account_id']);
            
            AccountPayableDetail::create([
                'account_payable_id'     => $payable->id,
                'chart_account_id'       => $row['chart_account_id'],
                'accounting_category_id' => $chartAccount->accounting_category_id, // ✅ fill this
                'description'            => $row['description'],
                'quantity'               => $row['quantity'],
                'tax_id'                 => $row['tax_id'] ?? null,
                'tax_value'              => $row['tax_value'] ?? 0,
                'tax_type'               => $row['tax_type'] ?? null,
                'amount_per_unit'        => $row['amount_per_unit'],
                'total_amount'           => $row['total_amount'],
            ]);
        }

        return redirect()->route('accounts-payables.index')
            ->with('success', 'Account Payable created successfully.');
    }

//     public function store(Request $request)
// {
//     \DB::beginTransaction();
//     try {

//         // 1. Create main AP record
//         $ap = AccountPayable::create([
//             'reference_number' => $request->reference_number,
//             'payor_details' => $request->payor_details,
//             'payer_name' => $request->payer_name,
//             'payer_company' => $request->payer_company,
//             'payer_address' => $request->payer_address,
//             'payer_mobile_number' => $request->payer_mobile_number,
//             'payer_email_address' => $request->payer_email_address,
//             'payer_tin' => $request->payer_tin,
//             'due_date' => $request->due_date,
//             'created_at' => $request->created_at,
//         ]);

//         // 2. Decode summary details array
//         $details = json_decode($request->details, true);

//         foreach ($details as $d) {
//             AccountPayableDetail::create([
//                 'account_payable_id'   => $ap->id,
//                 'accounting_category_id' => $d['accounting_category_id'],  // INT
//                 'description'          => $d['description'],
//                 'quantity'             => $d['quantity'],
//                 'tax_id' => $d['tax_id'],
//                 'amount_per_unit'      => $d['amount_per_unit'],
//                 'total_amount'         => $d['total_amount'],
//             ]);
//         }

//         \DB::commit();

//         return redirect()->route('accounts-payables.index')
//             ->with('success', 'Accounts Payable created successfully!');

//     } catch (\Throwable $e) {
//         \DB::rollBack();
//         dd($e->getMessage()); // TEMPORARY DEBUG
//     }
// }

    public function show($id)
    {
        $ap = AccountPayable::with('details.category')->findOrFail($id);
        return view('account_payables.show', compact('ap'));
    }

    public function edit($id)
{
    $ap = AccountPayable::with('details.chartAccount.category', 'details.chartAccount.subCategory')
        ->findOrFail($id);

    // SAME FILTER as create (debit only)
    $categories = ChartAccount::with(['category', 'subCategory'])
        ->whereHas('category', function($query) {
            $query->where('classification', 'debit');
        })
        ->get();

    $branches = Branch::all();
    $currentBranch = auth()->check() ? auth()->user()->branches()->first() : null;
    $currentBranchId = $currentBranch->id ?? ($branches->first()->id ?? null);

    $taxes = Tax::all();

    // Prepare details for JS (NO tax_value / tax_type)
    $detailsArray = $ap->details->map(function($d) {
        return [
            'chart_account_id' => $d->chart_account_id,
            'category_name'    => $d->chartAccount->category->category ?? '',
            'subcategory_name' => $d->chartAccount->subCategory->name ?? '',
            'description'      => $d->description,
            'quantity'         => $d->quantity,
            'tax_id'           => $d->tax_id,
            'amount_per_unit'  => $d->amount_per_unit,
            'total_amount'     => $d->total_amount,
        ];
    })->toArray();

    return view('accounts-payables.form', compact(
        'ap',
        'categories',
        'branches',
        'currentBranchId',
        'taxes',
        'detailsArray'
    ))->with('isEdit', true);
}

    public function update(Request $request, $id)
{
    $ap = AccountPayable::findOrFail($id);

    $validated = $request->validate([
        'payor_details' => 'nullable|string',
    ]);

    $ap->update($validated);

    $details = json_decode($request->details, true);

    // DELETE old details
    $ap->details()->delete();

    foreach ($details as $row) {

        if(empty($row['chart_account_id'])) continue;

        $chartAccount = ChartAccount::find($row['chart_account_id']);
        if(!$chartAccount) continue;

        AccountPayableDetail::create([
            'account_payable_id'     => $ap->id,
            'chart_account_id'       => $row['chart_account_id'],
            'accounting_category_id' => $chartAccount->accounting_category_id,
            'description'            => $row['description'] ?? '',
            'quantity'               => $row['quantity'] ?? 0,
            'tax_id'                 => $row['tax_id'] ?? null,
            'amount_per_unit'        => $row['amount_per_unit'] ?? 0,
            'total_amount'           => $row['total_amount'] ?? 0,
        ]);
    }

    return redirect()->route('accounts-payables.index')
        ->with('success', 'Account Payable updated successfully.');
}

    public function destroy($id)
    {
        $ap = AccountPayable::findOrFail($id);
        $ap->delete();

        return redirect()->route('account_payables.index')
            ->with('warning', 'Account Payable deleted.');
    }

    public function getDetails($id)
    {
        $details = AccountPayableDetail::with('category')
            ->where('account_payable_id', $id)
            ->get();

        return response()->json($details);
    }

    public function amountDetails($id)
    {
        $ap = AccountPayable::with('details.category')->findOrFail($id);

        $tax = $ap->details->sum('tax_id');
        $sub = $ap->details->sum(fn($d) => $d->quantity * $d->amount_per_unit);
        $total = $ap->details->sum('total_amount');

        return response()->json([
            'tax' => number_format($tax, 2),
            'sub_total' => number_format($sub, 2),
            'total' => number_format($total, 2),
        ]);
    }

    public function approve($id)
    {
        $po = AccountPayable::findOrFail($id);
            $po->update([
                'status' => 'approved',
            ]);

        return redirect()->route('accounts-payables.index')
            ->with('success', 'Purchase Order approved successfully.');
    }

    public function disapprove($id)
    {
        $po = AccountPayable::findOrFail($id);
        $po->update([
            'status' => 'disapproved',
        ]);

        return redirect()->route('accounts-payables.index')
            ->with('warning', 'Purchase Order disapproved.');
    }

    /**
     * Archive an Account Payable
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function archive($id)
    {
        $po = AccountPayable::findOrFail($id);
        
        $po->update([
            'status' => 'archived',
        ]);

        return redirect()->route('accounts-payables.index', ['status' => 'archived'])
            ->with('warning', 'Purchase Order moved to archive.');
    }

    public function makePayment(Request $request)
    {
        $validated = $request->validate([
            'account_payable_id'   => 'required|exists:account_payables,id',
            'payment_date'         => 'required|date',
            'amount_to_pay'        => 'required|numeric|min:0',
            'cash_equivalent_id'   => 'required|exists:cash_equivalents,id',
            'payment_id'           => 'required|exists:payments,id',
        ]);

        \DB::transaction(function () use ($validated) {
            $ap = AccountPayable::findOrFail($validated['account_payable_id']);

            // 1️⃣ Get the main detail row (the one that has the original total_amount)
            $apDetail = $ap->details()->first(); // assuming 1 detail per AP for simplicity

            // 2️⃣ Add the payment to the existing amount_to_pay
            $apDetail->amount_to_pay += $validated['amount_to_pay'];
            $apDetail->payment_id = $validated['payment_id'];
            $apDetail->cash_equivalent_id = $validated['cash_equivalent_id'];
            $apDetail->save();

            // 3️⃣ Update AP status if fully paid
            if ($apDetail->amount_to_pay >= $apDetail->total_amount) {
                $ap->status = 'completed';
            } else {
                // keep existing status (pending or approved)
                $ap->status = $ap->status === 'pending' ? 'approved' : $ap->status;
            }
            $ap->save();
        });

        return redirect()->back()->with('success', 'Payment successfully recorded.');
    }

    public function getTypes($category)
    {
        // find type rows where category_payable equals selected category
        $types = \App\Models\AccountingCategory::where('category_payable', $category)
            ->whereNotNull('type_payable')
            ->orderBy('type_payable')
            ->get(['id', 'type_payable']);

        return response()->json($types);
    }

    // public function getTypes($id)
    // {
    //     $types = AccountingCategory::where('id', $id)->first();
    //     return $types->type_payable_list; // make sure this returns id + name
    // }


}
