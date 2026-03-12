<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentDetail;
use Illuminate\Http\Request;
use App\Models\User;

class SalesJournalController extends Controller
{
public function index(Request $request)
{
    $users = User::all();
    $branchId = current_branch_id();

    $year  = $request->input('year');
    $month = $request->input('month');

    // Default: all dates
    $from = null;
    $to   = null;

    if ($year && $month && $month !== 'all') {
        // Specific month of a year
        $from = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $to   = Carbon::createFromDate($year, $month, 1)->endOfMonth();
    } elseif ($year) {
        // Whole year
        $from = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $to   = Carbon::createFromDate($year, 12, 31)->endOfYear();
    }

    $ordersQuery = Order::with('cashier')
        ->where('branch_id', $branchId)
        ->where('status', 'payments');

    if ($from && $to) {
        $ordersQuery->whereBetween('updated_at', [$from, $to]);
    }

    $orders = $ordersQuery
        ->orderBy('updated_at', 'desc')
        ->get();

    $totalTransactions = (clone $ordersQuery)->count();

    $discounts = $orders->sum('sr_pwd_discount')  + $orders->sum('other_discounts');

    $grossTotal = (clone $ordersQuery)->sum('total_charge') + $discounts;
    
    // SALES BREAKDOWN BY ORDER TYPE
    $salesBreakdown = Order::where('branch_id', $branchId)
    ->where('status', 'payments')
    ->when($from && $to, function ($q) use ($from, $to) {
        $q->whereBetween('updated_at', [$from, $to]);
    })
    ->selectRaw('order_type, SUM(total_charge) as total')
    ->groupBy('order_type')
    ->pluck('total', 'order_type');

    // Ensure missing types return 0
    $chartData = [
        'Dine-In' => $salesBreakdown['Dine-In'] ?? 0,
        'Take-Out' => $salesBreakdown['Take-Out'] ?? 0,
        'Delivery' => $salesBreakdown['Delivery'] ?? 0,
    ];

    $summary = [
        'total_transactions' => $totalTransactions,
        'gross_total' => $grossTotal,
        'salesBreakdown' => $chartData,
    ];

    return view('reports.sales-journal', compact(
        'orders',
        'users',
        'summary',
        'year',
        'month',
        'chartData'
    ));
}

public function xReport(Request $request)
{
    $date = $request->input('date');
    $cashierId = $request->input('cashier_id');
    $branchId = current_branch_id();

    if (!$date || !$cashierId) {
        return response()->json([
            'error' => 'Date and cashier are required.'
        ], 422);
    }

    try {
        $from = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $to   = Carbon::createFromFormat('Y-m-d', $date)->endOfDay();
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Invalid date format.'
        ], 422);
    }

    $orders = Order::with([
            'orderDetails.product',
            'orderDetails.component',
            'paymentDetails.payment',
            'cashier'
        ])
        ->where('branch_id', $branchId) // ✅ CURRENT BRANCH
        ->where('status', 'payments')
        ->where('cashier_id', $cashierId)
        ->whereBetween('updated_at', [$from, $to])
        ->orderBy('updated_at', 'asc')
        ->get();

    /* ---------------------------
       SALES SUMMARY
    --------------------------- */

    $totalOrders = $orders->count();

    $discounts = $orders->sum('sr_pwd_discount')  + $orders->sum('other_discounts');

    $grossSales = $orders->sum('total_charge') + $discounts;

    $tax = $orders->sum('vat_12');

    $netSales = $orders->sum('total_charge');


    /* ---------------------------
       PAYMENT BREAKDOWN
    --------------------------- */

    $cash = $orders->sum(function ($order) {
        return $order->paymentDetails
            ->where('payment.name', 'Cash')
            ->sum('amount_paid') - $order->change_amount;
    });

    $card = $orders->sum(function ($order) {
        return $order->paymentDetails
            ->whereIn('payment.name', ['Debit Card','Credit Card'])
            ->sum('amount_paid');
    });

    $eWallet = $orders->sum(function ($order) {
        return $order->paymentDetails
            ->where('payment.name', 'GCash')
            ->sum('amount_paid');
    });


    /* ---------------------------
       ITEMS SOLD
    --------------------------- */

    $orderDetails = collect();

    foreach ($orders as $order) {

        foreach ($order->orderDetails as $detail) {

            $key = $detail->product_id ?? $detail->component_id;

            $name = $detail->product->name
                ?? $detail->component->name;

            $orderDetails->push([
                'key' => $key,
                'name' => $name,
                'quantity' => $detail->quantity,
                'total' => $detail->price * $detail->quantity
            ]);
        }
    }

    $orderDetailsGrouped = $orderDetails
        ->groupBy('key')
        ->map(function ($items) {

            return [
                'name' => $items->first()['name'],
                'quantity' => $items->sum('quantity'),
                'total' => $items->sum('total')
            ];
        })
        ->values();


    /* ---------------------------
       RESPONSE
    --------------------------- */

    $report = [

        'date' => $from->format('Y-m-d'),

        'cashier' => $orders->first()?->cashier->name ?? null,

        'total_orders' => $totalOrders,

        'gross_sales' => $grossSales,

        'discounts' => $discounts,

        'net_sales' => $netSales,

        'tax' => $tax,

        'payments' => [
            'cash' => $cash,
            'card' => $card,
            'e_wallet' => $eWallet,
        ],

        'order_details' => $orderDetailsGrouped
    ];

    return response()->json($report);
}

   public function zReport(Request $request)
{
    $branchId = current_branch_id();

    $date = $request->input('date')
        ? Carbon::createFromFormat('Y-m-d', $request->date)
        : Carbon::today();

    $startOfDay = $date->copy()->startOfDay();
    $endOfDay   = $date->copy()->endOfDay();

    $orders = Order::with(['orderDetails.product','orderDetails.component','paymentDetails.payment'])
        ->where('branch_id', $branchId) // ✅ current branch
        ->where('status','payments')
        ->whereBetween('updated_at', [$startOfDay, $endOfDay])
        ->get();


    /* ---------------------------
       SALES SUMMARY
    --------------------------- */

    $totalOrders = $orders->count();

    $discounts = $orders->sum('sr_pwd_discount')  + $orders->sum('other_discounts');

    $grossSales = $orders->sum('total_charge') + $discounts;

    $netSales = $orders->sum('total_charge');

    $tax = $orders->sum('vat_12');


    /* ---------------------------
       PAYMENT METHODS
    --------------------------- */

    $payments = PaymentDetail::whereHas('order', function ($q) use ($startOfDay,$endOfDay,$branchId) {
        $q->where('branch_id', $branchId) // ✅ branch filter
          ->where('status','payments')
          ->whereBetween('updated_at', [$startOfDay,$endOfDay]);
    })
    ->with('payment')
    ->get()
    ->groupBy('payment.name')
    ->map(function ($items) {
        return $items->sum('amount_paid');
    });


    /* ---------------------------
       ITEMS SOLD
    --------------------------- */

    $items = OrderDetail::whereHas('order', function ($q) use ($startOfDay,$endOfDay,$branchId) {
        $q->where('branch_id', $branchId) // ✅ branch filter
          ->where('status','payments')
          ->whereBetween('updated_at', [$startOfDay,$endOfDay]);
    })
    ->with(['product','component'])
    ->get()
    ->groupBy(function ($item) {
        return $item->product->name ?? $item->component->name;
    })
    ->map(function ($items) {
        return $items->sum('quantity');
    });


    return response()->json([
        'date' => $date->format('M d, Y'),
        'time' => now()->format('h:i A'),

        'total_orders' => $totalOrders,
        'gross_sales' => $grossSales,
        'discounts' => $discounts,
        'net_sales' => $netSales,
        'tax' => $tax,

        'payments' => $payments,
        'items' => $items
    ]);
}
}