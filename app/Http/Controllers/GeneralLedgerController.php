<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashEquivalent;
use App\Models\FundTransfer;
use App\Models\Order;
use App\Models\AccountPayable;
use App\Models\AccountsReceivables;


class GeneralLedgerController extends Controller
{
    public function index()
    {
        $cash_equivalents = CashEquivalent::all()->map(function ($ce) {
            $excluded = ['cash on hand', 'revolving fund', 'petty cash'];
            $nameLower = strtolower($ce->name);

             // Use accountable user's name if in excluded list
            $ce->display_label = in_array($nameLower, $excluded)
                ? $ce->name . ' - ' . ($ce->accountable ? $ce->accountable->name : 'N/A')
                : $ce->name . ' - ' . $ce->account_number;

                return $ce;
            });

        return view('reports.general-ledger.index', [
            'cashEquivalents' => $cash_equivalents
        ]);
    }

    public function fetchRequests(Request $request)
    {
        // =========================
        // FUND TRANSFERS
        // =========================
        $ftQuery = FundTransfer::with(['approvedByUser', 'archivedByUser', 'fromCashEquivalent', 'toCashEquivalent']);

        if ($request->cash_equivalent_id) {
            $ftQuery->where(function ($q) use ($request) {
                $q->where('from_cash_equivalent_id', $request->cash_equivalent_id)
                ->orWhere('to_cash_equivalent_id', $request->cash_equivalent_id);
            });
        }

        if ($request->start_date && $request->end_date) {
            $ftQuery->whereBetween('approved_datetime', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $fundTransfers = $ftQuery->get()->map(function ($row) use ($request) {
            $isIncoming = $row->to_cash_equivalent_id == $request->cash_equivalent_id;

            return [
                'id' => 'FT-' . $row->id,
                'date' => $row->approved_datetime,
                'transaction' => 'Fund Transfer',
                'reference_number' => $row->reference_number,
                'type' => 'Transfer',
                'description' => $row->description,
                'payor' => $row->fromCashEquivalent->account_name ?? '-',
                'payee' => $row->toCashEquivalent->account_name ?? '-',
                'payment_method' => $row->methodOfTransfer->name ?? '-',
                'credit' => $isIncoming ? $row->amount : 0, // incoming
                'debit' => !$isIncoming ? $row->amount : 0, // outgoing
            ];
        });

        // =========================
        // ORDERS (NEW PART)
        // =========================
        $orderQuery = Order::query();

        // Filter by date range (paid_datetime)
        if ($request->start_date && $request->end_date) {
            $orderQuery->whereBetween('paid_datetime', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // OPTIONAL: filter by cash equivalent if applicable
        if ($request->cash_equivalent_id) {
            $orderQuery = Order::with(['paymentDetails.cashEquivalent']);

        // Filter by date range (paid_datetime)
        if ($request->start_date && $request->end_date) {
            $orderQuery->whereBetween('paid_datetime', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter by cash equivalent THROUGH payment_details
        if ($request->cash_equivalent_id) {
            $orderQuery->whereHas('paymentDetails', function ($q) use ($request) {
                $q->where('cash_equivalent_id', $request->cash_equivalent_id);
            });
        }
        }

        $orders = $orderQuery->get()->flatMap(function ($order) use ($request) {

        return $order->paymentDetails
            ->where('cash_equivalent_id', $request->cash_equivalent_id) // filter again for safety
            ->map(function ($pd) use ($order) {

                return [
                    'id' => 'OR-' . $order->id . '-' . $pd->id,
                    'date' => $order->paid_datetime,
                    'transaction' => 'Order Payment',
                    'reference_number' => $pd->transaction_reference_no ?? $order->id,
                    'type' => 'Sales',
                    'description' => 'Customer Order Payment',
                    'payor' => $order->customer_name ?? 'Customer',
                    'payee' => 'Business',
                    'payment_method' => $pd->payment->name ?? '-',
                    'debit' => 0,
                    'credit' =>  $pd->amount_paid, // 💰 correct amount per payment method
                ];
            });
        });

        // =========================
        // ACCOUNT PAYABLES
        // =========================
        $apQuery = AccountPayable::with(['details.payment', 'details.cashEquivalent']);

        // Filter: ONLY completed
        $apQuery->where('status', 'completed');

        // Filter by paid_datetime
        if ($request->start_date && $request->end_date) {
            $apQuery->whereBetween('paid_datetime', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->cash_equivalent_id) {
            $apQuery->whereHas('details', function ($q) use ($request) {
                $q->where('cash_equivalent_id', $request->cash_equivalent_id);
            });
        }

        $accountPayables = $apQuery->get()->map(function ($ap) use ($request) {

            $details = $ap->details
                ->when($request->cash_equivalent_id, function ($collection) use ($request) {
                    return $collection->where('cash_equivalent_id', $request->cash_equivalent_id);
                });

            $totalAmount = $details->sum('amount_to_pay');

            return [
                'id' => 'AP-' . $ap->id,
                'date' => $ap->paid_datetime,
                'transaction' => 'Account Payable',
                'reference_number' => $ap->reference_number,
                'type' => 'Expense',
                'description' => 'Account Payable Payment',
                'payor' => 'Business',
                'payee' => $ap->payer_name ?? $ap->payer_company ?? '-',

                // ✅ FIXED: based only on filtered details
                'payment_method' => $details
                    ->map(fn($d) => $d->payment->name ?? null)
                    ->filter()
                    ->unique()
                    ->implode(', ') ?: '-',

                'debit' => $totalAmount,
                'credit' => 0,
            ];
        });

        // =========================
        // ACCOUNTS RECEIVABLE
        // =========================
        $arQuery = AccountsReceivables::with([
            'payments.paymentMethod',
            'payments.cashEquivalent'
        ]);

        // Only completed
        $arQuery->where('status', 'completed');

        // Filter by completed_at
        if ($request->start_date && $request->end_date) {
            $arQuery->whereBetween('completed_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter by cash equivalent (through payments)
        if ($request->cash_equivalent_id) {
            $arQuery->whereHas('payments', function ($q) use ($request) {
                $q->where('cash_equivalent_id', $request->cash_equivalent_id);
            });
        }

        $accountsReceivables = $arQuery->get()->flatMap(function ($ar) use ($request) {

            return $ar->payments
                ->when($request->cash_equivalent_id, function ($collection) use ($request) {
                    return $collection->where('cash_equivalent_id', $request->cash_equivalent_id);
                })
                ->map(function ($payment) use ($ar) {

                    return [
                        'id' => 'AR-' . $ar->id . '-' . $payment->id,
                        'date' => $ar->completed_at, // ✅ as requested
                        'transaction' => 'Accounts Receivable',
                        'reference_number' => $ar->reference_no,
                        'type' => 'Collection',
                        'description' => 'AR Payment Collection',
                        'payor' => $ar->payor_name ?? $ar->company ?? '-',
                        'payee' => 'Business',
                        'payment_method' => $payment->paymentMethod->name ?? '-',
                        'debit' => 0,
                        'credit' => $payment->amount, // 💰 money coming in
                    ];
                });
        });

        // =========================
        // MERGE + SORT
        // =========================
        $data = $fundTransfers
            ->concat($orders)
            ->concat($accountPayables)
            ->concat($accountsReceivables)
            ->sortBy('date') // IMPORTANT for running balance
            ->values();

        return response()->json($data);
    }
}
