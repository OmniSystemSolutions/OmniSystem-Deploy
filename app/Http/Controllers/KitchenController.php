<?php

namespace App\Http\Controllers;

use App\Models\BranchComponent;
use App\Models\BranchProduct;
use App\Models\Component;
use App\Models\InventoryDeduction;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderItem;
use App\Models\Station;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KitchenController extends Controller
{
  public function index()
{
    return view('kitchen.index');
}
public function fetchItems(Request $request)
{
    $status = $request->get('status', 'serving');
    $year   = $request->get('year');
    $month  = $request->get('month');
    $day    = $request->get('day');
    $stationId = $request->station_id;
    $currentBranchId = current_branch_id();

    $orders = Order::where('branch_id', $currentBranchId)
    ->with([
    'details' => function ($q) use ($status, $stationId) {
        $q->where('status', $status)
          ->when($stationId, function ($q) use ($stationId) {
              $q->whereHas('product.station', function ($q) use ($stationId) {
                  $q->where('id', $stationId);
              });
          })
          ->with([
              'product.category',
              'product.subcategory',
              'product.recipes.component.unit',
              'product.station',
              'component.category',
              'orderItems.cook',
              'component.station',
          ]);
    },
    'user'
])
->when($stationId, function ($q) use ($stationId, $status) {
    $q->whereHas('details', function ($q) use ($stationId, $status) {
        $q->where('status', $status)
          ->whereHas('product.station', function ($q) use ($stationId) {
              $q->where('id', $stationId);
          });
    });
})
->when($year, fn ($q) => $q->whereYear('created_at', $year))
->when($month, fn ($q) => $q->whereMonth('created_at', $month))
->when($day, fn ($q) => $q->whereDay('created_at', $day))
->orderBy('created_at', 'asc')
->get();


    // Flatten orders → orderItems
    $orderItems = $orders->flatMap(function ($order) {
        return $order->details->flatMap(function ($detail) use ($order) {
            $item = $detail->product ?? $detail->component;

            // Build recipe
            $recipe = collect([]);
            if ($detail->product && $detail->product->recipes) {
                $recipe = $detail->product->recipes->map(fn($r) => [
                    'component_id'   => optional($r->component)->id,
                    'component_name' => optional($r->component)->name ?? 'Unknown Component',
                    'quantity'       => $r->quantity * $detail->quantity,
                    'base_quantity'  => $r->quantity,
                    'unit'           => optional($r->component->unit)->name ?? 'pcs',
                    'loss_type'      => '',
                    'loss_qty'       => 0,
                    'source'         => 'recipe',
                ])->values();
            } elseif ($detail->component) {
                $recipe = collect([[
                    'component_id'   => $detail->component->id,
                    'component_name' => $detail->component->name,
                    'quantity'       => $detail->quantity,
                    'base_quantity'  => 1,
                    'unit'           => optional($detail->component->unit)->name ?? 'pcs',
                    'loss_type'      => '',
                    'loss_qty'       => 0,
                    'source'         => 'component',
                ]]);
            }

            // Get cook info from order_items
            $orderItem = $detail->orderItems->first(); // assuming one-to-one mapping per detail
            $cook = $orderItem?->cook;
            $cookId = $cook?->id;
            $cookName = $cook?->name ?? null;

            return [[
                'order_detail_id' => $detail->id,
                'order_id'        => $order->id,
                'order_no'        => $order->order_no ?? ('ORD-' . $order->id),
                'created_at'      => $detail->created_at->format('Y-m-d H:i:s'),
               'time_submitted' => in_array($detail->status, ['served', 'walked','cancelled'])
                                    ? optional($detail->updated_at)->format('Y-m-d H:i:s')
                                    : (optional($order->time_submitted)
                                        ? \Carbon\Carbon::parse($order->time_submitted)->format('Y-m-d H:i:s')
                                        : null),
                'code'            => $item->code ?? 'N/A',
                'name'            => $item->name ?? 'Unnamed Item',
                'qty'             => $detail->quantity,
                'category'         => $item->category->name ?? 'N/A',
                'subcategory'      => $item->subcategory->name ?? 'N/A',
                'station'          => $item->station->name ?? 'N/A',
                'status'          => $detail->status,
                'recipe'          => $recipe,
                'cook_id'         => $cookId,
                'cook_name'       => $cookName,
            ]];
        });
    })->sortBy('created_at')->values();

    // Fetch all chefs
    $chefs = User::role(['chef', 'cook'])
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    $availableOrders = Order::where('branch_id', $currentBranchId)
    ->where('status', '!=', 'payments')
    ->orderBy('created_at', 'asc')
    ->get()
    ->map(function ($order) {
        return [
            'id' => $order->id,
            'order_no' => $order->order_no ?? 'ORD-' . $order->id,
        ];
    });

   $stations = Station::whereHas('products.details', function ($q) use ($currentBranchId, $year, $month, $day) {
    $q->where('status', 'serving') // 👈 filter by ORDER DETAIL status
      ->whereHas('order', function ($orderQuery) use ($currentBranchId, $year, $month, $day) {
          $orderQuery->where('branch_id', $currentBranchId)
              ->when($year, fn($q) => $q->whereYear('created_at', $year))
              ->when($month, fn($q) => $q->whereMonth('created_at', $month))
              ->when($day, fn($q) => $q->whereDay('created_at', $day));
      });
})
->select('id', 'name')
->orderBy('name')
->get();

    $branchProducts = BranchProduct::where('branch_id', $currentBranchId)
        ->where('status', 'active')
        ->with(['product:id,name,code,station_id', 'product.station:id,name', 'unit:id,name'])
        ->get()
        ->map(fn($bp) => [
            'id'         => $bp->id,
            'product_id' => $bp->product_id,
            'name'       => optional($bp->product)->name,
            'code'       => optional($bp->product)->code,
            'station'    => optional(optional($bp->product)->station)->name,
            'unit'       => optional($bp->unit)->name,
            'price'      => $bp->price,
            'quantity'   => $bp->quantity,
            'type'       => $bp->type,
        ]);

    $branchComponents = BranchComponent::where('branch_id', $currentBranchId)
        ->where('status', 'active')
        ->where('for_sale', true)
        ->with(['component:id,name,code,unit_id', 'component.unit:id,name'])
        ->get()
        ->map(fn($bc) => [
            'id'           => $bc->id,
            'component_id' => $bc->component_id,
            'name'         => optional($bc->component)->name,
            'code'         => optional($bc->component)->code,
            'unit'         => optional(optional($bc->component)->unit)->name,
            'price'        => $bc->price,
            'onhand'       => $bc->onhand,
        ]);

    return response()->json([
        'orderItems'       => $orderItems,
        'chefs'            => $chefs,
        'availableOrders'  => $availableOrders,
        'stations'         => $stations,
        'branchProducts'   => $branchProducts,
        'branchComponents' => $branchComponents,
    ]);
}




   public function updateOrCreate(Request $request)
{
    $validator = Validator::make($request->all(), [
        'order_detail_id' => 'required|integer|exists:order_details,id',
        'cook_id'         => 'required|integer|exists:users,id',
        'time_submitted'  => 'nullable|date',
        'status'          => 'required|string|in:serving,served,walked,cancelled',
        'recipe'          => 'nullable|array',
        'deductions'      => 'nullable|array', // ✅ new unified data source
        'deductions' => 'nullable|array',
        'deductions.*.component_id' => 'required|integer|exists:components,id',
        'deductions.*.quantity_deducted' => 'required|numeric|min:0',
        'deductions.*.deduction_type' => 'nullable|string|in:served,wastage,spoilage,theft',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors(),
        ], 422);
    }

    try {
        DB::beginTransaction();

        $orderDetail = OrderDetail::findOrFail($request->order_detail_id);

        // ✅ Create or update order item
        $item = OrderItem::updateOrCreate(
            ['order_detail_id' => $request->order_detail_id],
            [
                'cook_id'        => $request->cook_id,
                'time_submitted' => $request->time_submitted ?? now(),
            ]
        );

        // ✅ Update order detail status
        $orderDetail->update(['status' => $request->status]);

        // ✅ Perform stock deductions (only if order is served or walked)
        if (in_array($request->status, ['served', 'walked']) && !empty($request->deductions)) {
            $this->updateStockBulk($request->deductions, $orderDetail->id);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Order item, status, and inventory updated successfully.',
            'data' => [
                'order_item'   => $item,
                'order_detail' => $orderDetail,
            ],
        ], 200);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('OrderItem updateOrCreate failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage(),
        ], 500);
    }
}


    protected function updateStockBulk(array $deductions, int $orderDetailId)
{
    foreach ($deductions as $d) {
        // ✅ Skip invalid entries
        if (empty($d['component_id']) || empty($d['quantity_deducted'])) {
            continue;
        }

        $component = Component::find($d['component_id']);
        if (!$component) continue;

        $prevQty = $component->onhand;
        $deductedQty = floatval($d['quantity_deducted']);
        $newQty = $prevQty - $deductedQty;

        if ($newQty < 0) {
            throw new \Exception("Insufficient stock for {$component->name}");
        }

        $component->update(['onhand' => $newQty]);

        // ✅ Log to inventory deductions
        InventoryDeduction::create([
            'component_id'      => $component->id,
            'order_detail_id'   => $orderDetailId,
            'quantity_deducted' => $deductedQty,
            'prev_quantity'     => $prevQty,
            'new_quantity'      => $newQty,
            'deduction_type'    => $d['deduction_type'] ?? 'served',
            'notes'             => $d['notes'] ?? null,
            'user_id'           => Auth::id(),
        ]);

        Log::info('📦 Deduction Applied', [
            'component'         => $component->name,
            'deductedQty'       => $deductedQty,
            'type'              => $d['deduction_type'] ?? 'served',
            'prevQty'           => $prevQty,
            'newQty'            => $newQty,
            'order_detail_id'   => $orderDetailId,
        ]);
    }

    return true;
}

public function pushItem(Request $request)
{
    $detail = OrderDetail::findOrFail($request->order_detail_id);

    $detail->order_id = $request->new_order_id;

    // dd($detail);
    $detail->status = 'served';
    $detail->update();

    return response()->json([
        'success' => true
    ]);
}


}
