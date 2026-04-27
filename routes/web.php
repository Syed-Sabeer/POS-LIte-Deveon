
<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountingModuleController;
use App\Http\Controllers\BalanceSheetController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\V2\AccountController as V2AccountController;
use App\Http\Controllers\V2\AccountDetailController as V2AccountDetailController;
use App\Http\Controllers\V2\DashboardController as V2DashboardController;
use App\Http\Controllers\V2\InvoiceController as V2InvoiceController;
use App\Http\Controllers\V2\ItemController as V2ItemController;
use App\Http\Controllers\V2\JournalVoucherController as V2JournalVoucherController;
use App\Http\Controllers\V2\LedgerController as V2LedgerController;
use App\Http\Controllers\V2\MasterDataController as V2MasterDataController;
use App\Http\Controllers\V2\ReportController as V2ReportController;
use App\Http\Controllers\V2\StockLedgerController as V2StockLedgerController;
use App\Http\Controllers\V2\UserRightsController as V2UserRightsController;
use App\Http\Controllers\V2\UtilityController as V2UtilityController;
use App\Http\Controllers\V2\VoucherController as V2VoucherController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::post('stripe/webhook', [BillingController::class, 'webhook'])->name('billing.webhook');



Route::middleware('auth')->get('storage-link', function () {
    try {
        $link = public_path('storage');

        // Remove existing symlink if it exists
        if (File::exists($link)) {
            File::delete($link); // works for symlink
        }

        // Recreate symlink
        Artisan::call('storage:link');

        return response()->json([
            'ok' => true,
            'message' => 'Storage link recreated successfully.',
            'output' => trim(Artisan::output()),
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'message' => 'Failed to recreate storage link.',
            'error' => $e->getMessage(),
        ], 500);
    }
})->name('storage.link');

Route::get('/', [V2DashboardController::class, 'index'])
    ->middleware(['auth', 'subscription.active', 'permission:v2 dashboard'])
    ->name('home');

