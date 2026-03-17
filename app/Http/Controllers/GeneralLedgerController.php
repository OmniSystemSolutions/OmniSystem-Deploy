<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashEquivalent;
use App\Models\FundTransfer;
use App\Models\Order;

class GeneralLedgerController extends Controller
{
    public function index()
    {
        $cash_equivalents = CashEquivalent::all();
        
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
                'debit' => $isIncoming ? $row->amount : 0,
                'credit' => !$isIncoming ? $row->amount : 0,
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
                    'debit' => $pd->amount_paid, // 💰 correct amount per payment method
                    'credit' => 0,
                ];
            });
    });

        // =========================
        // MERGE + SORT
        // =========================
        $data = $fundTransfers
            ->concat($orders)
            ->sortBy('date') // IMPORTANT for running balance
            ->values();

        return response()->json($data);
    }
}
