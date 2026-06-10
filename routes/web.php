<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttachmentController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\WorkOrderController as AdminWorkOrders;
use App\Http\Controllers\Admin\WorkOrderScheduleController as AdminWorkOrderSchedule;
use App\Http\Controllers\Admin\WorkOrderAssignmentController as AdminWorkOrderAssignment;
use App\Http\Controllers\Admin\UserController as AdminUsers;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoices;
use App\Http\Controllers\Admin\CalendarController as AdminCalendar;
use App\Http\Controllers\Admin\MyAnalyticsController as AdminMyAnalytics;
use App\Http\Controllers\Admin\SettingController as AdminSettings;
use App\Http\Controllers\Admin\ServiceTypeController as AdminServices;
use App\Http\Controllers\Admin\PendingCustomerController as AdminPendingCustomers;
use App\Http\Controllers\Admin\CompanyController as AdminCompanies;
use App\Http\Controllers\Admin\CustomerAnalyticsController as AdminAnalytics;
use App\Http\Controllers\Admin\CompanyAnalyticsController as AdminCompanyAnalytics;
use App\Http\Controllers\Admin\InquiryController as AdminInquiries;
use App\Http\Controllers\Admin\ReportController as AdminReports;
use App\Http\Controllers\Admin\DeviceCatalogController as AdminDeviceCatalog;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;
use App\Http\Controllers\Customer\WorkOrderController as CustomerWorkOrders;
use App\Http\Controllers\Customer\InvoiceController as CustomerInvoices;
use App\Http\Controllers\Customer\SiteController as CustomerSites;
use App\Http\Controllers\Customer\CompanyController as CustomerCompany;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboard;
use App\Http\Controllers\Employee\CalendarController as EmployeeCalendar;
use App\Http\Controllers\Employee\WorkOrderController as EmployeeWorkOrders;
use App\Http\Controllers\Employee\AccountController as EmployeeAccount;
use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

// ── Public ───────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/services', [HomeController::class, 'services'])->name('services');
Route::get('/about', fn () => view('public.about'))->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'contactSubmit']);
Route::get('/quote', [HomeController::class, 'quote'])->name('quote');
Route::post('/quote', [HomeController::class, 'quoteSubmit']);

// ── Logo (served through Laravel so we can set a long-lived cache header) ────
Route::get('/logo', function () {
    $path = public_path('images/logo.png');
    abort_if(!file_exists($path), 404);
    return response()->file($path, [
        'Cache-Control' => 'public, max-age=31536000, immutable',
        'Content-Type'  => 'image/png',
    ]);
})->name('site.logo');

// ── Device catalog data (for equipment autocomplete — any authenticated user) ─
Route::middleware('auth')->get('/device-catalog/data', [AdminDeviceCatalog::class, 'data'])->name('device-catalog.data');

// ── Attachment routes (auth required; access controlled in AttachmentController) ─
Route::middleware('auth')->group(function () {
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::get('/attachments/{attachment}/view', [AttachmentController::class, 'view'])->name('attachments.view');
});

// ── Profile photos ────────────────────────────────────────────────────────────
Route::middleware('auth')->get('/users/{user}/photo', function (\App\Models\User $user) {
    $path = storage_path('app/profile-photos/' . $user->profile_photo);
    abort_if(!$user->profile_photo || !file_exists($path), 404);
    return response()->file($path);
})->name('users.photo');

// ── Pending approval holding page (auth required, no role gate) ──────────────
Route::middleware('auth')->get('/account/pending', fn () => view('auth.pending'))->name('account.pending');

// ── Auth (Breeze) ─────────────────────────────────────────────────────────────
require __DIR__.'/auth.php';