Route::middleware('guest')->group(function () {
    Route::get('admin/login', [LoginController::class, 'login'])->name('login');
    Route::post('login-attempt', [LoginController::class, 'loginAttempt'])->name('login.attempt');
    Route::get('login', [LoginController::class, 'userlogin'])->name('user.login');

    Route::get('register', [RegisterController::class, 'register'])->name('register');
    Route::post('registration-attempt', [RegisterController::class, 'registerAttempt'])->name('register.attempt');

    Route::get('reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware(['auth', 'subscription.active'])->group(function () {
    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('billing/confirm', [BillingController::class, 'confirm'])->name('billing.confirm');

    Route::get('login-verification', [AuthController::class, 'login_verification'])->name('login.verification');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('verify-account', [AuthController::class, 'verify_account'])->name('verify.account');
    Route::post('resend-code', [AuthController::class, 'resend_code'])->name('resend.code');

    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verification_verify'])->middleware('signed')->name('verification.verify');
    Route::get('email/verify', [AuthController::class, 'verification_notice'])->name('verification.notice');
    Route::post('email/verification-notification', [AuthController::class, 'verification_send'])->middleware('throttle:2,1')->name('verification.send');

    // Route::get('dashboard', [AdminDashboardController::class, 'index'])
    //     ->middleware('permission:view dashboard')
    //     ->name('home');

    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/', fn () => redirect()->route('accounting.sales'))->name('index');
        Route::get('sales', [AccountingModuleController::class, 'sales'])->name('sales');
        Route::post('sales', [AccountingModuleController::class, 'storeSales'])->name('sales.store');
        Route::get('purchase', [AccountingModuleController::class, 'purchase'])->name('purchase');
        Route::post('purchase', [AccountingModuleController::class, 'storePurchase'])->name('purchase.store');
        Route::get('receivable', [AccountingModuleController::class, 'receivable'])->name('receivable');
        Route::post('receivable', [AccountingModuleController::class, 'storeReceivable'])->name('receivable.store');
        Route::get('payable', [AccountingModuleController::class, 'payable'])->name('payable');
        Route::post('payable', [AccountingModuleController::class, 'storePayable'])->name('payable.store');
    });

    Route::prefix('v2')->name('v2.')->group(function () {
        Route::get('/', [V2DashboardController::class, 'index'])->middleware('permission:v2 dashboard')->name('dashboard');

        Route::get('accounts', [V2AccountController::class, 'index'])->middleware('permission:v2 accounts manager')->name('accounts.index');
        Route::post('accounts', [V2AccountController::class, 'store'])->middleware('permission:v2 insert')->name('accounts.store');
        Route::get('accounts/{account}/edit', [V2AccountController::class, 'edit'])->middleware('permission:v2 edit')->name('accounts.edit');
        Route::put('accounts/{account}', [V2AccountController::class, 'update'])->middleware('permission:v2 edit')->name('accounts.update');
        Route::delete('accounts/{account}', [V2AccountController::class, 'destroy'])->middleware('permission:v2 delete')->name('accounts.destroy');

        Route::get('account-details', [V2AccountDetailController::class, 'index'])->middleware('permission:v2 account details manager')->name('account-details.index');
        Route::post('account-details', [V2AccountDetailController::class, 'store'])->middleware('permission:v2 insert')->name('account-details.store');
        Route::get('account-details/{accountDetail}/edit', [V2AccountDetailController::class, 'edit'])->middleware('permission:v2 edit')->name('account-details.edit');
        Route::put('account-details/{accountDetail}', [V2AccountDetailController::class, 'update'])->middleware('permission:v2 edit')->name('account-details.update');
        Route::delete('account-details/{accountDetail}', [V2AccountDetailController::class, 'destroy'])->middleware('permission:v2 delete')->name('account-details.destroy');

        Route::get('categories', [V2MasterDataController::class, 'categories'])->middleware('permission:v2 category manager')->name('categories.index');
        Route::post('categories', [V2MasterDataController::class, 'storeCategory'])->middleware('permission:v2 insert')->name('categories.store');
        Route::put('categories/{category}', [V2MasterDataController::class, 'updateCategory'])->middleware('permission:v2 edit')->name('categories.update');
        Route::delete('categories/{category}', [V2MasterDataController::class, 'destroyCategory'])->middleware('permission:v2 delete')->name('categories.destroy');
        Route::get('brands', [V2MasterDataController::class, 'brands'])->middleware('permission:v2 brand manager')->name('brands.index');
        Route::post('brands', [V2MasterDataController::class, 'storeBrand'])->middleware('permission:v2 insert')->name('brands.store');
        Route::put('brands/{brand}', [V2MasterDataController::class, 'updateBrand'])->middleware('permission:v2 edit')->name('brands.update');
        Route::delete('brands/{brand}', [V2MasterDataController::class, 'destroyBrand'])->middleware('permission:v2 delete')->name('brands.destroy');

        Route::get('items', [V2ItemController::class, 'index'])->middleware('permission:v2 stock manager')->name('items.index');
        Route::post('items', [V2ItemController::class, 'store'])->middleware('permission:v2 insert')->name('items.store');
        Route::get('items/{item}/edit', [V2ItemController::class, 'edit'])->middleware('permission:v2 edit')->name('items.edit');
        Route::put('items/{item}', [V2ItemController::class, 'update'])->middleware('permission:v2 edit')->name('items.update');
        Route::delete('items/{item}', [V2ItemController::class, 'destroy'])->middleware('permission:v2 delete')->name('items.destroy');

        Route::get('purchase-invoices', [V2InvoiceController::class, 'index'])->defaults('type', 'purchase')->middleware('permission:v2 purchase book')->name('purchase.index');
        Route::get('purchase-invoices/create', [V2InvoiceController::class, 'create'])->defaults('type', 'purchase')->middleware('permission:v2 purchase book')->name('purchase.create');
        Route::post('purchase-invoices', [V2InvoiceController::class, 'store'])->defaults('type', 'purchase')->middleware('permission:v2 insert')->name('purchase.store');
        Route::get('purchase-invoices/{invoice}', [V2InvoiceController::class, 'show'])->middleware('permission:v2 purchase book')->name('purchase.show');
        Route::get('purchase-invoices/{invoice}/edit', [V2InvoiceController::class, 'edit'])->middleware('permission:v2 edit')->name('purchase.edit');
        Route::put('purchase-invoices/{invoice}', [V2InvoiceController::class, 'update'])->middleware('permission:v2 edit')->name('purchase.update');
        Route::delete('purchase-invoices/{invoice}', [V2InvoiceController::class, 'destroy'])->middleware('permission:v2 delete')->name('purchase.destroy');
        Route::get('purchase-invoices/{invoice}/print/{format?}', [V2InvoiceController::class, 'print'])->middleware('permission:v2 purchase book')->name('purchase.print');

        Route::get('sale-invoices', [V2InvoiceController::class, 'index'])->defaults('type', 'sale')->middleware('permission:v2 sale bill book')->name('sales.index');
        Route::get('sale-invoices/create', [V2InvoiceController::class, 'create'])->defaults('type', 'sale')->middleware('permission:v2 sale bill book')->name('sales.create');
        Route::post('sale-invoices', [V2InvoiceController::class, 'store'])->defaults('type', 'sale')->middleware('permission:v2 insert')->name('sales.store');
        Route::get('sale-invoices/{invoice}', [V2InvoiceController::class, 'show'])->middleware('permission:v2 sale bill book')->name('sales.show');
        Route::get('sale-invoices/{invoice}/edit', [V2InvoiceController::class, 'edit'])->middleware('permission:v2 edit')->name('sales.edit');
        Route::put('sale-invoices/{invoice}', [V2InvoiceController::class, 'update'])->middleware('permission:v2 edit')->name('sales.update');
        Route::delete('sale-invoices/{invoice}', [V2InvoiceController::class, 'destroy'])->middleware('permission:v2 delete')->name('sales.destroy');
        Route::get('sale-invoices/{invoice}/print/{format?}', [V2InvoiceController::class, 'print'])->middleware('permission:v2 sale bill book')->name('sales.print');

        Route::get('receipts', [V2VoucherController::class, 'index'])->defaults('type', 'receipt')->middleware('permission:v2 receipt vouchers')->name('receipts.index');
        Route::get('receipts/create', [V2VoucherController::class, 'create'])->defaults('type', 'receipt')->middleware('permission:v2 receipt vouchers')->name('receipts.create');
        Route::post('receipts', [V2VoucherController::class, 'store'])->defaults('type', 'receipt')->middleware('permission:v2 insert')->name('receipts.store');
        Route::get('receipts/{voucher}', [V2VoucherController::class, 'show'])->middleware('permission:v2 receipt vouchers')->name('receipts.show');
        Route::get('receipts/{voucher}/edit', [V2VoucherController::class, 'edit'])->middleware('permission:v2 edit')->name('receipts.edit');
        Route::put('receipts/{voucher}', [V2VoucherController::class, 'update'])->middleware('permission:v2 edit')->name('receipts.update');
        Route::delete('receipts/{voucher}', [V2VoucherController::class, 'destroy'])->middleware('permission:v2 delete')->name('receipts.destroy');
        Route::get('receipts/{voucher}/print', [V2VoucherController::class, 'print'])->middleware('permission:v2 receipt vouchers')->name('receipts.print');

        Route::get('payments', [V2VoucherController::class, 'index'])->defaults('type', 'payment')->middleware('permission:v2 payment vouchers')->name('payments.index');
        Route::get('payments/create', [V2VoucherController::class, 'create'])->defaults('type', 'payment')->middleware('permission:v2 payment vouchers')->name('payments.create');
        Route::post('payments', [V2VoucherController::class, 'store'])->defaults('type', 'payment')->middleware('permission:v2 insert')->name('payments.store');
        Route::get('payments/{voucher}', [V2VoucherController::class, 'show'])->middleware('permission:v2 payment vouchers')->name('payments.show');
        Route::get('payments/{voucher}/edit', [V2VoucherController::class, 'edit'])->middleware('permission:v2 edit')->name('payments.edit');
        Route::put('payments/{voucher}', [V2VoucherController::class, 'update'])->middleware('permission:v2 edit')->name('payments.update');
        Route::delete('payments/{voucher}', [V2VoucherController::class, 'destroy'])->middleware('permission:v2 delete')->name('payments.destroy');
        Route::get('payments/{voucher}/print', [V2VoucherController::class, 'print'])->middleware('permission:v2 payment vouchers')->name('payments.print');

        Route::get('journal-vouchers', [V2JournalVoucherController::class, 'index'])->middleware('permission:v2 journal vouchers')->name('journal.index');
        Route::get('journal-vouchers/create', [V2JournalVoucherController::class, 'create'])->middleware('permission:v2 journal vouchers')->name('journal.create');
        Route::post('journal-vouchers', [V2JournalVoucherController::class, 'store'])->middleware('permission:v2 insert')->name('journal.store');
        Route::get('journal-vouchers/{voucher}', [V2JournalVoucherController::class, 'show'])->middleware('permission:v2 journal vouchers')->name('journal.show');
        Route::get('journal-vouchers/{voucher}/print', [V2JournalVoucherController::class, 'print'])->middleware('permission:v2 journal vouchers')->name('journal.print');

        Route::get('ledgers/{type}', [V2LedgerController::class, 'summary'])->name('ledgers.summary');
        Route::get('ledger-account/{account}/{mode?}', [V2LedgerController::class, 'detail'])->middleware('permission:v2 dashboard')->name('ledgers.detail');
        Route::get('stock-ledger', [V2StockLedgerController::class, 'index'])->middleware('permission:v2 stock ledger')->name('stock-ledger.index');
        Route::get('stock-ledger/{item}/{report?}', [V2StockLedgerController::class, 'statement'])->middleware('permission:v2 stock ledger')->name('stock-ledger.statement');

        Route::get('reports', [V2ReportController::class, 'index'])->middleware('permission:v2 trial balance|v2 trial balance aging|v2 income statement|v2 balance sheet')->name('reports.index');
        Route::get('reports/{report}', [V2ReportController::class, 'show'])->middleware('permission:v2 trial balance|v2 trial balance aging|v2 income statement|v2 balance sheet')->name('reports.show');

        Route::get('users', [V2UserRightsController::class, 'index'])->middleware('permission:v2 add remove users')->name('users.index');
        Route::put('users/{user}/rights', [V2UserRightsController::class, 'update'])->middleware('permission:v2 add remove users')->name('users.rights.update');
        Route::get('utilities/backup', [V2UtilityController::class, 'backup'])->middleware('permission:v2 backup restore')->name('utilities.backup');
        Route::get('utilities/restore', [V2UtilityController::class, 'restore'])->middleware('permission:v2 backup restore')->name('utilities.restore');
    });

    Route::resource('access/roles', RoleManagementController::class)
        ->except(['show'])
        ->middleware('permission:manage chart of accounts')
        ->names('access.roles');

    Route::resource('access/users', UserManagementController::class)
        ->except(['show'])
        ->middleware('permission:manage chart of accounts')
        ->names('access.users');

    Route::resource('products', ProductController::class)->middleware('permission:manage products');
    Route::resource('admin/products', AdminProductController::class)
        ->middleware('permission:manage products')
        ->names('admin.products');

    Route::get('poss', [PosController::class, 'index'])->middleware('permission:pos checkout')->name('pos.index');
    Route::post('pos/checkout', [PosController::class, 'checkout'])->middleware('permission:pos checkout')->name('pos.checkout');
    Route::post('pos/checkout-sync', [PosController::class, 'checkoutSync'])->middleware('permission:pos checkout')->name('pos.checkout.sync');
    Route::get('pos/orders', [PosController::class, 'orders'])->middleware('permission:pos orders')->name('pos.orders');
    Route::get('pos/orders/{order}/edit', [PosController::class, 'edit'])->middleware('permission:pos orders')->name('pos.orders.edit');
    Route::put('pos/orders/{order}', [PosController::class, 'update'])->middleware('permission:pos orders')->name('pos.orders.update');
    Route::delete('pos/orders/{order}', [PosController::class, 'destroy'])->middleware('permission:pos orders')->name('pos.orders.destroy');
    Route::get('pos/orders/{order}', [PosController::class, 'show'])->middleware('permission:pos orders')->name('pos.orders.show');

    Route::get('customers', [CustomerController::class, 'index'])->middleware('permission:manage customers')->name('customers.index');
    Route::post('customers', [CustomerController::class, 'store'])->middleware('permission:manage customers')->name('customers.store');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('permission:manage customers')->name('customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:manage customers')->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:manage customers')->name('customers.destroy');

    Route::get('customer-payable', [CustomerPaymentController::class, 'payable'])->middleware('permission:manage customer payments')->name('customer-payable.index');
    Route::get('customer-payable/{customer}', [CustomerPaymentController::class, 'payableCreate'])->middleware('permission:manage customer payments')->name('customer-payable.create');

    Route::get('stock', [StockController::class, 'index'])->middleware('permission:manage stock')->name('stock.index');
    Route::post('stock/adjust', [StockController::class, 'adjust'])->middleware('permission:manage stock')->name('stock.adjust');

    Route::resource('suppliers', SupplierController::class)->middleware('permission:manage suppliers');

    Route::get('purchases', [PurchaseInvoiceController::class, 'index'])->middleware('permission:manage purchases')->name('purchases.index');
    Route::get('purchases/create', [PurchaseInvoiceController::class, 'create'])->middleware('permission:manage purchases')->name('purchases.create');
    Route::post('purchases', [PurchaseInvoiceController::class, 'store'])->middleware('permission:manage purchases')->name('purchases.store');
    Route::get('purchases/{purchase}', [PurchaseInvoiceController::class, 'show'])->middleware('permission:manage purchases')->name('purchases.show');
    Route::get('purchases/{purchase}/edit', [PurchaseInvoiceController::class, 'edit'])->middleware('permission:manage purchases')->name('purchases.edit');
    Route::put('purchases/{purchase}', [PurchaseInvoiceController::class, 'update'])->middleware('permission:manage purchases')->name('purchases.update');
    Route::post('purchases/{purchase}/post', [PurchaseInvoiceController::class, 'post'])->middleware('permission:manage purchases')->name('purchases.post');

    Route::get('customer-payments', [CustomerPaymentController::class, 'index'])->middleware('role_or_permission:manage customer payments|manage receivables')->name('customer-payments.index');
    Route::get('customer-payments/create', [CustomerPaymentController::class, 'create'])->middleware('role_or_permission:manage customer payments|manage receivables')->name('customer-payments.create');
    Route::post('customer-payments', [CustomerPaymentController::class, 'store'])->middleware('role_or_permission:manage customer payments|manage receivables')->name('customer-payments.store');
    Route::get('customer-payments/{customerPayment}', [CustomerPaymentController::class, 'show'])->middleware('role_or_permission:manage customer payments|manage receivables')->name('customer-payments.show');
    Route::get('customer-payments/{customerPayment}/receipt', [CustomerPaymentController::class, 'receipt'])->middleware('role_or_permission:manage customer payments|manage receivables')->name('customer-payments.receipt');

    Route::get('supplier-payments', [SupplierPaymentController::class, 'index'])->middleware('role_or_permission:manage supplier payments|manage payables')->name('supplier-payments.index');
    Route::get('supplier-payments/create', [SupplierPaymentController::class, 'create'])->middleware('role_or_permission:manage supplier payments|manage payables')->name('supplier-payments.create');
    Route::post('supplier-payments', [SupplierPaymentController::class, 'store'])->middleware('role_or_permission:manage supplier payments|manage payables')->name('supplier-payments.store');
    Route::get('supplier-payments/{supplierPayment}', [SupplierPaymentController::class, 'show'])->middleware('role_or_permission:manage supplier payments|manage payables')->name('supplier-payments.show');
    Route::get('supplier-payments/{supplierPayment}/voucher', [SupplierPaymentController::class, 'voucher'])->middleware('role_or_permission:manage supplier payments|manage payables')->name('supplier-payments.voucher');

    Route::get('reports/receivables', [FinanceReportController::class, 'receivables'])->middleware('role_or_permission:view receivables report|manage receivables')->name('reports.receivables');
    Route::get('reports/payables', [FinanceReportController::class, 'payables'])->middleware('role_or_permission:view payables report|manage payables')->name('reports.payables');
    Route::get('reports/balance-sheet', [BalanceSheetController::class, 'index'])->middleware('permission:view balance sheet')->name('reports.balance-sheet');

    Route::get('ledgers/customers', [LedgerController::class, 'customer'])->middleware('role_or_permission:view customer ledger|view customer statements')->name('ledgers.customers');
    Route::get('ledgers/customers/{customer}/statement', [LedgerController::class, 'customerStatement'])->middleware('role_or_permission:view customer ledger|view customer statements')->name('ledgers.customers.statement');
    Route::get('ledgers/suppliers', [LedgerController::class, 'supplier'])->middleware('role_or_permission:view supplier ledger|view vendor statements')->name('ledgers.suppliers');
    Route::get('ledgers/suppliers/{supplier}/statement', [LedgerController::class, 'supplierStatement'])->middleware('role_or_permission:view supplier ledger|view vendor statements')->name('ledgers.suppliers.statement');
    Route::get('ledgers/accounts', [LedgerController::class, 'account'])->middleware('role_or_permission:view journal entries|view general ledger')->name('ledgers.accounts');
    Route::get('ledgers/cash-book', [LedgerController::class, 'cashBook'])->middleware('role_or_permission:view journal entries|view general ledger')->name('ledgers.cash-book');
    Route::get('ledgers/bank-book', [LedgerController::class, 'bankBook'])->middleware('role_or_permission:view journal entries|view general ledger')->name('ledgers.bank-book');

    Route::get('accounts', [AccountController::class, 'index'])->middleware('role_or_permission:manage chart of accounts|manage accounts')->name('accounts.index');
    Route::get('accounts/tree', [AccountController::class, 'tree'])->middleware('role_or_permission:manage chart of accounts|manage accounts')->name('accounts.tree');
    Route::post('accounts', [AccountController::class, 'store'])->middleware('role_or_permission:manage chart of accounts|manage accounts')->name('accounts.store');
    Route::put('accounts/{account}', [AccountController::class, 'update'])->middleware('role_or_permission:manage chart of accounts|manage accounts')->name('accounts.update');

    Route::get('journals', [JournalEntryController::class, 'index'])->middleware('permission:view journal entries')->name('journals.index');
    Route::get('journals/{journalEntry}', [JournalEntryController::class, 'show'])->middleware('permission:view journal entries')->name('journals.show');

    Route::get('reports/sales', [PosController::class, 'reports'])->middleware('permission:view sales reports')->name('reports.sales');
    Route::get('reports/sales/export/excel', [PosController::class, 'exportDailySalesExcel'])->middleware('permission:view sales reports')->name('reports.sales.export.excel');
    Route::get('reports/sales/export/pdf', [PosController::class, 'exportDailySalesPdf'])->middleware('permission:view sales reports')->name('reports.sales.export.pdf');
});

