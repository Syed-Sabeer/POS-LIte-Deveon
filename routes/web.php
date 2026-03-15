
<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AccountController;
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
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('admin/login', [LoginController::class, 'login'])->name('login');
    Route::post('login-attempt', [LoginController::class, 'loginAttempt'])->name('login.attempt');
    Route::get('login', [LoginController::class, 'userlogin'])->name('user.login');

    Route::get('register', [RegisterController::class, 'register'])->name('register');
    Route::post('registration-attempt', [RegisterController::class, 'registerAttempt'])->name('register.attempt');

    Route::get('reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('login-verification', [AuthController::class, 'login_verification'])->name('login.verification');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('verify-account', [AuthController::class, 'verify_account'])->name('verify.account');
    Route::post('resend-code', [AuthController::class, 'resend_code'])->name('resend.code');

    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verification_verify'])->middleware('signed')->name('verification.verify');
    Route::get('email/verify', [AuthController::class, 'verification_notice'])->name('verification.notice');
    Route::post('email/verification-notification', [AuthController::class, 'verification_send'])->middleware('throttle:2,1')->name('verification.send');

    Route::get('dashboard', [AdminDashboardController::class, 'index'])
        ->middleware('permission:view dashboard')
        ->name('home');

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
    Route::get('pos/orders', [PosController::class, 'orders'])->middleware('permission:pos orders')->name('pos.orders');
    Route::get('pos/orders/{order}', [PosController::class, 'show'])->middleware('permission:pos orders')->name('pos.orders.show');

    Route::get('customers', [CustomerController::class, 'index'])->middleware('permission:manage customers')->name('customers.index');
    Route::post('customers', [CustomerController::class, 'store'])->middleware('permission:manage customers')->name('customers.store');

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

    Route::get('customer-payments', [CustomerPaymentController::class, 'index'])->middleware('permission:manage customer payments')->name('customer-payments.index');
    Route::get('customer-payments/create', [CustomerPaymentController::class, 'create'])->middleware('permission:manage customer payments')->name('customer-payments.create');
    Route::post('customer-payments', [CustomerPaymentController::class, 'store'])->middleware('permission:manage customer payments')->name('customer-payments.store');
    Route::get('customer-payments/{customerPayment}', [CustomerPaymentController::class, 'show'])->middleware('permission:manage customer payments')->name('customer-payments.show');
    Route::get('customer-payments/{customerPayment}/receipt', [CustomerPaymentController::class, 'receipt'])->middleware('permission:manage customer payments')->name('customer-payments.receipt');

    Route::get('supplier-payments', [SupplierPaymentController::class, 'index'])->middleware('permission:manage supplier payments')->name('supplier-payments.index');
    Route::get('supplier-payments/create', [SupplierPaymentController::class, 'create'])->middleware('permission:manage supplier payments')->name('supplier-payments.create');
    Route::post('supplier-payments', [SupplierPaymentController::class, 'store'])->middleware('permission:manage supplier payments')->name('supplier-payments.store');
    Route::get('supplier-payments/{supplierPayment}', [SupplierPaymentController::class, 'show'])->middleware('permission:manage supplier payments')->name('supplier-payments.show');
    Route::get('supplier-payments/{supplierPayment}/voucher', [SupplierPaymentController::class, 'voucher'])->middleware('permission:manage supplier payments')->name('supplier-payments.voucher');

    Route::get('reports/receivables', [FinanceReportController::class, 'receivables'])->middleware('permission:view receivables report')->name('reports.receivables');
    Route::get('reports/payables', [FinanceReportController::class, 'payables'])->middleware('permission:view payables report')->name('reports.payables');

    Route::get('ledgers/customers', [LedgerController::class, 'customer'])->middleware('permission:view customer ledger')->name('ledgers.customers');
    Route::get('ledgers/customers/{customer}/statement', [LedgerController::class, 'customerStatement'])->middleware('permission:view customer ledger')->name('ledgers.customers.statement');
    Route::get('ledgers/suppliers', [LedgerController::class, 'supplier'])->middleware('permission:view supplier ledger')->name('ledgers.suppliers');
    Route::get('ledgers/suppliers/{supplier}/statement', [LedgerController::class, 'supplierStatement'])->middleware('permission:view supplier ledger')->name('ledgers.suppliers.statement');
    Route::get('ledgers/accounts', [LedgerController::class, 'account'])->middleware('permission:view journal entries')->name('ledgers.accounts');
    Route::get('ledgers/cash-book', [LedgerController::class, 'cashBook'])->middleware('permission:view journal entries')->name('ledgers.cash-book');
    Route::get('ledgers/bank-book', [LedgerController::class, 'bankBook'])->middleware('permission:view journal entries')->name('ledgers.bank-book');

    Route::get('accounts', [AccountController::class, 'index'])->middleware('permission:manage chart of accounts')->name('accounts.index');
    Route::post('accounts', [AccountController::class, 'store'])->middleware('permission:manage chart of accounts')->name('accounts.store');
    Route::put('accounts/{account}', [AccountController::class, 'update'])->middleware('permission:manage chart of accounts')->name('accounts.update');

    Route::get('journals', [JournalEntryController::class, 'index'])->middleware('permission:view journal entries')->name('journals.index');
    Route::get('journals/{journalEntry}', [JournalEntryController::class, 'show'])->middleware('permission:view journal entries')->name('journals.show');

    Route::get('reports/sales', [PosController::class, 'reports'])->middleware('permission:view sales reports')->name('reports.sales');
    Route::get('reports/sales/export/excel', [PosController::class, 'exportDailySalesExcel'])->middleware('permission:view sales reports')->name('reports.sales.export.excel');
    Route::get('reports/sales/export/pdf', [PosController::class, 'exportDailySalesPdf'])->middleware('permission:view sales reports')->name('reports.sales.export.pdf');
});





