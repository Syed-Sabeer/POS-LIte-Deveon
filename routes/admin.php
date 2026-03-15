use App\Http\Controllers\Admin\AdminWebsiteController;
use App\Http\Controllers\Admin\AdminContactController;
use App\Http\Controllers\Admin\AdminAboutController;

Route::put('/website/sections/update', [AdminWebsiteController::class, 'updateAllSections'])->name('admin.website.sections.update');

// Contact Routes
Route::get('/contact', [AdminContactController::class, 'index'])->name('admin.contact.index');
Route::put('/contact/update', [AdminContactController::class, 'updateContact'])->name('admin.contact.update');

// About Routes
Route::get('/about', [AdminAboutController::class, 'index'])->name('admin.about.index');
Route::put('/about/update', [AdminAboutController::class, 'update'])->name('admin.about.update');

