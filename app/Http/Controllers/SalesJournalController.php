<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentDetail;
use Illuminate\Http\Request;

class SalesJournalController extends Controller
{
public function index(Request $request)
{
    $year = $request->input('year');
    $month = $request->input('month') ?? now()->month;
    $from = $request->input('from_date');
    $to = $request->input('to_date');

    $query = Order::where('status', 'payments');

    // 🗓 Apply filters
    if ($year) {
        $query->whereYear('created_at', $year);
    }

    if ($month && $month != 'all') {
        $query->whereMonth('created_at', $month);
    }

    if ($from && $to) {
        try {
            // Detect whether date is "YYYY-MM-DD" or "MM/DD/YYYY"
            if (strpos($from, '/') !== false) {
                $from = Carbon::createFromFormat('m/d/Y', $from)->startOfDay();
                $to = Carbon::createFromFormat('m/d/Y', $to)->endOfDay();
            } else {
                $from = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
                $to = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
            }

            $query->whereBetween('created_at', [$from, $to]);
        } catch (\Exception $e) {
            \Log::error('Invalid date format: ' . $e->getMessage());
        }
    }

    $orders = $query->with('cashier')->orderBy('created_at', 'desc')->get();

    // 🧮 Z Reading Computations
    $discounts = $orders->sum('sr_pwd_discount') + $orders->sum('other_discounts');

    $grossTotal = $orders->sum('total_charge');
    $lessDiscount = $discounts;
    $taxExempt = $orders->sum('sr_pwd_discount') - $orders->sum('vat_12');
    $total = $grossTotal - $lessDiscount - $taxExempt;
    $netTotal = $grossTotal - $lessDiscount - $taxExempt;

    $vat12 = $orders->sum('vat_12');
    $vatIncl = $orders->sum('vatable');
    $vatExcl = $orders->sum('total_charge');

    // 🍽 FOOD TOTAL (filtered by date)
    $foodQuery = OrderDetail::whereHas('order', function ($query) use ($from, $to) {
        $query->where('status', 'payments');
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    })
    ->where(function ($query) {
        $query->whereHas('product.category', function ($q) {
            $q->where('name', 'Food');
        })->orWhereHas('component.category', function ($q) {
            $q->where('name', 'Food');
        });
    });

    $foodTotal = $foodQuery->sum(\DB::raw('price * quantity'));

    // 🍹 DRINKS TOTAL (filtered by date)
    $drinksQuery = OrderDetail::whereHas('order', function ($query) use ($from, $to) {
        $query->where('status', 'payments');
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    })
    ->where(function ($query) {
        $query->whereHas('product.category', function ($q) {
            $q->where('name', 'Drinks');
        })->orWhereHas('component.category', function ($q) {
            $q->where('name', 'Drinks');
        });
    });

    $drinksTotal = $drinksQuery->sum(\DB::raw('price * quantity'));

    $FoodAndDrinksDiscountTotal = $orders->sum('sr_pwd_discount') + $orders->sum('other_discounts');

    // 💵 COLLECTION PER PAYMENT TYPE (filtered by date)
    $paymentTypes = ['Cash', 'GCash', 'Debit Card', 'Credit Card', 'Check'];
    $collections = [];
    $totalCollection = 0;

    foreach ($paymentTypes as $type) {
        $collectionQuery = PaymentDetail::whereHas('order', function ($q) use ($from, $to) {
            $q->where('status', 'payments');
            if ($from && $to) {
                $q->whereBetween('created_at', [$from, $to]);
            }
        })
        ->whereHas('payment', fn($q) => $q->where('name', $type))
        ->get();

        $collections[strtolower(str_replace(' ', '_', $type))] = $collectionQuery->sum(function ($detail) use ($type) {
            if ($type === 'Cash') {
                return $detail->amount_paid - $detail->order->change_amount;
            }
            return $detail->amount_paid;
        });

        $totalCollection += $collections[strtolower(str_replace(' ', '_', $type))];
    }

    // 📊 Summary
    $summary = [
        'total_transactions' => $orders->count(),
        'gross_total' => $grossTotal,
        'less_discount' => $lessDiscount,
        'tax_exempt' => $taxExempt,
        'net_total' => $netTotal,
        'vat_12' => $vat12,
        'vat_inclusive' => $vatIncl,
        'vat_exclusive' => $vatExcl,
        'food_total' => $foodTotal,
        'drinks_total' => $drinksTotal,
        'food_and_drinks_discount_total' => $FoodAndDrinksDiscountTotal,
        'collections' => $collections,
        'total_collection' => $totalCollection,
    ];

    // ✅ AJAX Response
    if ($request->ajax()) {
        return response()->json($summary);
    }

    return view('reports.sales-journal', compact('orders', 'summary', 'year', 'month'));
}

public function xReport(Request $request)
{
    $date = $request->input('date');
    $cashierId = $request->input('cashier_id');

    if (!$date || !$cashierId) {
        return response()->json(['error' => 'Date and cashier are required.'], 422);
    }

    // Parse date
    try {
        $from = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $to = Carbon::createFromFormat('Y-m-d', $date)->endOfDay();
    } catch (\Exception $e) {
        return response()->json(['error' => 'Invalid date format. Use YYYY-MM-DD.'], 422);
    }

    // Get orders for that date and cashier
    $orders = Order::with('orderDetails.product', 'orderDetails.component', 'cashier')
        ->where('status', 'payments')
        ->where('cashier_id', $cashierId)
        ->whereBetween('created_at', [$from, $to])
        ->orderBy('created_at', 'asc')
        ->get();

    $totalOrders = $orders->count();
    $grossSales = $orders->sum('total_charge');
    $discounts = $orders->sum('sr_pwd_discount') + $orders->sum('other_discounts');
    $taxExempt = $orders->sum('sr_pwd_discount') - $orders->sum('vat_12');
    $netSales = $grossSales - $discounts - $taxExempt;
    $tax = $orders->sum('vat_12');

    // Payment breakdown
    $cash = $orders->sum(function($order) { return $order->paymentDetails->where('payment.name', 'Cash')->sum('amount_paid') - $order->change_amount; });
    $card = $orders->sum(function($order) { return $order->paymentDetails->whereIn('payment.name', ['Debit Card','Credit Card'])->sum('amount_paid'); });
    $eWallet = $orders->sum(function($order) { return $order->paymentDetails->where('payment.name', 'GCash')->sum('amount_paid'); });

    // Order details breakdown (grouped by product/component)
    $orderDetails = collect();
    foreach ($orders as $order) {
        foreach ($order->orderDetails as $detail) {
            $key = $detail->product_id ?? $detail->component_id;
            $name = $detail->product->name ?? $detail->component->name;
            $orderDetails->push([
                'key' => $key,
                'name' => $name,
                'quantity' => $detail->quantity,
                'total' => $detail->price * $detail->quantity,
            ]);
        }
    }

    // Group by product/component
    $orderDetailsGrouped = $orderDetails->groupBy('key')->map(function($items) {
        return [
            'name' => $items->first()['name'],
            'quantity' => $items->sum('quantity'),
            'total' => $items->sum('total'),
        ];
    })->values();

    // Response
    $report = [
        'date' => $date,
        'cashier' => $orders->first()?->cashier->name ?? null,
        'total_orders' => $totalOrders,
        'gross_sales' => $grossSales,
        'net_sales' => $netSales,
        'tax' => $tax,
        'payments' => [
            'cash' => $cash,
            'card' => $card,
            'e_wallet' => $eWallet,
        ],
        'order_details' => $orderDetailsGrouped,
    ];

    return response()->json($report);
}

}