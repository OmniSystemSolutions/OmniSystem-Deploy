<?php

use App\Http\Controllers\AccountingCategoryController;
use App\Http\Controllers\AccountPayableController;
use App\Http\Controllers\InventoryAuditController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\SystemSettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\CashEquivalentController;
use App\Http\Controllers\ComponentRemarkController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\InventoryPurchaseOrderController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SalesJournalController;
use App\Http\Controllers\PosSessionController;
use App\Http\Controllers\RemarkController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AccountReceivableController;
use App\Http\Controllers\AllowancesController;
use App\Http\Controllers\DesignationController;
use App\Models\CashEquivalent;
use App\Models\User;
use App\Http\Controllers\FundTransferController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\InventoryTransferController;
use App\Http\Controllers\LeaveRequestController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use App\Http\Controllers\TaxController;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\PosClossingController;
use App\Http\Controllers\LeavesController;
use App\Http\Controllers\ShiftsController;
use App\Http\Controllers\DailyTimeRecordController;
use App\Http\Controllers\TableLayoutController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\BundledItemController;
use App\Http\Controllers\ChartofAccountController;
use App\Http\Controllers\GeneralLedgerController;
use App\Http\Controllers\KitchenmrpController;
use App\Http\Controllers\OrderAndReservationController;

Route::get('/', function () {
    return redirect()->route('login');
})->middleware('redirect.auth');

Route::post('/clear-cache', function () {
    // Clear everything Laravel uses
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('event:clear');
    // Optional extras
    // Artisan::call('optimize:clear');

    return response()->json(['message' => 'Application cache cleared successfully!']);
})->name('clear-cache');