// ── Shared profile (all authenticated users) ─────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/availability', [ProfileController::class, 'updateAvailability'])->name('profile.availability.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Customer portal ───────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:customer'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {
        Route::get('/dashboard', [CustomerDashboard::class, 'index'])->name('dashboard');
        Route::get('/work-orders', [CustomerWorkOrders::class, 'index'])->name('work-orders.index');
        Route::get('/work-orders/create', [CustomerWorkOrders::class, 'create'])->name('work-orders.create');
        Route::post('/work-orders', [CustomerWorkOrders::class, 'store'])->name('work-orders.store');
        Route::get('/work-orders/{workOrder}', [CustomerWorkOrders::class, 'show'])->name('work-orders.show');
        Route::patch('/work-orders/{workOrder}', [CustomerWorkOrders::class, 'update'])->name('work-orders.update');
        Route::post('/work-orders/{workOrder}/confirm', [CustomerWorkOrders::class, 'confirmVisit'])->name('work-orders.confirm');
        Route::post('/work-orders/{workOrder}/decline', [CustomerWorkOrders::class, 'declineVisit'])->name('work-orders.decline');
        Route::post('/work-orders/{workOrder}/visits/{visit}/confirm', [CustomerWorkOrders::class, 'confirmVisitByCustomer'])->name('work-orders.visits.confirm');
        Route::post('/work-orders/{workOrder}/visits/{visit}/decline', [CustomerWorkOrders::class, 'declineVisitByCustomer'])->name('work-orders.visits.decline');
        Route::post('/work-orders/{workOrder}/cancel', [CustomerWorkOrders::class, 'cancel'])->name('work-orders.cancel');
        Route::post('/work-orders/{workOrder}/submit-payment', [CustomerWorkOrders::class, 'submitPayment'])->name('work-orders.submit-payment');
        Route::get('/invoices', [CustomerInvoices::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [CustomerInvoices::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/print', [CustomerInvoices::class, 'printView'])->name('invoices.print');
        Route::post('/invoices/{invoice}/submit-payment', [CustomerInvoices::class, 'submitPayment'])->name('invoices.submit-payment');
        Route::post('/work-orders/{workOrder}/notes', [CustomerWorkOrders::class, 'addNote'])->name('work-orders.notes.add');
        Route::post('/work-orders/{workOrder}/attachments', [CustomerWorkOrders::class, 'addAttachment'])->name('work-orders.attachments.add');
        Route::delete('/work-orders/{workOrder}/attachments/{attachment}', [CustomerWorkOrders::class, 'removeAttachment'])->name('work-orders.attachments.remove');
        Route::post('/sites', [CustomerSites::class, 'store'])->name('sites.store');
        Route::patch('/sites/{address}', [CustomerSites::class, 'update'])->name('sites.update');
        Route::post('/sites/{address}/deactivate', [CustomerSites::class, 'deactivate'])->name('sites.deactivate');
        Route::post('/sites/{address}/reactivate', [CustomerSites::class, 'reactivate'])->name('sites.reactivate');
        Route::post('/sites/{address}/default', [CustomerSites::class, 'setDefault'])->name('sites.default');
        Route::get('/company', [CustomerCompany::class, 'index'])->name('company');
        Route::post('/company/request-create', [CustomerCompany::class, 'requestCreate'])->name('company.request-create');
        Route::post('/company/request-join', [CustomerCompany::class, 'requestJoin'])->name('company.request-join');
        Route::delete('/company/cancel', [CustomerCompany::class, 'cancelRequest'])->name('company.cancel');
        Route::delete('/company/leave', [CustomerCompany::class, 'leaveCompany'])->name('company.leave');
        Route::delete('/company/{company}/members/{user}/unlink', [CustomerCompany::class, 'unlinkMember'])->name('company.members.unlink');
        Route::post('/company/{company}/members/{user}/approve', [CustomerCompany::class, 'approveMember'])->name('company.members.approve');
        Route::delete('/company/{company}/members/{user}/reject', [CustomerCompany::class, 'rejectMember'])->name('company.members.reject');
    });

// ── Employee portal ───────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        Route::get('/dashboard', [EmployeeDashboard::class, 'index'])->name('dashboard');
        Route::get('/calendar', [EmployeeCalendar::class, 'index'])->name('calendar');
        Route::get('/work-orders/{workOrder}', [EmployeeWorkOrders::class, 'show'])->name('work-orders.show');
        Route::post('/work-orders/{workOrder}/complete', [EmployeeWorkOrders::class, 'complete'])->name('work-orders.complete');
        Route::post('/work-orders/{workOrder}/confirm-customer', [EmployeeWorkOrders::class, 'confirmCustomer'])->name('work-orders.confirm-customer');
        Route::post('/work-orders/{workOrder}/notes', [EmployeeWorkOrders::class, 'storeNote'])->name('work-orders.notes.store');
        Route::post('/work-orders/{workOrder}/time/arrive', [EmployeeWorkOrders::class, 'recordArrival'])->name('work-orders.time.arrive');
        Route::post('/work-orders/{workOrder}/time/depart', [EmployeeWorkOrders::class, 'recordDeparture'])->name('work-orders.time.depart');
        // Per-visit routes
        Route::get('/work-orders/{workOrder}/visits/{visit}', [EmployeeWorkOrders::class, 'showVisit'])->name('work-orders.visits.show');
        Route::post('/work-orders/{workOrder}/visits/{visit}/time/arrive', [EmployeeWorkOrders::class, 'recordVisitArrival'])->name('work-orders.visits.time.arrive');
        Route::post('/work-orders/{workOrder}/visits/{visit}/time/depart', [EmployeeWorkOrders::class, 'recordVisitDeparture'])->name('work-orders.visits.time.depart');
        Route::post('/work-orders/{workOrder}/visits/{visit}/complete', [EmployeeWorkOrders::class, 'completeVisit'])->name('work-orders.visits.complete');
        Route::get('/account', [EmployeeAccount::class, 'edit'])->name('account');
        Route::patch('/account', [EmployeeAccount::class, 'update'])->name('account.update');
    });

