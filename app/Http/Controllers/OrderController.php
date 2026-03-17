<?php

namespace App\Http\Controllers;

use App\Models\CashEquivalent;
use App\Models\Category;
use App\Models\Component;
use App\Models\Discount;
use App\Models\DiscountEntry;
use App\Models\Product;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\User;
use App\Models\Branch;
use App\Models\CashAudit;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Default to 'serving' tab
        $status = $request->query('status', 'serving');

        // Allow only specific statuses
        $allowedStatuses = ['serving', 'billout', 'payments'];
        if (!in_array($status, $allowedStatuses)) {
            $status = 'serving';
        }

        // Get current branch (GLOBAL HELPER)
        $currentBranchId = current_branch_id();

        // Fetch orders filtered by status
        $orders = Order::with([
                'details' => function ($query) {
                    $query->where('status', '!=', 'walked');
                },
                'details.product',
                'details.component',
                'user',
                'paymentDetails.payment',
                'cashier',
                'discountEntries',
                'reservation',          // ← load linked reservation so blade can show badge
            ])
            ->where('branch_id', $currentBranchId)
            ->when($status === 'serving', function ($q) {
                // ✅ FIX: wrap in a grouped where so branch_id filter is NOT bypassed
                $q->where(function ($inner) {
                    $inner->where('status', 'serving')
                          ->orWhere('status', 'served');
                });
                })
            ->when($status === 'billout',   fn($q) => $q->where('status', 'billout'))
            ->when($status === 'payments',  fn($q) => $q->where('status', 'payments'))
            ->orderByDesc('created_at')
            ->get();

        // Load active discounts
        $discounts = Discount::where('status', 'active')->orderBy('name')->get();

        // Load payment methods and cash equivalents for Payment modal
        $paymentMethods  = Payment::where('status', 'active')->orderBy('name')->get();
        $cashEquivalents = CashEquivalent::where('status', 'active')->orderBy('name')->get();

        // Current branch info
        $branch = Branch::find($currentBranchId);

        return view('orders.index', compact(
            'orders', 'discounts', 'status',
            'paymentMethods', 'cashEquivalents', 'branch'
        ));
    }

   public function create()
    {
        // ✅ Fetch categories with subcategories (include all products, filter components)
        $categories = Category::with([
            'subcategories' => function ($query) {
                $query->with([
                    'products', // all products
                    'components' => function ($c) {
                        $c->where('for_sale', '!=', 0); // only sellable components
                    },
                ]);
            },
        ])->get();

        // ✅ Filter out empty subcategories (no products AND no for_sale components)
        $categories = $categories->filter(function ($category) {
            $category->subcategories = $category->subcategories->filter(function ($sub) {
                $hasProducts = $sub->products && $sub->products->count() > 0;
                $hasComponents = $sub->components && $sub->components->count() > 0;
                return $hasProducts || $hasComponents;
            })->values();

            // Keep category only if it has valid subcategories
            return $category->subcategories->count() > 0;
        })->values();

        // ✅ Fetch all products (no for_sale column)
        $products = Product::with(['category', 'subcategory'])->get();

        // ✅ Fetch components (for_sale only)
        $components = Component::with(['category', 'subcategory'])
            ->where('for_sale', '!=', 0)
            ->get();

        // ✅ Fetch waiters
        $waiters = User::select('id', 'name')->get();

        // ✅ Transform products
        $productsTransformed = $products->map(function ($p) {
            return [
                'id'             => $p->id,
                'sku'            => $p->code,
                'name'           => $p->name,
                'description'    => $p->description ?? '',
                'price'          => $p->price,
                'category_id'    => $p->category_id,
                'subcategory_id' => $p->subcategory_id,
                'category'       => $p->category->name ?? '',
                'subcategory'    => $p->subcategory->name ?? '',
                'image'          => $p->image
                    ? asset('storage/' . $p->image)
                    : 'https://via.placeholder.com/300x200?text=No+Image',
                'type'           => 'product',
            ];
        });

        // ✅ Transform components
        $componentsTransformed = $components->map(function ($c) {
            return [
                'id'             => $c->id,
                'sku'            => $c->code,
                'name'           => $c->name,
                'description'    => $c->description ?? '',
                'price'          => $c->price,
                'category_id'    => $c->category_id,
                'subcategory_id' => $c->subcategory_id,
                'category'       => $c->category->name ?? '',
                'subcategory'    => $c->subcategory->name ?? '',
                'image'          => $c->image
                    ? asset('storage/' . $c->image)
                    : 'https://via.placeholder.com/300x200?text=No+Image',
                'type'           => 'component',
            ];
        });

        // ✅ Merge both
        $allItems = $productsTransformed->merge($componentsTransformed)->values();

        // ✅ Get latest order number
        $latestOrder = Order::latest('id')->first();
        $nextOrderNo = $latestOrder ? $latestOrder->id + 1 : 1;

        // ✅ Return to view
        return view('orders.form', [
            'isEdit'       => false,
            'products'     => $allItems,
            'categories'   => $categories,
            'waiters'      => $waiters,
            'nextOrderNo'  => $nextOrderNo,
        ]);
    }

    public function store(Request $request)
{

    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'table_no' => 'required|integer|min:1',
        'number_pax' => 'required|integer|min:1',
        'status' => 'required|in:serving,billout,payments,closed,cancelled',
        'order_details' => 'required|array|min:1',
        'order_type' => 'required|string|in:Dine-In,Take-Out,Delivery',
        'gross_amount' => 'required|numeric|min:0',

        // allow either product_id OR component_id
        'order_details.*.product_id'   => 'nullable|exists:products,id',
        'order_details.*.component_id' => 'nullable|exists:components,id',

        'order_details.*.quantity' => 'required|integer|min:1',
        'order_details.*.price'    => 'required|numeric|min:0',
        'order_details.*.discount' => 'nullable|numeric|min:0',
        'order_details.*.notes' => 'nullable|string|max:255',

         // ✅ validation for discount entries
        'discount_entries' => 'nullable|array',
        'discount_entries.*.discount_id' => 'required|exists:discounts,id',
        'discount_entries.*.person_name' => 'nullable|string|max:255',
        'discount_entries.*.person_id_number' => 'nullable|string|max:100',
        'discount_entries.*.quantity' => 'required|integer|min:1',
    ]);

    // ✅ Extra validation: must have at least one of product_id or component_id
    foreach ($validated['order_details'] as $detail) {
        if (empty($detail['product_id']) && empty($detail['component_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Each order item must have either a product_id or component_id.'
            ], 422);
        }
    }

    $branchId = $validated['branch_id'] ?? current_branch_id();

    // Create the order
    $order = Order::create([
        'user_id'   => $validated['user_id'],
        'branch_id' => $branchId,
        'table_no'  => $validated['table_no'],
        'number_pax'=> $validated['number_pax'],
        'status'    => $validated['status'],
        'time_submitted' => $request->input('time_submitted'),
        'order_type' => $validated['order_type'],
        'gross_amount'   => $validated['gross_amount'],
        'cashier_id' => auth()->user()->id,
    ]);

    // Attach order details
    foreach ($validated['order_details'] as $detail) {
        $order->details()->create([
            'product_id'   => $detail['product_id']   ?? null,
            'component_id' => $detail['component_id'] ?? null,
            'quantity'     => $detail['quantity'],
            'price'        => $detail['price'],
            'discount'     => $detail['discount'] ?? 0,
            'notes'        => $detail['notes'] ?? null,
            'status'       => 'serving', // 👈 default here
        ]);
    }

    // ✅ Attach discount entries (if any)
    if (!empty($validated['discount_entries'])) {
        foreach ($validated['discount_entries'] as $entry) {
            $order->discountEntries()->create([
                'discount_id'     => $entry['discount_id'],
                'person_name'     => $entry['person_name'] ?? null,
                'person_id_number'=> $entry['person_id_number'] ?? null,
                'quantity'        => $entry['quantity'],
            ]);
        }
    }

    // ✅ Attach discount entries (if any)
    if (!empty($validated['discount_entries'])) {
        foreach ($validated['discount_entries'] as $entry) {
            $order->discountEntries()->create([
                'discount_id'     => $entry['discount_id'],
                'person_name'     => $entry['person_name'] ?? null,
                'person_id_number'=> $entry['person_id_number'] ?? null,
                'quantity'        => $entry['quantity'],
            ]);
        }
    }

    return response()->json([
        'success'  => true,
        'message'  => 'Order created successfully!',
        'redirect' => route('orders.index')
    ]);
    }

    public function billout(Request $request, $orderId)
{
    
    $order = Order::findOrFail($orderId);

    $validated = $request->validate([
        'grossCharge' => 'nullable|numeric|min:0',
        'srPwdBill' => 'nullable|numeric|min:0',
        'regBill' => 'nullable|numeric|min:0',
        'discount20' => 'nullable|numeric|min:0',
        'netBill' => 'nullable|numeric|min:0',
        'vatable' => 'nullable|numeric|min:0',
        'vat12' => 'nullable|numeric|min:0',
        'totalCharge' => 'required|numeric|min:0',
        'otherDiscount' => 'nullable|numeric|min:0',
        'vat_exempt_12' => 'nullable|numeric|min:0',
        'charges_description' => 'nullable|string|max:500',
        'persons' => 'nullable|json'
    ]);

    // dd($validated);

    $order->update([
        'gross_amount' => $validated['grossCharge'] ?? 0,
        'sr_pwd_bill' => $validated['srPwdBill'] ?? 0,
        'sr_pwd_discount' => $validated['discount20'] ?? 0,
        'other_discounts' => $validated['otherDiscount'] ?? 0,
        'net_amount' => $validated['netBill'] ?? 0,
        'regular_bill' => $validated['regBill'] ?? 0,
        'vatable' => $validated['vatable'] ?? 0,
        'vat_12' => $validated['vat12'] ?? 0,
        'vat_exempt_12' => $validated['vat_exempt_12'] ?? 0,
        'total_charge' => $validated['totalCharge'],
        'charges_description' => $validated['charges_description'] ?? null,
        'status' => 'billout',
        'cashier_id' => auth()->id(),
    ]);

    if (!empty($validated['persons'])) {

        $persons = json_decode($validated['persons'], true);

        foreach ($persons as $person) {

            if (!empty($person['discount_id']) && !empty($person['name'])) {

                DiscountEntry::create([
                    'order_id' => $order->id,
                    'discount_id' => $person['discount_id'],
                    'person_name' => $person['name'],
                    'person_id_number' => $person['id_number'] ?? null
                ]);

            }

        }

    }

    return response()->json([
        'success' => true,
        'order' => $order->fresh(['details','discountEntries','paymentDetails'])
    ]);
}
    
    public function edit($id)
    {
        // ✅ Fetch the order with its relations
        $order = Order::with([
            'details.product',
            'details.component',
            'user'
        ])->findOrFail($id);

        // ✅ Fetch categories (with subcategories → products + components)
        $categories = Category::with([
            'subcategories' => function ($query) {
                $query->with(['products', 'components']);
            }
        ])->get();

        // ✅ Fetch products with relations
        $products = Product::with(['category', 'subcategory'])->get();

        // ✅ Fetch components (for_sale only)
        $components = Component::with(['category', 'subcategory'])
            ->where('for_sale', '!=', 0)
            ->get();

        // ✅ Fetch waiters
        $waiters = User::select('id', 'name')->get();

        // ✅ Transform products
        $productsTransformed = $products->map(function ($p) {
            return [
                'id'             => $p->id,
                'sku'            => $p->code,
                'name'           => $p->name,
                'description'    => $p->description ?? '',
                'price'          => $p->price,
                'category_id'    => $p->category_id,
                'subcategory_id' => $p->subcategory_id,
                'category'       => $p->category->name ?? '',
                'subcategory'    => $p->subcategory->name ?? '',
                'image'          => $p->image
                    ? asset('storage/' . $p->image)
                    : 'https://via.placeholder.com/300x200?text=No+Image',
                'type'           => 'product',
            ];
        });

        // ✅ Transform components
        $componentsTransformed = $components->map(function ($c) {
            return [
                'id'             => $c->id,
                'sku'            => $c->code,
                'name'           => $c->name,
                'description'    => $c->description ?? '',
                'price'          => $c->price,
                'category_id'    => $c->category_id,
                'subcategory_id' => $c->subcategory_id,
                'category'       => $c->category->name ?? '',
                'subcategory'    => $c->subcategory->name ?? '',
                'image'          => $c->image
                    ? asset('storage/' . $c->image)
                    : 'https://via.placeholder.com/300x200?text=No+Image',
                'type'           => 'component',
            ];
        });

        // ✅ Merge both
        $allItems = $productsTransformed->merge($componentsTransformed)->values();

        $orderDetails = $order->details->map(function ($detail) {
    return [
        'order_detail_id' => $detail->id, // 🔥 THIS IS THE IMPORTANT PART
        'product_id'      => $detail->product_id,
        'component_id'    => $detail->component_id,
        'id'              => $detail->product_id ?? $detail->component_id,
        'type'            => $detail->product_id ? 'product' : 'component',
        'sku'             => $detail->product->code ?? $detail->component->code,
        'name'            => $detail->product->name ?? $detail->component->name,
        'qty'             => $detail->quantity,
        'price'           => $detail->price,
        'status'          => $detail->status,
        'notes'           => $detail->notes,
    ];
});

        // ✅ Return same view as create
        return view('orders.form', [
            'isEdit'       => true,
            'order'        => $order,
            'products'     => $allItems,
            'categories'   => $categories,
            'waiters'      => $waiters,
            'orderDetails' => $orderDetails,
        ]);
    }


    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'table_no' => 'required|integer|min:1',
            'number_pax' => 'required|integer|min:1',
            'status' => 'required|in:serving,served,billout,payments,closed,cancelled',
            'order_details' => 'required|array|min:1',
            'order_details.*.detail_id' => 'nullable|exists:order_details,id',
            'order_details.*.product_id'   => 'nullable|exists:products,id',
            'order_details.*.component_id' => 'nullable|exists:components,id',
            'order_details.*.quantity' => 'required|integer|min:1',
            'order_details.*.price' => 'required|numeric|min:0',
            'order_details.*.status'       => 'required|in:serving,served,walked,cancelled',
            'order_details.*.notes' => 'nullable|string|max:255',
        ]);

        $order->update([
            'user_id' => $validated['user_id'],
            'table_no' => $validated['table_no'],
            'number_pax' => $validated['number_pax'],
            'status' => $validated['status'],
        ]);

        $existingIds = $order->details()->pluck('id')->toArray();