Route::get('/pos', function () {
    return view('point-of-sale');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');


// No new route needed!
View::composer('layouts.sidebar', function ($view) {
    $managers = User::whereHas('roles', fn($q) => $q->where('name', 'Manager'))
                    ->get(['id', 'name']);
    $view->with('managers', $managers);
});

View::composer('layouts.sidebar', function ($view) {
    $cashEquivalent = CashEquivalent::select('id', 'account_number')
        ->where('name', 'Cash On Hand')
        ->get();

    $view->with('cashEquivalentNames', $cashEquivalent);
});

Route::get('/remarks', [RemarkController::class, 'index'])->name('remarks.index');
Route::get('/remarks/create', [RemarkController::class, 'create'])->name('remarks.create');
Route::post('/remarks/store', [RemarkController::class, 'store'])->name('remarks.store');
Route::put('/remarks/{id}/mark-read', [RemarkController::class, 'markRead']);
Route::put('/remarks/{id}/mark-unread', [RemarkController::class, 'markUnread']);


Route::get('/component-remarks', [ComponentRemarkController::class, 'index'])->name('component-remarks.index');
Route::get('/component-remarks/create', [ComponentRemarkController::class, 'create'])->name('component-remarks.create'); 
Route::post('/component-remarks/store', [ComponentRemarkController::class, 'store'])->name('component-remarks.store');
Route::put('/component-remarks/{id}/mark-read', [ComponentRemarkController::class, 'markRead']);
Route::put('/component-remarks/{id}/mark-unread', [ComponentRemarkController::class, 'markUnread']);

// Route::get('/components/{component}/remarks', [ComponentRemarkController::class, 'index']);
// Route::post('/components/{component}/remarks', [ComponentRemarkController::class, 'store']);
// Route::post('/components/{component}/remarks/mark-read', [ComponentRemarkController::class, 'markRead']);

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/order/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');
Route::post('/orders/{order}/billout', [OrderController::class, 'billout'])->name('orders.billout');
Route::get('/orders/{id}/show', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{id}/edit', [OrderController::class, 'edit'])->name('orders.edit');
Route::post('/orders/update/{id}', [OrderController::class, 'update'])->name('orders.update');
Route::post('/orders/{order}/payment', [OrderController::class, 'payment'])->name('orders.payment');
Route::get('/reports/sales-journal', [SalesJournalController::class, 'index'])->name('reports.sales-journal');
Route::get('/reports/sales-journal/fetch-xreport', [SalesJournalController::class, 'xReport'])->name('reports.sales-journal.fetch-xreport');
Route::get('/reports/sales-journal/fetch-zreport', [SalesJournalController::class, 'zReport'])->name('reports.sales-journal.fetch-zreport');
Route::prefix('order-details')->group(function () {
    Route::get('{id}/note', [OrderController::class, 'showNote']);
    Route::post('{id}/note', [OrderController::class, 'saveNote']);
    Route::post('{id}/update-note', [OrderController::class, 'updateNote']);
});




Route::prefix('kitchen')->name('kitchen.')->group(function () {
    Route::get('/', [KitchenController::class, 'index'])->name('index');
    Route::get('/fetch', [KitchenController::class, 'fetchItems'])->name('fetch');
    // Route::post('/', [KitchenController::class, 'store'])->name('store');
    // Route::put('/{id}/update-status', [KitchenController::class, 'updateStatus']);
    // Route::get('/{id}/edit', [KitchenController::class, 'edit'])->name('leave-requests.edit');
    // Route::put('/{id}', [KitchenController::class, 'update'])->name('leave-requests.update');
});


Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
Route::get('/kitchen/fetch-items', [KitchenController::class, 'fetchItems']);
Route::get('/kitchen/served', [KitchenController::class, 'showServed'])->name('kitchen.served');
Route::get('/kitchen/walked', [KitchenController::class, 'showWalked'])->name('kitchen.walked');
Route::post('/order-items/update-or-create', [KitchenController::class, 'updateOrCreate']);
Route::post('/kitchen/push-item', [KitchenController::class, 'pushItem']);
// Route::post('/inventory/update-stock-bulk', [KitchenController::class, 'updateStockBulk']);



Route::post('/orders/{order}/payment', [OrderController::class, 'payment'])->name('orders.payment');
Route::get('/get-all-payments', [OrderController::class, 'getAllStatusPayments'])->name('orders.getAllPayments');
Route::get('/check-unpaid-orders', [OrderController::class, 'checkUnpaidOrders'])->name('orders.checkUnpaidOrders');


Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    // ←←← PUT YOUR SWITCH-BRANCH ROUTE HERE
    Route::post('/switch-branch', function (Request $request) {
        $branchId = $request->input('branch_id');

        if (!$branchId || !is_numeric($branchId)) {
            return response()->json(['error' => 'Invalid branch'], 400);
        }

        $user = auth()->user();

        // This NOW works because auth() is guaranteed
        $hasAccess = $user->branches()->where('branch_id', $branchId)->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        session(['branch_id' => $branchId]);

        return response()->json(['success' => true, 'branch_id' => $branchId]);
    })->name('switch-branch');

});

// routes/web.php
Route::prefix('pos/session')->group(function () {
    Route::get('/check', [PosSessionController::class, 'checkSession']);
    Route::post('/open', [PosSessionController::class, 'openSession']);
    Route::post('/close', [PosSessionController::class, 'closeSession']);
});

Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{id}/profile', [UserController::class, 'viewProfile'])->name('users.profile');
Route::put('/users/{user}/archive', [UserController::class, 'archive'])->name('users.archive');
Route::put('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
// Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
// Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
Route::put('/users/{user}/status/{status}', [UserController::class, 'updateStatus'])->name('users.updateStatus');
Route::get('/users/{user}/leave-history/{leaveId}', [UserController::class, 'leaveHistory'])->name('users.leave-history');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/fetch', [ProductController::class, 'fetchProducts'])
    ->name('products.fetch');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{id}/update', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
Route::put('/products/{product}/archive', [ProductController::class, 'archive'])->name('products.archive');
Route::put('/products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');
Route::get('/products/{id}/product-stock-card', [ProductController::class, 'stockCard'])->name('products.stock-card');
Route::post('/products/verify-import', [ProductController::class, 'verify'])->name('products.verify-import');
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');



Route::get('/components', [ComponentController::class, 'index'])->name('components.index');
Route::get('/components/fetch', [ComponentController::class, 'fetchComponents'])
    ->name('components.fetch');
Route::get('/components/create', [ComponentController::class, 'create'])->name('components.create');
Route::post('/components', [ComponentController::class, 'store'])->name('components.store');
Route::get('/components/{id}/edit', [ComponentController::class, 'edit'])->name('components.edit');
Route::put('/components/{component}', [ComponentController::class, 'update'])->name('components.update');
Route::delete('/components/{id}', [ComponentController::class, 'destroy'])->name('components.destroy');
Route::put('/components/{component}/archive', [ComponentController::class, 'archive'])->name('components.archive');
Route::put('/components/{component}/restore', [ComponentController::class, 'restore'])->name('components.restore');
Route::get('/components/{id}/stock-card', [ComponentController::class, 'stockCard'])->name('components.stock-card');
Route::post('/components/verify-import', [ComponentController::class, 'verifyImport']);
Route::post('/components/import', [ComponentController::class, 'import']);

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
Route::put('/categories/{category}/archive', [CategoryController::class, 'archive'])->name('categories.archive');
Route::put('/categories/{category}/restore', [CategoryController::class, 'restore'])->name('categories.restore');

Route::get('/subcategories', [SubcategoryController::class, 'index'])->name('subcategories.index');
Route::post('/subcategories', [SubcategoryController::class, 'store'])->name('subcategories.store');
Route::get('/categories/{id}/subcategories', [SubcategoryController::class, 'byCategory']);

Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
Route::get('/settings/create', [SystemSettingController::class, 'create'])->name('settings.create');
Route::post('/settings', [SystemSettingController::class, 'store'])->name('settings.store');

Route::get('/permission', [PermissionController::class, 'index'])->name('permissions.index');
Route::get('/permission/create', [PermissionController::class, 'create'])->name('permissions.create');
Route::post('/permission', [PermissionController::class, 'store'])->name('permissions.store');
Route::get('/permission/{role}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
Route::put('/permission/{role}', [PermissionController::class, 'update'])->name('permissions.update');
Route::delete('/permissions/{role}', [PermissionController::class, 'destroy'])->name('permissions.delete');

Route::get('/branches', [BranchesController::class, 'index'])->name('branches.index');
Route::post('/branches', [BranchesController::class, 'store'])->name('branches.store');
Route::get('/branches/{id}/edit', [BranchesController::class, 'edit'])->name('branches.edit');
Route::put('/branches/{id}', [BranchesController::class, 'update'])->name('branches.update');
Route::delete('/branches/{id}', [BranchesController::class, 'destroy'])->name('branches.destroy');
Route::put('/branches/{branch}/archive', [BranchesController::class, 'archive'])->name('branches.archive');
Route::put('/branches/{branch}/restore', [BranchesController::class, 'restore'])->name('branches.restore');

Route::get('/units', [UnitController::class, 'index'])->name('units.index');
Route::post('/units', [UnitController::class, 'store'])->name('units.store');
Route::get('/units/{id}/edit', [UnitController::class, 'edit'])->name('units.edit');
Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
Route::delete('/units/{id}', [UnitController::class, 'destroy'])->name('units.destroy');
Route::put('/units/{unit}/archive', [UnitController::class, 'archive'])->name('units.archive');
Route::put('/units/{unit}/restore', [UnitController::class, 'restore'])->name('units.restore');

Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
Route::get('/payments/{id}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');
Route::put('/payments/{payment}/archive', [PaymentController::class, 'archive'])->name('payments.archive');
Route::put('/payments/{payment}/restore', [PaymentController::class, 'restore'])->name('payments.restore');

Route::get('/cash-equivalents', [CashEquivalentController::class, 'index'])->name('cash_equivalents.index');
Route::post('/cash-equivalents', [CashEquivalentController::class, 'store'])->name('cash_equivalents.store');
Route::get('/cash-equivalents/{id}/edit', [CashEquivalentController::class, 'edit'])->name('cash_equivalents.edit');
Route::put('/cash-equivalents/{cash_equivalent}', [CashEquivalentController::class, 'update'])->name('cash_equivalents.update');
Route::delete('/cash-equivalents/{id}', [CashEquivalentController::class, 'destroy'])->name('cash_equivalents.destroy');
Route::put('/cash-equivalents/{cash_equivalent}/archive', [CashEquivalentController::class, 'archive'])->name('cash_equivalents.archive');
Route::put('/cash-equivalents/{cash_equivalent}/restore', [CashEquivalentController::class, 'restore'])->name('cash_equivalents.restore');

Route::get('/discounts', [DiscountController::class, 'index'])->name('discounts.index');
Route::post('/discounts', [DiscountController::class, 'store'])->name('discounts.store');
Route::get('/discounts/{id}/edit', [DiscountController::class, 'edit'])->name('discounts.edit');
Route::put('/discounts/{discount}', [DiscountController::class, 'update'])->name('discounts.update');
Route::delete('/discounts/{id}', [DiscountController::class, 'destroy'])->name('discounts.destroy');
Route::put('/discounts/{discount}/archive', [DiscountController::class, 'archive'])->name('discounts.archive');
Route::put('/discounts/{discount}/restore', [DiscountController::class, 'restore'])->name('discounts.restore');

Route::get('/components/{component}/remarks', [ComponentController::class, 'remarks']);
Route::post('/components/{component}/remarks', [ComponentController::class, 'storeRemark']);

Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
Route::put('/suppliers/{supplier}/archive', [SupplierController::class, 'archive'])->name('suppliers.archive');
Route::put('/suppliers/{supplier}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');

// CUSTOMER ROUTES
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

// Optional if you plan to have soft delete / archive feature
Route::put('/customers/{customer}/archive', [CustomerController::class, 'archive'])->name('customers.archive');
Route::put('/customers/{customer}/restore', [CustomerController::class, 'restore'])->name('customers.restore');

Route::get('/inventory-purchase-orders', [InventoryPurchaseOrderController::class, 'index'])->name('inventory_purchase_orders.index');
Route::get('/inventory-purchase-orders/create', [InventoryPurchaseOrderController::class, 'create'])->name('inventory_purchase_orders.create');
Route::post('/inventory-purchase-orders', [InventoryPurchaseOrderController::class, 'store'])->name('inventory_purchase_orders.store');
Route::get('/inventory-purchase-orders/{purchaseOrder}/edit', [InventoryPurchaseOrderController::class, 'edit'])->name('inventory_purchase_orders.edit');
Route::put('/inventory-purchase-orders/{purchaseOrder}', [InventoryPurchaseOrderController::class, 'update'])->name('inventory_purchase_orders.update');
Route::delete('/inventory-purchase-orders/{id}', [InventoryPurchaseOrderController::class, 'destroy'])->name('inventory_purchase_orders.destroy');
Route::get('/inventory/purchase-orders/{id}/details', [InventoryPurchaseOrderController::class, 'getDetails']);
// Generate next Delivery Receipt (DR) number for a branch
Route::get('/inventory/purchase-orders/generate-next-dr', [InventoryPurchaseOrderController::class, 'generateNextDrNumber']);
Route::post('/inventory/purchase-orders/attachments', [InventoryPurchaseOrderController::class, 'uploadAttachments'])
    ->name('inventory.purchase_orders.attachments');
Route::get('/inventory_purchase_orders/{id}/attachments', [InventoryPurchaseOrderController::class, 'getAttachments']);
Route::put('/inventory/purchase-orders/{id}/approve', [InventoryPurchaseOrderController::class, 'approve'])
    ->name('inventory_purchase_orders.approve');
Route::put('/inventory/purchase-orders/{id}/disapprove', [InventoryPurchaseOrderController::class, 'disapprove'])
    ->name('inventory_purchase_orders.disapprove');
Route::get('/inventory/purchase-orders/{id}/invoice', [InventoryPurchaseOrderController::class, 'getInvoiceData']);
Route::put('/inventory/purchase-orders/{id}/archive', [InventoryPurchaseOrderController::class, 'archive'])
    ->name('inventory_purchase_orders.archive');
// Route::get('/inventory/purchase-orders/{id}/log-stocks', [InventoryPurchaseOrderController::class, 'logStocks']);
Route::post('/inventory/purchase-orders/{id}/log-stocks', [InventoryPurchaseOrderController::class, 'storeLogStocks']);

Route::get('/inventory/audits', [InventoryAuditController::class, 'index'])->name('inventory_audit.index');
Route::get('/inventory/audits/fetch', [InventoryAuditController::class, 'fetchAudits'])->name('inventory_audit.fetch');
Route::get('/inventory/audits/create', [InventoryAuditController::class, 'create'])->name('inventory_audit.create');
Route::post('/inventory/audits/{id}/apply', [\App\Http\Controllers\InventoryAuditController::class, 'apply'])->name('inventory_audit.apply');
Route::get('/inventory/audits/{id}/show', [InventoryAuditController::class, 'show'])->name('inventory_audit.show');
Route::get('/inventory/audits/{id}/pdf', [InventoryAuditController::class, 'downloadPdf'])->name('audits.pdf');
Route::get('/inventory/items/fetch', [InventoryAuditController::class, 'fetchItems']);
Route::post('/inventory/audits/store', [InventoryAuditController::class, 'store'])->name('inventory.audits.store');
Route::delete('/inventory/audits/{id}/destroy', [InventoryAuditController::class, 'destroy'])->name('inventory_audits.destroy');

// Archive an audit
Route::put('/inventory/audits/{id}/archive', [InventoryAuditController::class, 'archive'])->name('inventory_audits.archive');
Route::put('/inventory/audits/{id}/restore', [InventoryAuditController::class, 'restore'])->name('inventory_audits.restore');

// 🔹 New routes for edit/update
Route::get('/inventory/audits/{id}/edit', [InventoryAuditController::class, 'edit'])->name('inventory_audits.edit');
Route::post('/inventory/audits/{id}/update', [InventoryAuditController::class, 'update'])->name('inventory_audits.update');


// Workforce
Route::prefix('departments')->name('departments.')->group(function () {

    Route::get('/', [DepartmentController::class, 'index'])->name('index');
    Route::post('/', [DepartmentController::class, 'store'])->name('store');

    Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('edit');
    Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');

    Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');

    Route::put('/{department}/archive', [DepartmentController::class, 'archive'])->name('archive');
    Route::put('/{department}/restore', [DepartmentController::class, 'restore'])->name('restore');

});

Route::prefix('designations')->name('designations.')->group(function () {

    Route::get('/', [DesignationController::class, 'index'])->name('index');
    Route::post('/', [DesignationController::class, 'store'])->name('store');

    Route::get('/{designation}/edit', [DesignationController::class, 'edit'])->name('edit');
    Route::put('/{designation}', [DesignationController::class, 'update'])->name('update');

    Route::delete('/{designation}', [DesignationController::class, 'destroy'])->name('destroy');

    Route::put('/{designation}/archive', [DesignationController::class, 'archive'])->name('archive');
    Route::put('/{designation}/restore', [DesignationController::class, 'restore'])->name('restore');

});

Route::prefix('statuses')->name('statuses.')->group(function () {
    Route::get('/', [StatusController::class, 'index'])->name('index');
    Route::post('/', [StatusController::class, 'store'])->name('store');

    Route::get('/{status}/edit', [StatusController::class, 'edit'])->name('edit');
    Route::put('/{status}', [StatusController::class, 'update'])->name('update');

    Route::delete('/{status}', [StatusController::class, 'destroy'])->name('destroy');
    Route::put('/{status}/archive', [StatusController::class, 'archive'])->name('archive');
    Route::put('/{status}/restore', [StatusController::class, 'restore'])->name('restore');
});

Route::prefix('asset-categories')->name('asset-categories.')->group(function () {

    Route::get('/', [AssetCategoryController::class, 'index'])->name('index');
    Route::post('/', [AssetCategoryController::class, 'store'])->name('store');

    Route::get('/{asset_category}/edit', [AssetCategoryController::class, 'edit'])->name('edit');
    Route::put('/{asset_category}', [AssetCategoryController::class, 'update'])->name('update');

    Route::delete('/{asset_category}', [AssetCategoryController::class, 'destroy'])->name('destroy');

    Route::put('/{asset_category}/archive', [AssetCategoryController::class, 'archive'])->name('archive');
    Route::put('/{asset_category}/restore', [AssetCategoryController::class, 'restore'])->name('restore');

});

// Fund Transfers routes (follows same pattern as asset-categories)
Route::prefix('fund-transfers')->name('fund-transfers.')->group(function () {

    Route::get('/', [FundTransferController::class, 'index'])->name('index');
    Route::post('/', [FundTransferController::class, 'store'])->name('store');

    Route::get('/create', [FundTransferController::class, 'create'])->name('create');
    Route::put('/{fund_transfer}', [FundTransferController::class, 'update'])->name('update');

    Route::delete('/{fund_transfer}', [FundTransferController::class, 'destroy'])->name('destroy');

    Route::put('/{fund_transfer}/approve', [FundTransferController::class, 'approve'])->name('approve');
    Route::put('/{fund_transfer}/archive', [FundTransferController::class, 'archive'])->name('archive');
    Route::put('/{fund_transfer}/restore', [FundTransferController::class, 'restore'])->name('restore');

    Route::get('/{fund_transfer}', [FundTransferController::class, 'show'])->name('show');

    // ✅ FIXED — attachment routes
    Route::post('/{id}/attachments/upload', 
        [FundTransferController::class, 'uploadAttachments']
    )->name('attachments.upload');

    Route::get('/{id}/attachments', 
        [FundTransferController::class, 'getAttachments']
    )->name('attachments.get');
});

Route::prefix('accounting-categories')->name('accounting-categories.')->group(function () {
    Route::get('/',    [AccountingCategoryController::class, 'index'])->name('index');
    Route::delete('/{id}', [AccountingCategoryController::class, 'destroy'])->name('destroy');
    Route::put('/{accountingCategory}/archive', [AccountingCategoryController::class, 'archive'])->name('archive');
    Route::put('/{accountingCategory}/restore', [AccountingCategoryController::class, 'restore'])->name('restore');

    Route::post('/category/add',         [AccountingCategoryController::class, 'addCategory'])->name('category.add');
    Route::post('/sub-category/add',     [AccountingCategoryController::class, 'addSubCategory'])->name('sub-category.add');
    Route::delete('/sub-category/{id}',  [AccountingCategoryController::class, 'destroySubCategory'])->name('sub-category.destroy');
});

Route::prefix('accounts-payables')->name('accounts-payables.')->group(function () {

    // LIST + CREATE + STORE
    Route::get('/', [AccountPayableController::class, 'index'])->name('index');
    Route::get('/create', [AccountPayableController::class, 'create'])->name('create');
    Route::post('/store', [AccountPayableController::class, 'store'])->name('store');

    // VIEW DETAILS (View Invoice)
    Route::get('/{id}/show', [AccountPayableController::class, 'show'])->name('show');

    // EDIT + UPDATE
    Route::get('/{id}/edit', [AccountPayableController::class, 'edit'])->name('edit');
    Route::put('/{id}/update', [AccountPayableController::class, 'update'])->name('update');

    // APPROVAL ACTIONS
    Route::put('/{id}/approve', [AccountPayableController::class, 'approve'])->name('approve');
    Route::put('/{id}/disapprove', [AccountPayableController::class, 'disapprove'])->name('disapprove');

    // ATTACHMENTS
    Route::get('/{id}/attachments', [AccountPayableController::class, 'viewAttachments'])->name('attachments');
    Route::post('/{id}/attach', [AccountPayableController::class, 'addAttachment'])->name('attach');

    // ARCHIVE + DELETE
    Route::put('/{id}/archive', [AccountPayableController::class, 'archive'])->name('archive');
    Route::delete('/{id}/destroy', [AccountPayableController::class, 'destroy'])->name('destroy');

    Route::get('/{id}/amount-details', [AccountPayableController::class, 'amountDetails'])
    ->name('amount-details');

    Route::get('/accounts-payables/{id}/invoice', [AccountPayableController::class, 'invoice'])
    ->name('accounts-payables.invoice');

    Route::post('/make-payment', [AccountPayableController::class, 'makePayment'])
    ->name('makePayment');
    
    Route::get('/get-types/{category}', [AccountPayableController::class, 'getTypes'])
    ->name('getTypes');
});

Route::prefix('taxes')->name('taxes.')->group(function () {
    Route::get('/', [TaxController::class, 'index'])->name('index');
    Route::post('/', [TaxController::class, 'store'])->name('store');
    Route::put('/{tax}/archive', [TaxController::class, 'archive'])->name('archive');
    Route::put('/{tax}/restore', [TaxController::class, 'restore'])->name('restore');
    Route::get('/{tax}/edit', [TaxController::class, 'edit'])->name('edit');
    Route::put('/{tax}', [TaxController::class, 'update'])->name('update');
    Route::delete('/{tax}', [TaxController::class, 'destroy'])->name('destroy');
});

Route::get('/accounts-receivable', [AccountReceivableController::class, 'index'])->name('account-receivable.index');
Route::get('/accounts-receivable/filter', [AccountReceivableController::class, 'filter']);
Route::get('/accounts-receivable/create', [AccountReceivableController::class, 'create'])->name('account-receivable.create');
Route::get('/accounts-receivable/{id}/edit', [AccountReceivableController::class, 'edit'])->name('account-receivable.edit');
Route::post('/accounts-receivable/{id}/update', [AccountReceivableController::class, 'update'])->name('account-receivable.update');
Route::post('/accounts-receivable/store', [AccountReceivableController::class, 'store'])->name('account-receivable.store');
Route::get('/api/receivable/categories', [AccountReceivableController::class, 'getCategories']);
Route::get('/api/receivable/types', [AccountReceivableController::class, 'getTypes']);
Route::post('/accounts-receivable/{id}/status', [AccountReceivableController::class, 'updateStatus']);
Route::get('/receive-payment-options', [AccountReceivableController::class, 'receivePaymentOptions']);
Route::post('/accounts-receivables/{id}/payments', [AccountReceivableController::class, 'updatePayment']);
Route::patch('/accounts-receivable/{id}/due-date', [AccountReceivableController::class, 'updateDueDate']);
Route::get('/api/receivable/account-names', [AccountReceivableController::class, 'getAccountNames']);

Route::get('/pos-clossing', [PosClossingController::class, 'index'])->name('pos-clossing.index');
Route::get('/pos-clossing/closed', [PosClossingController::class, 'getClosed'])->name('pos-clossing.closed');
Route::post('/pos-clossing/store', [PosClossingController::class, 'store'])->name('pos-clossing.store');
Route::get('/pos-clossing/create', [PosClossingController::class, 'create'])->name('pos-clossing.create');

Route::prefix('workforce-leaves')->name('leaves.')->group(function () {
    Route::get('/', [LeavesController::class, 'index'])->name('index');
    Route::get('/fetch', [LeavesController::class, 'fetchLeaves'])->name('fetch');
    
    Route::post('/', [LeavesController::class, 'store']);
    Route::put('/{id}', [LeavesController::class, 'update']);
    
    Route::patch('/{id}/archive', [LeavesController::class, 'archive']);
    Route::patch('/{id}/restore', [LeavesController::class, 'restore']);
    
    Route::delete('/{id}', [LeavesController::class, 'destroy']);
});

Route::prefix('workforce-allowances')->name('allowances.')->group(function () {
    Route::get('/', [AllowancesController::class, 'index'])->name('index');
    Route::get('/fetch', [AllowancesController::class, 'fetchAllowances'])->name('fetch');
    
    Route::post('/', [AllowancesController::class, 'store']);
    Route::put('/{id}', [AllowancesController::class, 'update']);
    
    Route::patch('/{id}/archive', [AllowancesController::class, 'archive']);
    Route::patch('/{id}/restore', [AllowancesController::class, 'restore']);
    
    Route::delete('/{id}', [AllowancesController::class, 'destroy']);
});

Route::prefix('order-reservations')->name('order-reservations.')->group(function () {
    Route::get('/', [OrderAndReservationController::class, 'index'])
        ->name('index');

    // ── Create ──────────────────────────────────────────────────
    Route::get('/create', [OrderAndReservationController::class, 'create'])
        ->name('create');
    Route::post('/', [OrderAndReservationController::class, 'store'])
        ->name('store');

    // ── Edit ────────────────────────────────────────────────────
    Route::get('/{orderReservation}/edit', [OrderAndReservationController::class, 'edit'])
        ->name('edit');
    Route::put('/{orderReservation}', [OrderAndReservationController::class, 'update'])
        ->name('update');


    // ── Status Actions ──────────────────────────────────────────
    Route::post('/{orderReservation}/ready-for-service', [OrderAndReservationController::class, 'readyForService'])
        ->name('ready-for-service');
    Route::put('/{orderReservation}/archive', [OrderAndReservationController::class, 'archive'])
        ->name('archive');
    Route::put('/{orderReservation}/restore', [OrderAndReservationController::class, 'restore'])
        ->name('restore');
    Route::delete('/{orderReservation}', [OrderAndReservationController::class, 'destroy'])
        ->name('destroy');

        // View Invoice
Route::get('/{orderReservation}/invoice', [OrderAndReservationController::class, 'invoice'])
    ->name('invoice');
});

// Night Differentials routes
Route::get('/night-differentials', [App\Http\Controllers\NightDifferentialController::class, 'index'])->name('night-differentials.index');
Route::post('/night-differentials', [App\Http\Controllers\NightDifferentialController::class, 'store'])->name('night-differentials.store');

Route::prefix('benefits')->name('benefits.')->group(function () {
    Route::get('/', [BenefitController::class, 'index'])->name('index');
    Route::post('/', [BenefitController::class, 'store'])->name('store');

    Route::get('/{benefit}/edit', [BenefitController::class, 'edit'])->name('edit');
    Route::put('/{benefit}', [BenefitController::class, 'update'])->name('update');

    Route::delete('/{benefit}', [BenefitController::class, 'destroy'])->name('destroy');
    Route::put('/{benefit}/archive', [BenefitController::class, 'archive'])->name('archive');
    Route::put('/{benefit}/restore', [BenefitController::class, 'restore'])->name('restore');
});


Route::prefix('holidays')->name('holidays.')->group(function () {
    Route::get('/', [HolidayController::class, 'index'])->name('index');
    Route::post('/', [HolidayController::class, 'store'])->name('store');

    Route::get('/{holiday}/edit', [HolidayController::class, 'edit'])->name('edit');
    Route::put('/{holiday}', [HolidayController::class, 'update'])->name('update');

    Route::delete('/{holiday}', [HolidayController::class, 'destroy'])->name('destroy');
    Route::put('/{holiday}/archive', [HolidayController::class, 'archive'])->name('archive');
    Route::put('/{holiday}/restore', [HolidayController::class, 'restore'])->name('restore');
});

Route::prefix('workforce-shifts')->name('shifts.')->group(function () {
    Route::get('/', [ShiftsController::class, 'index'])->name('index');
    Route::get('/fetch', [ShiftsController::class, 'fetchShifts'])->name('fetch');
    
    Route::post('/', [ShiftsController::class, 'store']);
    Route::put('/{id}', [ShiftsController::class, 'update']);
    
    Route::patch('/{id}/archive', [ShiftsController::class, 'archive']);
    Route::patch('/{id}/restore', [ShiftsController::class, 'restore']);
    
    Route::delete('/{id}', [ShiftsController::class, 'destroy']);
});

Route::prefix('daily-time-records')->name('dtr.')->group(function () {

    Route::get('/', [DailyTimeRecordController::class, 'index'])->name('index');
    Route::post('/', [DailyTimeRecordController::class, 'store'])->name('store');

    Route::get('/{dtr}/edit', [DailyTimeRecordController::class, 'edit'])->name('edit');
    Route::put('/{dtr}', [DailyTimeRecordController::class, 'update'])->name('update');
    Route::delete('/{dtr}', [DailyTimeRecordController::class, 'destroy'])->name('destroy');

});

Route::prefix('inventory/transfer')->name('transfers.')->group(function () {
    Route::get('/', [InventoryTransferController::class, 'index'])->name('index');
    Route::get('/fetch', [InventoryTransferController::class, 'fetchTransfers'])->name('fetch');
    
    Route::get('/create', [InventoryTransferController::class, 'create'])->name('create');
    Route::get('/create/request', fn () => redirect()->route('transfers.create', ['transfer_type' => 'request']));
    Route::get('/create/send', fn () => redirect()->route('transfers.create', ['transfer_type' => 'send']));


    Route::post('/', [InventoryTransferController::class, 'store']);
    Route::get('/{id}/edit', [InventoryTransferController::class, 'edit'])->name('edit');
    Route::put('/{id}', [InventoryTransferController::class, 'update']);
    Route::put('/{id}/update-status', [InventoryTransferController::class, 'updateStatus']);
    
    Route::patch('/{id}/archive', [InventoryTransferController::class, 'archive']);
    Route::patch('/{id}/restore', [InventoryTransferController::class, 'restore']);

    Route::get('/{id}/send-out', [InventoryTransferController::class, 'sendOutForm'])->name('sendOutForm');
    Route::post('/{id}/send', [InventoryTransferController::class, 'storeSendOut']);
    Route::post('/{id}/receive', [InventoryTransferController::class, 'receiveTransfer']);
    
    Route::delete('/{id}', [InventoryTransferController::class, 'destroy']);
});

Route::prefix('workforce/leave-requests')->name('leave-requests.')->group(function () {
    Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
    Route::get('/fetch', [LeaveRequestController::class, 'fetchLeaveRequests'])->name('fetch');
    Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
    Route::put('/{id}/update-status', [LeaveRequestController::class, 'updateStatus']);
    Route::get('/{id}/edit', [LeaveRequestController::class, 'edit'])->name('leave-requests.edit');
    Route::put('/{id}', [LeaveRequestController::class, 'update'])->name('leave-requests.update');
});

Route::prefix('settings/table-layouts')->name('table-layouts.')->group(function () {
    Route::get('/', [TableLayoutController::class, 'index'])->name('index');
    // Route::get('/fetch', [LeaveRequestController::class, 'fetchLeaveRequests'])->name('fetch');
    // Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
    // Route::put('/{id}/update-status', [LeaveRequestController::class, 'updateStatus']);
    // Route::get('/{id}/edit', [LeaveRequestController::class, 'edit'])->name('leave-requests.edit');
    // Route::put('/{id}', [LeaveRequestController::class, 'update'])->name('leave-requests.update');
});

Route::prefix('settings/stations')->name('stations.')->group(function () {
    Route::get('/', [StationController::class, 'index'])->name('index');
    Route::get('/fetch', [StationController::class, 'fetchStations'])->name('fetch');
    Route::post('/', [StationController::class, 'store'])->name('store');
    Route::put('/{id}', [StationController::class, 'update'])->name('update');
    Route::delete('/{id}', [StationController::class, 'destroy'])->name('destroy');
    Route::patch('/{id}/archive', [StationController::class, 'archive'])->name('archive');
    Route::patch('/{id}/restore', [StationController::class, 'restore'])->name('restore');
});

Route::prefix('/bundled-items')->name('bundled-items.')->group(function () {
    Route::get('/', [BundledItemController::class, 'index'])->name('index');
    Route::get('/fetch', [BundledItemController::class, 'fetchItems'])->name('fetch');
    Route::get('/create', [BundledItemController::class, 'create'])->name('create');
    Route::post('/', [BundledItemController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [BundledItemController::class, 'edit'])->name('edit');
    Route::put('/{id}', [BundledItemController::class, 'update'])->name('update');
    // Route::delete('/{id}', [BundledItemController::class, 'destroy'])->name('destroy');
    Route::put('/{id}/archive', [BundledItemController::class, 'archive'])->name('archive');
    Route::put('/{id}/restore', [BundledItemController::class, 'restore'])->name('restore');
});

Route::prefix('/chart-of-accounts')->name('chart-of-accounts.')->group(function () {
    Route::get('/', [ChartofAccountController::class, 'index'])->name('index');
    Route::get('/fetch', [ChartofAccountController::class, 'fetchItems'])->name('fetch');

    Route::post('/', [ChartofAccountController::class, 'store'])->name('store');
    Route::put('/{id}', [ChartofAccountController::class, 'update']);

    Route::put('/{id}/archive', [ChartofAccountController::class, 'archive'])->name('archive');
    Route::put('/{id}/restore', [ChartofAccountController::class, 'restore'])->name('restore');

    Route::delete('/{id}', [ChartofAccountController::class, 'destroy']);

});

Route::prefix('/inventory/kitchen-mrp')->name('kitchen-mrp.')->group(function () {
    Route::get('/', [KitchenmrpController::class, 'index'])->name('index');
    Route::get('/fetch', [KitchenmrpController::class, 'fetchRequests'])->name('fetch');

    Route::get('/create', [KitchenmrpController::class, 'create'])->name('create');
    Route::post('/', [KitchenmrpController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [KitchenmrpController::class, 'edit'])->name('edit');
    Route::get('/{id}/logGoods', [KitchenmrpController::class, 'isLogProcess'])->name('logGoods');
    Route::put('/{id}', [KitchenmrpController::class, 'update']);

    Route::put('/{id}/update-status', [KitchenmrpController::class, 'updateStatus']);

    Route::put('/{id}/archive', [KitchenmrpController::class, 'archive'])->name('archive');
    Route::put('/{id}/restore', [KitchenmrpController::class, 'restore'])->name('restore');

    Route::delete('/{id}', [KitchenmrpController::class, 'destroy']);

});

Route::prefix('/reports/general-ledger')->name('general-ledger.')->group(function () {
    Route::get('/', [GeneralLedgerController::class, 'index'])->name('index');
    Route::get('/fetch', [GeneralLedgerController::class, 'fetchRequests'])->name('fetch');

    // Route::get('/create', [KitchenmrpController::class, 'create'])->name('create');
    // Route::post('/', [KitchenmrpController::class, 'store'])->name('store');
    // Route::get('/{id}/edit', [KitchenmrpController::class, 'edit'])->name('edit');
    // Route::get('/{id}/logGoods', [KitchenmrpController::class, 'isLogProcess'])->name('logGoods');
    // Route::put('/{id}', [KitchenmrpController::class, 'update']);

    // Route::put('/{id}/update-status', [KitchenmrpController::class, 'updateStatus']);

    // Route::put('/{id}/archive', [KitchenmrpController::class, 'archive'])->name('archive');
    // Route::put('/{id}/restore', [KitchenmrpController::class, 'restore'])->name('restore');

    // Route::delete('/{id}', [KitchenmrpController::class, 'destroy']);

});