// ── Admin portal ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
        Route::get('/nav-counts', [AdminDashboard::class, 'navCounts'])->name('nav-counts');
        Route::resource('work-orders', AdminWorkOrders::class);
        Route::post('work-orders/{workOrder}/notes',        [AdminWorkOrders::class, 'storeNote'])->name('work-orders.notes.store');
        Route::post('work-orders/{workOrder}/status',       [AdminWorkOrders::class, 'updateStatus'])->name('work-orders.status');
        Route::post('work-orders/{workOrder}/urgency',      [AdminWorkOrders::class, 'updateUrgency'])->name('work-orders.urgency');
        Route::post('work-orders/{workOrder}/schedule',     [AdminWorkOrderSchedule::class, 'updateSchedule'])->name('work-orders.schedule');
        Route::get('work-orders/{workOrder}/tech-schedule', [AdminWorkOrderSchedule::class, 'techSchedule'])->name('work-orders.tech-schedule');
        Route::get('work-orders/{workOrder}/travel-time',   [AdminWorkOrderSchedule::class, 'travelTime'])->name('work-orders.travel-time');
        Route::post('work-orders/{workOrder}/request-confirmation', [AdminWorkOrderSchedule::class, 'requestConfirmation'])->name('work-orders.request-confirmation');
        Route::post('work-orders/{workOrder}/override-confirmation', [AdminWorkOrderSchedule::class, 'overrideConfirmation'])->name('work-orders.override-confirmation');
        Route::post('work-orders/{workOrder}/assign',       [AdminWorkOrderAssignment::class, 'assignEmployee'])->name('work-orders.assign');
        Route::delete('work-orders/{workOrder}/assign/{user}', [AdminWorkOrderAssignment::class, 'unassignEmployee'])->name('work-orders.unassign');
        Route::post('work-orders/{workOrder}/attachments',  [AdminWorkOrders::class, 'addAttachment'])->name('work-orders.attachments.add');
        Route::delete('work-orders/{workOrder}/attachments/{attachment}', [AdminWorkOrders::class, 'removeAttachment'])->name('work-orders.attachments.remove');
        Route::post('work-orders/{workOrder}/visits',                          [AdminWorkOrderSchedule::class, 'storeVisit'])->name('work-orders.visits.store');
        Route::patch('work-orders/{workOrder}/visits/{visit}',                 [AdminWorkOrderSchedule::class, 'updateVisit'])->name('work-orders.visits.update');
        Route::delete('work-orders/{workOrder}/visits/{visit}',                [AdminWorkOrderSchedule::class, 'destroyVisit'])->name('work-orders.visits.destroy');
        Route::post('work-orders/{workOrder}/visits/{visit}/request-confirm',  [AdminWorkOrderSchedule::class, 'requestVisitConfirmation'])->name('work-orders.visits.request-confirm');
        Route::post('work-orders/{workOrder}/visits/{visit}/admin-confirm',    [AdminWorkOrderSchedule::class, 'adminConfirmVisit'])->name('work-orders.visits.admin-confirm');
        Route::post('users/quick-company', [AdminUsers::class, 'quickStoreCompany'])->name('users.quick-company');
        Route::post('users/{user}/send-password-reset', [AdminUsers::class, 'sendPasswordReset'])->name('users.send-password-reset');
        Route::resource('users', AdminUsers::class);
        Route::resource('invoices', AdminInvoices::class);
        Route::post('invoices/{invoice}/status', [AdminInvoices::class, 'updateStatus'])->name('invoices.status');
        Route::get('invoices/{invoice}/print', [AdminInvoices::class, 'printView'])->name('invoices.print');
        Route::get('/calendar', [AdminCalendar::class, 'index'])->name('calendar');
        Route::get('/my-analytics', [AdminMyAnalytics::class, 'index'])->name('my-analytics');
        Route::get('inquiries/check-email', [AdminInquiries::class, 'checkEmail'])->name('inquiries.check-email');
        Route::get('inquiries', [AdminInquiries::class, 'index'])->name('inquiries.index');
        Route::get('inquiries/{inquiry}', [AdminInquiries::class, 'show'])->name('inquiries.show');
        Route::post('inquiries/{inquiry}/notes', [AdminInquiries::class, 'addNote'])->name('inquiries.notes.store');
        Route::post('inquiries/{inquiry}/status', [AdminInquiries::class, 'updateStatus'])->name('inquiries.status');
        Route::post('inquiries/{inquiry}/create-work-order', [AdminInquiries::class, 'createWorkOrder'])->name('inquiries.create-work-order');
        Route::get('/settings', [AdminSettings::class, 'index'])->name('settings');
        Route::post('/settings', [AdminSettings::class, 'update'])->name('settings.update');
        Route::post('/settings/queue-order', [AdminSettings::class, 'updateQueueOrder'])->name('settings.queue-order');
        Route::post('/settings/invoice-queue-order', [AdminSettings::class, 'updateInvoiceQueueOrder'])->name('settings.invoice-queue-order');
        Route::post('services/reorder', [AdminServices::class, 'reorder'])->name('services.reorder');
        Route::resource('services', AdminServices::class)->except(['destroy', 'show']);
        Route::post('services/{service}/toggle', [AdminServices::class, 'toggle'])->name('services.toggle');
        Route::get('device-catalog', [AdminDeviceCatalog::class, 'index'])->name('device-catalog.index');
        Route::post('device-catalog', [AdminDeviceCatalog::class, 'store'])->name('device-catalog.store');
        Route::patch('device-catalog/{device}', [AdminDeviceCatalog::class, 'update'])->name('device-catalog.update');
        Route::delete('device-catalog/{device}', [AdminDeviceCatalog::class, 'destroy'])->name('device-catalog.destroy');
        Route::post('device-catalog/reorder', [AdminDeviceCatalog::class, 'reorder'])->name('device-catalog.reorder');
        Route::get('/analytics/companies/search', [AdminCompanyAnalytics::class, 'search'])->name('analytics.companies.search');
        Route::get('/analytics/companies', [AdminCompanyAnalytics::class, 'index'])->name('analytics.companies');
        Route::get('/analytics/customers/search', [AdminAnalytics::class, 'search'])->name('analytics.customers.search');
        Route::get('/analytics/customers', [AdminAnalytics::class, 'customer'])->name('analytics.customers');

        // Reports (print-oriented). Index is the catalog; each report opens standalone.
        Route::get('/reports', [AdminReports::class, 'index'])->name('reports.index');
        Route::get('/reports/work-order-summary',      [AdminReports::class, 'workOrderSummary'])->name('reports.work-order-summary');
        Route::get('/reports/work-order-aging',        [AdminReports::class, 'workOrderAging'])->name('reports.work-order-aging');
        Route::get('/reports/invoice-register',        [AdminReports::class, 'invoiceRegister'])->name('reports.invoice-register');
        Route::get('/reports/accounts-receivable',     [AdminReports::class, 'accountsReceivable'])->name('reports.accounts-receivable');
        Route::get('/reports/technician-productivity', [AdminReports::class, 'technicianProductivity'])->name('reports.technician-productivity');
        Route::get('/reports/technician-time',         [AdminReports::class, 'technicianTime'])->name('reports.technician-time');
        Route::get('/reports/customer-statement',      [AdminReports::class, 'customerStatement'])->name('reports.customer-statement');
        Route::get('/reports/company-performance',     [AdminReports::class, 'companyPerformance'])->name('reports.company-performance');
        Route::get('/reports/service-usage',           [AdminReports::class, 'serviceUsage'])->name('reports.service-usage');
        Route::resource('companies', AdminCompanies::class);
        Route::post('companies/{company}/sites', [AdminCompanies::class, 'storeSite'])->name('companies.sites.store');
        Route::patch('companies/{company}/sites/{site}', [AdminCompanies::class, 'updateSite'])->name('companies.sites.update');
        Route::post('companies/{company}/sites/{site}/default', [AdminCompanies::class, 'setDefaultSite'])->name('companies.sites.default');
        Route::delete('companies/{company}/sites/{site}', [AdminCompanies::class, 'destroySite'])->name('companies.sites.destroy');
        Route::post('companies/{company}/members', [AdminCompanies::class, 'attachMember'])->name('companies.members.attach');
        Route::delete('companies/{company}/members/{user}', [AdminCompanies::class, 'detachMember'])->name('companies.members.detach');
        Route::post('companies/{company}/members/{user}/primary', [AdminCompanies::class, 'setPrimaryMember'])->name('companies.members.primary');
        Route::post('companies/{company}/approve', [AdminCompanies::class, 'approveCompany'])->name('companies.approve');
        Route::delete('companies/{company}/reject', [AdminCompanies::class, 'rejectCompany'])->name('companies.reject');
        Route::post('companies/{company}/members/{user}/approve', [AdminCompanies::class, 'approveMember'])->name('companies.members.approve');
        Route::delete('companies/{company}/members/{user}/reject', [AdminCompanies::class, 'rejectMember'])->name('companies.members.reject');
        Route::get('pending-customers', [AdminPendingCustomers::class, 'index'])->name('pending-customers.index');
        Route::post('pending-customers/{user}/approve', [AdminPendingCustomers::class, 'approve'])->name('pending-customers.approve');
        Route::post('pending-customers/{user}/create-company', [AdminPendingCustomers::class, 'createCompany'])->name('pending-customers.create-company');
        Route::delete('pending-customers/{user}', [AdminPendingCustomers::class, 'reject'])->name('pending-customers.reject');
    });