$submittedIds = collect($validated['order_details'])
    ->pluck('detail_id')
    ->filter()
    ->toArray();

$idsToDelete = array_diff($existingIds, $submittedIds);

$order->details()
    ->whereIn('id', $idsToDelete)
    ->delete();

foreach ($validated['order_details'] as $detail) {

    if (!empty($detail['detail_id'])) {

        $existingDetail = $order->details()->find($detail['detail_id']);

        if ($existingDetail) {
            $existingDetail->fill([
                'quantity' => $detail['quantity'],
                'price' => $detail['price'],
                'status' => $detail['status'],
                'notes' => $detail['notes'] ?? null,
            ]);

            if ($existingDetail->isDirty()) {
                $existingDetail->save();
            }
        }

    } else {

        $order->details()->create([
            'product_id' => $detail['product_id'] ?? null,
            'component_id' => $detail['component_id'] ?? null,
            'quantity' => $detail['quantity'],
            'price' => $detail['price'],
            'status' => $detail['status'],
            'notes' => $detail['notes'] ?? null,
        ]);
    }
}

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully!',
            'redirect' => route('orders.index')
        ]);
    }
public function show($id)
{
    $order = Order::with([
        'details.product',
        'details.component',
        'user'
    ])->findOrFail($id);

    return response()->json($order);
}

    public function payment(Request $request, $orderId)
    {
        $order = Order::with('paymentDetails')->findOrFail($orderId);

        $validated = $request->validate([
            'payments' => 'required|string', // JSON array
            'total_payment_rendered' => 'nullable|numeric|min:0',
            'change_amount' => 'nullable|numeric|min:0',
        ]);

        $decodedPayments = json_decode($validated['payments'], true);

        if (empty($decodedPayments) || !is_array($decodedPayments)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or empty payment data.',
            ], 422);
        }

        $totalPaid = 0;
        $createdPaymentDetailIds = [];

        // Create payment detail rows with zeroed totals/change for now.
        foreach ($decodedPayments as $p) {
            if (empty($p['payment_method_id']) || empty($p['cash_equivalent_id']) || empty($p['amount_paid'])) {
                continue;
            }

            $pd = PaymentDetail::create([
                'order_id' => $order->id,
                'payment_id' => $p['payment_method_id'],
                'cash_equivalent_id' => $p['cash_equivalent_id'],
                'transaction_reference_no' => $p['reference_no'] ?? null,
                'amount_paid' => $p['amount_paid'],
                'total_rendered' => 0,
                'change_amount' => 0,
            ]);

            $createdPaymentDetailIds[] = $pd->id;
            $totalPaid += floatval($p['amount_paid']);
        }

        // compute total rendered and change
        $totalCharge = floatval($order->total_charge ?? 0);
        $changeAmount = max(0, $totalPaid - $totalCharge);

        // update order
        $order->update([
            'status' => 'payments',
            'paid_datetime' => now(),
            'total_payment_rendered' => $totalPaid,
            'change_amount' => $changeAmount,
            'charges_description' => ($order->charges_description ?? '') . "\nPayments added on " . now()->toDateTimeString(),
            'cashier_id' => auth()->user()->id,
        ]);

        PaymentDetail::where('order_id', $order->id)->update([
            'total_rendered' => $totalPaid,
            'change_amount' => 0,
        ]);

        $cashPayment = Payment::whereRaw('LOWER(name) = ?', [strtolower('cash')])->first();
        if ($cashPayment) {
            $cashPaymentDetail = PaymentDetail::where('order_id', $order->id)
                ->where('payment_id', $cashPayment->id)
                ->latest()
                ->first();

            if ($cashPaymentDetail) {
                $cashPaymentDetail->update([
                    'total_rendered' => $totalPaid,
                    'change_amount' => $changeAmount,
                ]);
            }
        }

        $order = Order::with([
            'details.product',
            'details.component',
            'user',
            'discountEntries',
            'paymentDetails.payment',
            'paymentDetails.cashEquivalent'
        ])->find($order->id);

        return response()->json([
            'success' => true,
            'message' => 'Payment successfully recorded!',
            'order' => $order,
            'total_paid' => $totalPaid,
            'change' => $changeAmount,
        ]);

    }

    public function getAllStatusPayments(Request $request)
    {
        $cashierId = auth()->id();
        $branchId   = current_branch_id();
        $terminalNo = $request->query('terminal_no');

        $session = CashAudit::where('cashier_id', $cashierId)
            ->where('branch_id', $branchId)
            // ->where('terminal_no', $terminalNo)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return response()->json([
                'order' => ['totals_by_payment' => []],
                'message' => 'No active session',
            ]);
        }

        // Fix: Convert session start time from UTC → Asia/Manila
        $startUtc = $session->transaction_datetime ?? $session->created_at;
        $start    = Carbon::parse($startUtc)->setTimezone('Asia/Manila');
        $end      = Carbon::now('Asia/Manila');

        // Load both relationships so we can get the name correctly
        $paymentDetails = PaymentDetail::with(['cashEquivalent', 'payment'])
            ->whereHas('order', function ($q) use ($cashierId, $branchId) {
                $q->where('cashier_id', $cashierId)
                  ->where('branch_id', $branchId);
            })
            ->whereBetween('created_at', [$start, $end])
            ->get();

        // Group by payment method name
        $totals = $paymentDetails->groupBy(function ($pd) {
            // 1. Try cash equivalent (GCash, Maya, Card, etc.)
            // 2. Fallback to payment type (Cash, Cash on Hand, etc.)
            $name = $pd->payment?->name 
                ?? $pd->cashEquivalent?->name 
                ?? 'Unknown';

            return strtolower(trim($name));
        })->map(function ($group, $key) {
            $total = $group->sum(function ($pd) use ($key) {
                $amountPaid   = (float) ($pd->amount_paid ?? 0);
                $changeAmount = (float) ($pd->change_amount ?? 0);

                // Only subtract change for cash-based payments
                $isCash = str_contains($key, 'cash');

                return $isCash 
                    ? ($amountPaid - $changeAmount)
                    : $amountPaid;
            });

            $displayName = ucwords(str_replace(['_', '-'], ' ', $key));

            return [
                'payment_name' => $displayName,   // e.g. "Cash On Hand", "Gcash"
                'total_amount' => round($total, 2),
            ];
        })->values();

        return response()->json([
            'order' => [
                'totals_by_payment' => $totals->toArray(),
            ],
            'session_info' => [
                'start_manila' => $start->format('Y-m-d H:i:s'),
                'total_records' => $paymentDetails->count(),
            ]
        ]);
    }


//    public function checkUnpaidOrders(Request $request)
// {
//     // Get the currently logged-in cashier
//     $cashierId = Auth::id();

//     // Find the active (open) cash audit session for this cashier
//     $session = CashAudit::where('cashier_id', $cashierId)
//         ->where('status', 'open')
//         ->first();

//     if (!$session) {
//         return response()->json([
//             'has_unpaid_orders' => false,
//             'message' => 'No active POS session found.'
//         ]);
//     }

//     // Define the session timeframe
//     $sessionStart = Carbon::parse($session->created_at);
//     $sessionEnd = $session->closed_at
//         ? Carbon::parse($session->closed_at)
//         : Carbon::now();

//     // ✅ Check unpaid orders ONLY for the current branch
//     $hasUnpaidOrders = Order::where('branch_id', current_branch_id())
//         ->whereBetween('created_at', [$sessionStart, $sessionEnd])
//         ->where('status', '!=', 'payments')
//         ->exists();

//     return response()->json([
//         'has_unpaid_orders' => $hasUnpaidOrders,
//         'session_start' => $sessionStart->toDateTimeString(),
//         'session_end' => $sessionEnd->toDateTimeString(),
//     ]);
// }

public function checkUnpaidOrders(Request $request)
{
    $branchId = current_branch_id();

   // ✅ Check unpaid orders
$hasUnpaidOrders = Order::where('branch_id', $branchId)
    ->where('status', '!=', 'payments')
    ->exists();

// ✅ Check unserved products
$hasUnservedProducts = OrderDetail::whereHas('order', function ($q) use ($branchId) {
        $q->where('branch_id', $branchId);
    })
    ->where('status', 'serving') // FIXED
    ->exists();


    return response()->json([
        'has_unpaid_orders'     => $hasUnpaidOrders,
        'has_unserved_products' => $hasUnservedProducts,
    ]);
}

public function showNote($orderDetailId)
{
    $detail = OrderDetail::findOrFail($orderDetailId);
    return response()->json(['notes' => $detail->notes]);
}

public function saveNote(Request $request, $orderDetailId)
{
    $detail = OrderDetail::findOrFail($orderDetailId);
    $validated = $request->validate(['notes' => 'nullable|string|max:255']);
    $detail->update(['notes' => $validated['notes']]);
    return response()->json(['success' => true]);
}

}
