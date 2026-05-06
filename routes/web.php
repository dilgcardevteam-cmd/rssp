<?php

use Illuminate\Support\Facades\{Route, Auth};
use App\Http\Controllers\Forms;
use App\Http\Controllers\Auth\{
    RegisterController,
    LoginController,
    ForgotPasswordController,
    GoogleController,
    AdminAuthController
};
use App\Http\Controllers\{
    activityLogController,
    AdminEmailLogController,
    VacancyController,
    JobVacancyController,
    ExamController,
    ShowApplicantsProfile,
    AdminController,
    GeminiChatController,
    WorkExpSheetController,
    ExportController,
    ImportController,
    ApplicantOnboardingController,
    SignatoryController,
};
use App\Http\Controllers\Forms\ExportPDSController;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\ViewerAccess;
use App\Http\Middleware\BlockIfAdmin;
use App\Http\Middleware\EnsureSuperadmin;
use App\Http\Middleware\ApplicantsAccess;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BackupRestoreController;
use App\Http\Controllers\PsgcController;
use App\Http\Controllers\VacancyTitleController;
use App\Http\Controllers\PositionUtilityController;
use App\Http\Controllers\EligibilityPresetController;
use App\Http\Controllers\CoursePresetController;
use App\Http\Controllers\ManualController;
use App\Support\ApplicantOnboarding;

use App\Events\PackageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

// ==================================================================================================
// HOME ROUTE - Smart redirect based on authentication
// ==================================================================================================
Route::get('/', function (\Illuminate\Http\Request $request) {
    $baseQuery = \App\Models\JobVacancy::query()
        ->whereIn('status', ['OPEN', 'ASSESSMENT']);

    $allCount = (clone $baseQuery)->where('status', 'OPEN')->count();
    $plantillaCount = (clone $baseQuery)->where('status', 'OPEN')->whereIn('vacancy_type', ['plantilla', 'permanent'])->count();
    $cosCount = (clone $baseQuery)->where('status', 'OPEN')->whereIn('vacancy_type', ['cos', 'contract of service', 'contract'])->count();

    $vacancies = $baseQuery->with(['applications.personalInformation'])
        ->orderByRaw("
            CASE 
                WHEN status = 'OPEN' THEN 1
                WHEN status = 'ASSESSMENT' THEN 2
                ELSE 3 
            END
        ")
        ->orderByDesc('created_at')
        ->paginate(100);

    if (Auth::guard('admin')->check()) {
        $user = Auth::guard('admin')->user();
        $approvalStatus = (string) ($user->approval_status ?? 'approved');

        if ($approvalStatus === 'pending') {
            return redirect()->route('admin.pending.dashboard');
        }

        if ($approvalStatus === 'declined') {
            Auth::guard('admin')->logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Your account request was declined. Please contact superadmin.',
            ]);
        }

        return match ($user->role ?? null) {
            'viewer' => redirect()->route('viewer'),
            'hr_division' => redirect()->route('dashboard_admin'),
            default => redirect()->route('dashboard_admin'),
        };
    } elseif (Auth::check()) {
        return redirect()->route('dashboard_user');
    } else {
        return view('public.landing', [
            'vacancies' => $vacancies,
            'allCount' => $allCount,
            'plantillaCount' => $plantillaCount,
            'cosCount' => $cosCount
        ]);
    }
})->name('home');

// ==================================================================================================
// TEST PREVIEW ROUTES (Delete after use)
// ==================================================================================================
if (app()->environment(['local', 'testing'])) {
    Route::get('/preview/exam/lobby', function () {
        return view('exam_user.exam_lobby', ['vacancy_id' => 'PREVIEW-123']);
    })->name('preview.exam.lobby');

    Route::get('/preview/exam/questions', function () {
        // Mock exam items
        $examItems = [
            (object) [
                'id' => 1,
                'question' => 'What is the capital of the Philippines?',
                'is_essay' => 0,
                'choices' => ['Manila', 'Cebu', 'Davao', 'Baguio']
            ],
            (object) [
                'id' => 2,
                'question' => 'Explain the importance of public service.',
                'is_essay' => 1,
                'choices' => null
            ]
        ];
        return view('exam_user.exam_question_page', [
            'vacancy_id' => 'PREVIEW-123',
            'examItems' => $examItems
        ]);
    })->name('preview.exam.questions');

    Route::get('/preview/exam/thankyou', function () {
        return view('exam_user.exam_thankyou', ['vacancy_id' => 'PREVIEW-123']);
    })->name('preview.exam.thankyou');
}

// In routes/api.php or routes/web.php
Route::get('/api/examination-dates', [ExamController::class, 'getExaminationDates']);

// ==================================================================================================
// PUBLIC ROUTES (No authentication required)
// ==================================================================================================

// ==================================================================================================
// Registration, OTP, Login and Logout
// ==================================================================================================
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::get('/otp', [RegisterController::class, 'OTPForm'])->name('otp');
Route::post('/otp', [RegisterController::class, 'OTPCheck'])->name('otp_check');

Route::get('/exam/confirm/{token}', [ExamController::class, 'confirmNotification'])->name('exam.confirm_notification');
Route::get('/exam/{vacancy_id}/attendance', [ExamController::class, 'attendancePrompt'])->name('exam.attendance.prompt');
Route::post('/otp/resend', [RegisterController::class, 'resendOTP'])->name('otp_resend');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login')->middleware('throttle:5,1');
Route::get('/csrf-token', function () {
    return response()
        ->json(['token' => csrf_token()])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
})->name('csrf.token');

// ==================================================================================================
// Reset Password
// ==================================================================================================
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('forgot.password.form');
Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('forgot.password.send.otp');
Route::get('/forgot-password/otp', [ForgotPasswordController::class, 'showOtpForm'])->name('forgot.password.otp.form');
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('forgot.password.verify.otp');
Route::post('/forgot-password/otp/resend', [ForgotPasswordController::class, 'resendOtp'])->name('forgot.password.otp.resend');
Route::get('/forgot-password/reset/{email}', [ForgotPasswordController::class, 'showResetForm'])->name('forgot.password.reset.form');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('forgot.password.reset');

// removed duplicate root route

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard_user', [JobVacancyController::class, 'getOpenVacanciesForDashboard'])->name('dashboard_legacy')->middleware(\App\Http\Middleware\RunDailyTask::class);
});

// ==================================================================================================
// USER LOGOUT
// ==================================================================================================
Route::post('/logout', function (Request $request) {
    $request->session()->forget([
        'form',
        'data_learning',
        'data_voluntary',
        'data_otherInfo',
        'vacancy_doc_uploads',
        'pds_form_owner',
        'redirect_after_login',
        'pending_registration',
    ]);
    Auth::guard('web')->logout();
    Auth::guard('admin')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login')
        ->header('Clear-Site-Data', '"cache","storage"');
})->name('logout');

// ==================================================================================================
// PDS ROUTES
// ==================================================================================================
/*
Route::get('/pds/c1', [Forms\PDSController::class, 'c1DisplayForm'])->name('display_c1')->middleware('auth');
Route::post('/pds/submit_c1', [Forms\PDSController::class, 'c1UpdateFormSession'])->name('submit_c1')->middleware('auth');

Route::get('/pds/c2', [Forms\PDSController::class, 'c2DisplayForm'])->name('display_c2')->middleware('auth');
Route::post('/pds/submit_c2', [Forms\PDSController::class, 'c2UpdateFormSession'])->name('submit_c2')->middleware('auth');
Route::delete('/c2/d/{target_row}/{id}', [Forms\PDSController::class, 'c2DeleteRow']);

Route::get('/pds/c5', [Forms\PDSController::class, 'c5DisplayForm'])->name('display_c5')->middleware('auth');
Route::post('/pds/finalize', [Forms\PDSController::class, 'finalizePDS'])->name('finalize_pds')->middleware('auth');

Route::get('/pds/submit', [Forms\PDSController::class, 'showSubmittedForm'])->name('display_final_pds')->middleware('auth');

// ==================================================================================================
// PDS ROUTES
// GOOGLE AUTH ROUTES
// ==================================================================================================

// Exporting PDF (WIP)
Route::get('/export-pds/{id}', [Forms\ExportPDSController::class, 'exportPDS'])->name('export.pds');

// ---------------------------------------
// PDS C3 ROUTES
// ---------------------------------------
Route::post('/c3_submit', [Forms\PDSController::class, 'c3SubmitForm'])->name('c3_submit');
Route::get('/c3_submit', [Forms\PDSController::class, 'c3ShowForm'])->name('c3_show');

// ---------------------------------------
// PDS C4 ROUTES
// ---------------------------------------
Route::post('/c4_submit', [Forms\PDSController::class, 'c4SubmitForm'])->name('c4_submit');
Route::get('/c4_submit', [Forms\PDSController::class, 'c4ShowForm'])->name('c4_show');


// Function call below is reimplemented. Do not uncomment. 💩
// Route::post('/c5_submit', [Forms\C5controller::class, 'c5SubmitForm'])->name('c5_submit');

Route::view('/pds_update', 'pds_update.pds_update')->name('pds_update');
Route::view('/c2_update', 'pds_update.c2_update')->name('c2_update');
Route::view('/c3_update', 'pds_update.c3_update')->name('c3_update');
Route::view('/c4_update', 'pds_update.c4_update')->name('c4_update');
Route::view('/c5_update', 'pds_update.c5_update')->name('c5_update');
Route::view('/submit_update', 'pds_update.submit_update')->name('submit_update');


// ==================================================================================================
// USER ROUTES
// ADMIN AUTH ROUTES (accessible when not authenticated as admin)
// ==================================================================================================
/*
Route::view('/dashboard', 'dashboard_user.dashboard_user')->name('dashboard_user');
Route::get('/job-vacancies', [JobVacancyController::class, 'jobVacancy'])->name('job_vacancy');
Route::get('/{id}/job_description', [JobVacancyController::class, 'jobDescription'])->name('job_description');
Route::get('/job-vacancies/filter', [JobVacancyController::class, 'filterVacancy'])->name('vacancies.filter');
Route::view('/my_applications', 'dashboard_user.my_applications')->name('my_applications');
Route::view('/application_status/{}', 'dashboard_user.application_status')->name('application_status');
Route::view('/about', 'dashboard_user.about')->name('about');
Route::get('/pds_print', fn() => view('dashboard_user.pds_print'))->name('pds_print');
*/

// ==================================================================================================
// GOOGLE AUTH ROUTES
// ==================================================================================================
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

//Route::middleware(['auth', BlockIfAdmin::class])   // 👈 here!
//->group(function () {

Route::get('/dashboard', [JobVacancyController::class, 'getOpenVacanciesForDashboard'])->name('dashboard_user');

Route::view('/about', 'dashboard_user.about')
    ->name('about');

Route::middleware(['auth'])->group(function () {
    Route::get('/my_applications', [JobVacancyController::class, 'myApplications'])->name('my_applications');
    Route::get('/my-applications/sort', [JobVacancyController::class, 'sortMyApplications'])->name('my_applications.sort');
    // User application status get route
    Route::get('/application_status/{user}/{vacancy}', [JobVacancyController::class, 'applicationStatus'])->name('application_status');
    Route::post('/application_status/{user}/{vacancy}/cancel', [JobVacancyController::class, 'cancelApplication'])->name('application_status.cancel');
    Route::get('/application_status/{user}/{vacancy}/documents', [JobVacancyController::class, 'getUpdatedDocumentsUser'])->name('application_status.get_documents');
});

Route::get('/job-vacancies', [JobVacancyController::class, 'jobVacancy'])
    ->name('job_vacancy');

Route::get('/{id}/job_description', [JobVacancyController::class, 'jobDescription'])
    ->name('job_description');

Route::get('/job-vacancies/filter', [JobVacancyController::class, 'filterVacancy'])
    ->name('vacancies.filter');

Route::get('/pds_print', fn() => view('dashboard_user.pds_print'))
    ->name('pds_print');

//Route::middleware('guest:admin')->group(function () {
//    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
//    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
//});

// ==================================================================================================
// PDS ROUTES
// ADMIN LOGOUT (accessible when authenticated as admin)
// ==================================================================================================


//});
Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/admin/pending-dashboard', [AdminAuthController::class, 'pendingDashboard'])->name('admin.pending.dashboard');
    Route::get('/admin/manual', [ManualController::class, 'adminManual'])->name('manual.admin');
});

// PDS and WES Export
Route::middleware('auth:web')->group(function () {
    Route::get('/export-pds', [Forms\ExportPDSController::class, 'exportPDS'])->name('export.pds');
    Route::get('/export-wes', [Forms\ExportWESController::class, 'exportWES'])->name('export.wes');
    Route::view('/pds-preview', 'pds.preview')->name('pds.preview');
    Route::get('/wes-preview', [Forms\ExportWESController::class, 'previewWES'])->name('wes.preview');
});

// ==================================================================================================
// ADMIN AUTH ROUTES
// ==================================================================================================
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit')->middleware('throttle:5,1');
Route::post('/admin/register', [AdminAuthController::class, 'register'])->name('admin.register.submit')->middleware('throttle:5,1');
// USER ROUTES (blocked if admin is logged in)
// ==================================================================================================
Route::middleware(['auth', BlockIfAdmin::class])->group(function () {
    Route::get('/onboarding/applicant', [ApplicantOnboardingController::class, 'show'])->name('applicant.onboarding.show');
    Route::post('/onboarding/applicant', [ApplicantOnboardingController::class, 'store'])->name('applicant.onboarding.store');

    Route::get('/dashboard', [JobVacancyController::class, 'getOpenVacanciesForDashboard'])->name('dashboard_user')->middleware(\App\Http\Middleware\RunDailyTask::class);

    Route::get('/about', fn() => view('dashboard_user.about'))->name('about');

    Route::get('/my_applications', [JobVacancyController::class, 'myApplications'])->name('my_applications');
    Route::get('/manual', [ManualController::class, 'applicantManual'])->name('manual.user');

    Route::get('/work_experience', [WorkExpSheetController::class, 'show'])->name('work_experience');
    Route::post('/work_experience', [WorkExpSheetController::class, 'store'])->name('work_experience_store');

    Route::get('/application_status/{user}/{vacancy}', [JobVacancyController::class, 'applicationStatus'])->name('application_status');
    Route::post('/application_status/{user}/{vacancy}/cancel', [JobVacancyController::class, 'cancelApplication'])->name('application_status.cancel');

    Route::get('/job-vacancies', [JobVacancyController::class, 'jobVacancy'])->name('job_vacancy');

    Route::get('/{id}/job_description', [JobVacancyController::class, 'jobDescription'])->name('job_description');

    Route::get('/job-vacancies/filter', [JobVacancyController::class, 'filterVacancy'])->name('vacancies.filter');

    Route::get('/pds_print', fn() => view('dashboard_user.pds_print'))->name('pds_print');

    // ==================================================================================================
    // PDS ROUTES
    // ==================================================================================================
    Route::get('/psgc/provinces', [PsgcController::class, 'provinces'])->name('pds.psgc.provinces');
    Route::get('/psgc/provinces/{provinceCode}/cities-municipalities', [PsgcController::class, 'citiesMunicipalities'])
        ->where('provinceCode', '[0-9]{10}')
        ->name('pds.psgc.cities_municipalities');
    Route::get('/psgc/cities-municipalities/{cityCode}/barangays', [PsgcController::class, 'barangays'])
        ->where('cityCode', '[0-9]{10}')
        ->name('pds.psgc.barangays');
    Route::get('/psgc/cities-municipalities/{cityCode}', [PsgcController::class, 'cityMunicipality'])
        ->where('cityCode', '[0-9]{10}')
        ->name('pds.psgc.city_municipality');

    Route::get('/pds/c1', [Forms\PDSController::class, 'c1DisplayForm'])->name('display_c1');
    Route::post('/pds/submit_c1/{go_to}', [Forms\PDSController::class, 'c1UpdateFormSession'])->name('submit_c1');
    Route::post('/pds/import-c1-excel', [Forms\PDSController::class, 'importC1Excel'])->name('pds.import_c1_excel');
    Route::get('/pds/export-annex-h1-excel', [Forms\PDSController::class, 'exportAnnexH1Excel'])->name('pds.export_annex_h1_excel');
    Route::post('/pds/autosave/{section}', [Forms\PDSController::class, 'autosaveDraft'])->name('pds.autosave');
    Route::get('/pds/utilities/eligibilities/list', [EligibilityPresetController::class, 'listJson'])->name('pds.eligibilities.list');
    Route::get('/pds/utilities/programs/list', [CoursePresetController::class, 'publicListJson'])->name('pds.programs.list');

    Route::get('/pds/c2', [Forms\PDSController::class, 'c2DisplayForm'])->name('display_c2');
    Route::post('/pds/submit_c2/{go_to}', [Forms\PDSController::class, 'c2UpdateFormSession'])->name('submit_c2');
    Route::delete('/c2/d/{target_row}/{id}', [Forms\PDSController::class, 'c2DeleteRow']);

    Route::get('/pds/c3', [Forms\PDSController::class, 'c3ShowForm'])->name('display_c3');
    Route::post('/pds/submit_c3/{go_to}', [Forms\PDSController::class, 'c3SubmitForm'])->name('submit_c3');

    Route::get('/pds/c4', [Forms\PDSController::class, 'c4ShowForm'])->name('display_c4');
    Route::post('/pds/submit_c4/{go_to}', [Forms\PDSController::class, 'c4SubmitForm'])->name('submit_c4');

    Route::get('/pds/wes', [WorkExpSheetController::class, 'show'])->name('display_wes');

    Route::get('/pds/c5', [Forms\PDSController::class, 'c5DisplayForm'])->name('display_c5');
    Route::post('/pds/finalize/{go_to}', [Forms\PDSController::class, 'finalizePDS'])->name('finalize_pds');
    Route::post('/application-status/{user_id}/{vacancy_id}/upload', [Forms\PDSController::class, 'uploadApplicationDocuments'])->name('application_status.upload');
    Route::post('/exam/{vacancy_id}/attendance', [ExamController::class, 'submitAttendanceResponse'])->name('exam.attendance.respond');

    Route::get('/pds/submit', [Forms\PDSController::class, 'showSubmittedForm'])->name('display_final_pds');

    // Exporting PDF (WIP)
    // Route::get('/export-pds/{id}', [Forms\ExportPDSController::class, 'exportPDS'])->name('export.pds');

    // PDS Update Routes
    Route::get('/pds_update', [Forms\PDSController::class, 'c1DisplayUpdateForm'])->name('pds_update')->middleware('auth');
    Route::get('/c2_update', [Forms\PDSController::class, 'c2DisplayUpdateForm'])->name('c2_update')->middleware('auth');
    Route::get('/c3_update', [Forms\PDSController::class, 'c3DisplayUpdateForm'])->name('c3_update')->middleware('auth');
    //Sample
    Route::view('/c4_sample', 'pds.c4-sample')->name('c4-sample');
    Route::get('/c4_update', [Forms\PDSController::class, 'c4DisplayUpdateForm'])->name('c4_update')->middleware('auth');
    Route::get('/c5_update', [Forms\PDSController::class, 'c5DisplayUpdateForm'])->name('c5_update')->middleware('auth');
    //Route::view('/submit_update', 'pds_update.submit_update')->name('submit_update');

    // APPLICATION ROUTE
    Route::post('/initial-assessment/{vacancy_id}', [JobVacancyController::class, 'submitInitialAssessment'])->name('initial_assessment.submit');
    Route::post('/apply/{vacancy_id}', [JobVacancyController::class, 'apply'])->name('application.store');

    // =========================
    // Notifications
    // =========================


    // =========================
    // Profile
    // =========================
    Route::get('/account-settings', [ProfileController::class, 'accountSettings'])->name('account.settings');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::post('/profile/document-gallery', [ProfileController::class, 'storeGalleryDocument'])->name('profile.document_gallery.store');
    Route::post('/profile/document-gallery/{item}/replace', [ProfileController::class, 'replaceGalleryDocument'])->name('profile.document_gallery.replace');
    Route::get('/profile/document-gallery/{item}/preview', [ProfileController::class, 'previewGalleryDocument'])->name('profile.document_gallery.preview');
    Route::get('/profile/document-gallery/{item}/download', [ProfileController::class, 'downloadGalleryDocument'])->name('profile.document_gallery.download');
    Route::delete('/profile/document-gallery/{item}', [ProfileController::class, 'deleteGalleryDocument'])->name('profile.document_gallery.delete');
    Route::get('/profile/password', fn() => redirect()->route('account.settings'))->name('profile.password.form');
    Route::post('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::post('/profile/request-account-deletion', [ProfileController::class, 'requestAccountDeletion'])->name('profile.request_account_deletion');
});

// ==================================================================================================
// ADMIN PROTECTED ROUTES
// ADMIN ROUTES (only accessible to authenticated admins with admin role)
// ==================================================================================================
//Route::middleware(RedirectIfNotAdmin::class)->group(function () {

Route::middleware([RedirectIfNotAdmin::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('home_admin');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('dashboard_admin')->middleware(\App\Http\Middleware\RunDailyTask::class);
    Route::get('/admin/account-settings', [AdminController::class, 'accountSettings'])->name('admin.account.settings');
    Route::put('/admin/account-settings', [AdminController::class, 'updateAccountSettings'])->name('admin.account.settings.update');
    Route::put('/admin/account-settings/password', [AdminController::class, 'updateOwnPassword'])->name('admin.account.password.update');
    Route::middleware([EnsureSuperadmin::class])->group(function () {
        Route::get('/admin/admin_account_management', [AdminController::class, 'manage'])->name('admin_account_management');
        Route::get('/admin/applicant-records', [ShowApplicantsProfile::class, 'applicantRecords'])->name('admin.applicant_records.index');
        Route::get('/admin/applicant-records/{user}', [ShowApplicantsProfile::class, 'showApplicantRecord'])->name('admin.applicant_records.show');
        Route::get('/admin/applicant-records/{user}/pds', [ExportPDSController::class, 'exportPDS'])->name('admin.applicant_records.pds');
        Route::post('/admin/applicant-records/{user}/schedule-deletion', [ShowApplicantsProfile::class, 'scheduleApplicantRecordDeletion'])->name('admin.applicant_records.schedule');
        Route::post('/admin/applicant-records/{user}/cancel-deletion', [ShowApplicantsProfile::class, 'cancelApplicantRecordDeletion'])->name('admin.applicant_records.cancel');
        Route::delete('/admin/applicant-records/{user}', [ShowApplicantsProfile::class, 'destroyApplicantRecord'])->name('admin.applicant_records.destroy');
        Route::post('/admin/store', [AdminController::class, 'store'])->name('admin.store');
        Route::post('/admin/{id}/deactivate', [AdminController::class, 'deactivate'])->name('admin.deactivate');
        Route::post('/admin/{id}/activate', [AdminController::class, 'activate'])->name('admin.activate');
        Route::post('/admin/{id}/approve', [AdminController::class, 'approve'])->name('admin.approve');
        Route::post('/admin/{id}/decline', [AdminController::class, 'decline'])->name('admin.decline');
        Route::put('/admin/{id}/update', [AdminController::class, 'update'])->name('admin.update');
        Route::post('/admin/{id}/hr-vacancy-access', [AdminController::class, 'updateHrDivisionVacancyAccess'])->name('admin.hr_vacancy_access.update');
        Route::get('/admin/search', [AdminController::class, 'search'])->name('admin.search');
    });

    Route::get('/admin/vacancies_management/add/plantilla', function (\Illuminate\Http\Request $request) {
        $admin = Auth::guard('admin')->user();
        if (($admin->role ?? null) === 'hr_division') {
            return redirect()->route('vacancies_management')
                ->with('error', 'Access denied. HR Division can only add COS vacancies.');
        }

        $signatories = \App\Models\Signatory::query()->orderBy('id')->get();
        $templateVacancy = null;
        $reuseTitleId = (int) $request->query('reuse_title', 0);
        if ($reuseTitleId > 0 && \Illuminate\Support\Facades\Schema::hasTable('vacancy_titles')) {
            $templateVacancy = \App\Models\VacancyTitle::query()->find($reuseTitleId);
        }
        $reuseVacancyId = trim((string) $request->query('reuse', ''));
        if (!$templateVacancy && $reuseVacancyId !== '') {
            $templateVacancy = \App\Models\JobVacancy::where('vacancy_id', $reuseVacancyId)->first();
        }
        $positionMode = true;
        return view('admin.vacancy_add_plantilla', compact('signatories', 'templateVacancy', 'positionMode'));
    })->name('addplantilla');
    Route::get('/admin/vacancies_management/add/cos', function (\Illuminate\Http\Request $request) {
        $admin = Auth::guard('admin')->user();
        $signatories = \App\Models\Signatory::query()->orderBy('id')->get();
        $templateVacancy = null;
        $reuseTitleId = (int) $request->query('reuse_title', 0);
        if ($reuseTitleId > 0 && \Illuminate\Support\Facades\Schema::hasTable('vacancy_titles')) {
            $templateTitle = \App\Models\VacancyTitle::query()->find($reuseTitleId);
            if ($templateTitle && strcasecmp((string) $templateTitle->vacancy_type, 'COS') === 0) {
                $templateVacancy = $templateTitle;
            }
        }
        $reuseVacancyId = trim((string) $request->query('reuse', ''));
        if (!$templateVacancy && $reuseVacancyId !== '') {
            $templateQuery = \App\Models\JobVacancy::query()
                ->where('vacancy_id', $reuseVacancyId)
                ->whereRaw('UPPER(vacancy_type) = ?', ['COS']);

            if (($admin->role ?? null) === 'hr_division') {
                $grantedVacancyIds = \Illuminate\Support\Facades\Schema::hasTable('admin_vacancy_accesses')
                    ? \App\Models\AdminVacancyAccess::query()
                        ->where('admin_id', $admin->id)
                        ->pluck('vacancy_id')
                        ->map(fn($value) => trim((string) $value))
                        ->filter(fn($value) => $value !== '')
                        ->unique()
                        ->values()
                        ->all()
                    : [];

                $supportsCreatorColumn = \Illuminate\Support\Facades\Schema::hasColumn('job_vacancies', 'created_by_admin_id');

                $templateQuery->where(function ($query) use ($admin, $grantedVacancyIds, $supportsCreatorColumn) {
                    $hasScope = false;

                    if ($supportsCreatorColumn) {
                        $query->where('created_by_admin_id', $admin->id);
                        $hasScope = true;
                    }

                    if (!empty($grantedVacancyIds)) {
                        if ($hasScope) {
                            $query->orWhereIn('vacancy_id', $grantedVacancyIds);
                        } else {
                            $query->whereIn('vacancy_id', $grantedVacancyIds);
                        }
                        $hasScope = true;
                    }

                    if (!$hasScope) {
                        $query->whereRaw('1 = 0');
                    }
                });
            }

            $templateVacancy = $templateQuery->first();

            if (($admin->role ?? null) === 'hr_division' && !$templateVacancy) {
                return redirect()->route('vacancies_management')
                    ->with('error', 'Access denied. You can only reuse your own or assigned COS vacancies.');
            }
        }
        $positionMode = true;
        return view('admin.vacancy_add_cos', compact('signatories', 'templateVacancy', 'positionMode'));
    })->name('addcos');

    Route::get('/admin/vacancies_management/add_vacancy/plantilla', function (\Illuminate\Http\Request $request) {
        $admin = Auth::guard('admin')->user();
        if (($admin->role ?? null) === 'hr_division') {
            return redirect()->route('vacancies_management')
                ->with('error', 'Access denied. HR Division can only add COS vacancies.');
        }

        $signatories = \App\Models\Signatory::query()->orderBy('id')->get();
        $templateVacancy = null;
        $reuseVacancyId = trim((string) $request->query('reuse', ''));
        if ($reuseVacancyId !== '') {
            $templateVacancy = \App\Models\JobVacancy::where('vacancy_id', $reuseVacancyId)->first();
        }
        $positionMode = false;
        return view('admin.vacancy_add_plantilla', compact('signatories', 'templateVacancy', 'positionMode'));
    })->name('vacancies.addplantilla');

    Route::get('/admin/vacancies_management/add_vacancy/cos', function (\Illuminate\Http\Request $request) {
        $admin = Auth::guard('admin')->user();
        $signatories = \App\Models\Signatory::query()->orderBy('id')->get();
        $templateVacancy = null;
        $reuseVacancyId = trim((string) $request->query('reuse', ''));
        if ($reuseVacancyId !== '') {
            $templateQuery = \App\Models\JobVacancy::query()
                ->where('vacancy_id', $reuseVacancyId)
                ->whereRaw('UPPER(vacancy_type) = ?', ['COS']);

            if (($admin->role ?? null) === 'hr_division') {
                $grantedVacancyIds = \Illuminate\Support\Facades\Schema::hasTable('admin_vacancy_accesses')
                    ? \App\Models\AdminVacancyAccess::query()
                        ->where('admin_id', $admin->id)
                        ->pluck('vacancy_id')
                        ->map(fn($value) => trim((string) $value))
                        ->filter(fn($value) => $value !== '')
                        ->unique()
                        ->values()
                        ->all()
                    : [];

                $supportsCreatorColumn = \Illuminate\Support\Facades\Schema::hasColumn('job_vacancies', 'created_by_admin_id');

                $templateQuery->where(function ($query) use ($admin, $grantedVacancyIds, $supportsCreatorColumn) {
                    $hasScope = false;

                    if ($supportsCreatorColumn) {
                        $query->where('created_by_admin_id', $admin->id);
                        $hasScope = true;
                    }

                    if (!empty($grantedVacancyIds)) {
                        if ($hasScope) {
                            $query->orWhereIn('vacancy_id', $grantedVacancyIds);
                        } else {
                            $query->whereIn('vacancy_id', $grantedVacancyIds);
                        }
                        $hasScope = true;
                    }

                    if (!$hasScope) {
                        $query->whereRaw('1 = 0');
                    }
                });
            }

            $templateVacancy = $templateQuery->first();

            if (($admin->role ?? null) === 'hr_division' && !$templateVacancy) {
                return redirect()->route('vacancies_management')
                    ->with('error', 'Access denied. You can only reuse your own or assigned COS vacancies.');
            }
        }
        $positionMode = false;
        return view('admin.vacancy_add_cos', compact('signatories', 'templateVacancy', 'positionMode'));
    })->name('vacancies.addcos');

    Route::post('/admin/vacancies_management/add/cos/store', [JobVacancyController::class, 'storeVacancy'])->name('vacancies.store');
    // Route::put('/admin/vacancies_management/cos/{vacancy}/update', [JobVacancyController::class, 'update'])->name('vacancy.update');
    //Route::get('/admin/vacancies_management/add', fn() => view('admin.vacancy_add'))->name('add_job_vacancy_form');
    //Route::post('/admin/vacancies_management/add', [JobVacancyController::class, 'storeVacancy'])->name('add_job_vacancy');
    Route::post('/admin/vacancies_management/add/plantilla/store', [JobVacancyController::class, 'storeVacancy'])->name('plantilla.store');
    Route::put('/admin/vacancies/plantilla/{vacancy_id}/edit', [JobVacancyController::class, 'update'])->name('plantilla.update');
    Route::get('/admin/vacancies_management', [JobVacancyController::class, 'jobVacancyManagement'])->name('vacancies_management');
    Route::get('/admin/vacancies_management/filter', [JobVacancyController::class, 'adminFilterVacancy'])->name('admin.vacancies.filter');
    Route::get('/admin/vacancies/{vacancy_id}/edit', [JobVacancyController::class, 'edit'])->name('vacancies.edit');
    Route::put('/admin/vacancies/cos/{vacancy_id}/edit', [JobVacancyController::class, 'update'])->name('vacancies.update');
    Route::delete('/admin/vacancies/{vacancy_id}/delete', [JobVacancyController::class, 'delete'])->name('vacancies.delete');

    Route::get("/admin/activity_log", [activityLogController::class, 'view'])->name('admin_activity_log');
    Route::get('/admin/activity-log/data', [activityLogController::class, 'fetch'])->name('admin.activity_log.fetch');

    Route::get('/admin/email-logs/{emailLog}', [AdminEmailLogController::class, 'show'])->name('admin.email_logs.show');
    Route::get('/admin/email-logs/{emailLog}/html', [AdminEmailLogController::class, 'html'])->name('admin.email_logs.html');

    // ==================================================================================================
    // REPORT ROUTES
    // ==================================================================================================
    Route::get('/admin/utilities/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/utilities/reports/data', [App\Http\Controllers\ReportController::class, 'getData'])->name('admin.reports.data');
    Route::get('/admin/utilities/reports/export', [App\Http\Controllers\ReportController::class, 'export'])->name('admin.reports.export');
    Route::get('/admin/utilities/positions', [PositionUtilityController::class, 'index'])->name('admin.positions.index');
    Route::get('/admin/utilities/positions/list', [PositionUtilityController::class, 'listJson'])->name('admin.positions.list');
    Route::get('/admin/utilities/courses', [CoursePresetController::class, 'index'])->name('admin.courses.index');
    Route::post('/admin/utilities/courses', [CoursePresetController::class, 'store'])->name('admin.courses.store');
    Route::put('/admin/utilities/courses/{id}', [CoursePresetController::class, 'update'])->name('admin.courses.update');
    Route::delete('/admin/utilities/courses/{id}', [CoursePresetController::class, 'destroy'])->name('admin.courses.destroy');
    Route::get('/admin/utilities/courses/list', [CoursePresetController::class, 'listJson'])->name('admin.courses.list');
    Route::post('/admin/utilities/courses/suggestions/{id}/approve', [CoursePresetController::class, 'approveSuggestion'])->name('admin.courses.suggestions.approve');
    Route::post('/admin/utilities/courses/suggestions/{id}/decline', [CoursePresetController::class, 'declineSuggestion'])->name('admin.courses.suggestions.decline');
    Route::middleware([EnsureSuperadmin::class])->group(function () {
        Route::get('/admin/utilities/backup-restore', [BackupRestoreController::class, 'index'])->name('admin.backup.index');
        Route::post('/admin/utilities/backup-restore/backup', [BackupRestoreController::class, 'backup'])->name('admin.backup.run');
        Route::post('/admin/utilities/backup-restore/restore', [BackupRestoreController::class, 'restore'])->name('admin.backup.restore');
        Route::post('/admin/utilities/backup-restore/schedule', [BackupRestoreController::class, 'saveSchedule'])->name('admin.backup.schedule');
        Route::post('/admin/utilities/backup-restore/test-now', [BackupRestoreController::class, 'sendTestBackupNow'])->name('admin.backup.test');
    });
    Route::get('/admin/utilities/vacancy-titles/list', [VacancyTitleController::class, 'listJson'])->name('admin.vacancy_titles.list');
    Route::get('/admin/utilities/eligibilities/list', [EligibilityPresetController::class, 'listJson'])->name('admin.eligibilities.list');

    // ==================================================================================================
    // SIGNATORY ROUTES
    // ==================================================================================================
    Route::resource('/admin/signatories', SignatoryController::class)->names([
        'index' => 'signatories.index',
        'create' => 'signatories.create',
        'store' => 'signatories.store',
        'show' => 'signatories.show',
        'edit' => 'signatories.edit',
        'update' => 'signatories.update',
        'destroy' => 'signatories.destroy',
    ]);

});

// Routes accessible by both admin and viewer
// ==================================================================================================
// VIEWER ROUTES (accessible to both admin and viewer roles)
// ==================================================================================================
Route::middleware([ViewerAccess::class, 'admin.ability:admin.exam.monitor'])->group(function () {
    // Viewer routes
    Route::get('/viewer', fn() => redirect()->route('admin_exam_management'))->name('viewer');
    Route::get('/viewer/exam_management', fn() => redirect()->route('admin_exam_management'))->name('viewer.exam_management');
    Route::get('/viewer/exam_management/view_exam', fn() => redirect()->route('admin_exam_management'))->name('viewer.view_exam');


    // Exam management routes (accessible by both admin and viewer)
    Route::get('/admin/exam_management', [ExamController::class, 'examManagement'])->name('admin_exam_management');
    Route::get('/admin/exam_management/{vacancy_id}/manage', [ExamController::class, 'manageExam'])->name('admin.manage_exam');
    Route::get('/admin/exam_management/{vacancy_id}/lobby-data', [ExamController::class, 'getLobbyData'])->name('admin.exam.lobby_data');

    // Exam answer monitoring (admin + viewer)
    Route::get('/admin/exam_management/{vacancy_id}/view_exam/{user_id}', [ExamController::class, 'viewExam'])->name('admin.view_exam');
    Route::get('/admin/exam_management/{vacancy_id}/view_exam/{user_id}/json', [ExamController::class, 'getExamAnswersJson'])->name('admin.view_exam.json');
    // Admin-only exam scoring/config
    Route::get('/admin/exam_management/{vacancy_id}/view_exam/{user_id}/pdf', [ExamController::class, 'downloadExamPdf'])->name('admin.view_exam.pdf')->middleware(RedirectIfNotAdmin::class);
    Route::post('/admin/exam_management/{vacancy_id}/view_exam/{user_id}', [ExamController::class, 'saveResult'])->name('admin.save_result')->middleware(RedirectIfNotAdmin::class);
    Route::get('/admin/exam_management/{vacancy_id}/qualified', [ExamController::class, 'getQualifiedApplicants'])->name('admin.exam.qualified')->middleware(RedirectIfNotAdmin::class);
    Route::get('/admin/exam_management/{vacancy_id}/attendance-data', [ExamController::class, 'getAttendanceApplicants'])->name('admin.exam.attendance_data')->middleware(RedirectIfNotAdmin::class);
    Route::post('/admin/exam_management/{vacancy_id}/notify', [ExamController::class, 'notifyApplicants'])->name('admin.exam_notify')->middleware(RedirectIfNotAdmin::class);
    //Route::get('/admin/exam_management/{vacancy_id}/notify', [ExamController::class, 'notifyApplicants'])->name('admin.exam_notify');
    Route::post('/admin/exam_management/{vacancy_id}/details/save', [ExamController::class, 'saveExamDetails'])->name('admin.exam.details.save')->middleware(RedirectIfNotAdmin::class);
    Route::post('/admin/exam_management/{vacancy_id}/start', [ExamController::class, 'startExam'])->name('admin.exam_start')->middleware(RedirectIfNotAdmin::class);
    Route::post('/admin/exam_management/{vacancy_id}/pause', [ExamController::class, 'toggleExamPause'])->name('admin.exam.pause')->middleware(RedirectIfNotAdmin::class);
    Route::post('/admin/exam_management/{vacancy_id}/pause/{user_id}', [ExamController::class, 'toggleApplicantPause'])->name('admin.exam.pause_applicant')->middleware(RedirectIfNotAdmin::class);
    Route::post('/admin/exam_management/{vacancy_id}/notify-selected', [ExamController::class, 'notifySelectedApplicants'])->name('admin.exam.notify_selected')->middleware(RedirectIfNotAdmin::class);
    Route::post('/admin/exam_management/{vacancy_id}/attendance/{user_id}', [ExamController::class, 'updateAttendanceStatus'])->name('admin.exam.attendance.update')->middleware(RedirectIfNotAdmin::class);
    Route::post('/admin/exam_management/{vacancy_id}/resume/{user_id}', [ExamController::class, 'resumeApplicantExam'])->name('admin.exam.resume')->middleware(RedirectIfNotAdmin::class);


    // Exam Library Routes
    Route::get('/admin/exam-library', [App\Http\Controllers\ExamLibraryController::class, 'index'])->name('admin.exam_library')->middleware(RedirectIfNotAdmin::class);
    // Selection page (read-only) for importing series into an exam
    Route::get('/admin/exam-library/select', function (Illuminate\Http\Request $request) {
        $series = App\Models\QuestionSeries::withCount('questions')->orderByDesc('created_at')->get();
        return view('admin.exam_library.select', compact('series'));
    })->name('admin.exam_library.select')->middleware(RedirectIfNotAdmin::class);
    Route::post('/admin/exam-library/series', [App\Http\Controllers\ExamLibraryController::class, 'storeSeries'])->name('admin.exam_library.series.store')->middleware(RedirectIfNotAdmin::class);
    Route::put('/admin/exam-library/series/{id}', [App\Http\Controllers\ExamLibraryController::class, 'updateSeries'])->name('admin.exam_library.series.update')->middleware(RedirectIfNotAdmin::class);
    Route::delete('/admin/exam-library/series/{id}', [App\Http\Controllers\ExamLibraryController::class, 'deleteSeries'])->name('admin.exam_library.series.delete')->middleware(RedirectIfNotAdmin::class);
    Route::get('/admin/exam-library/series/{id}', [App\Http\Controllers\ExamLibraryController::class, 'getSeriesQuestions'])->name('admin.exam_library.series.show')->middleware(RedirectIfNotAdmin::class);
    Route::get('/admin/exam-library/series/{id}/questions', [App\Http\Controllers\ExamLibraryController::class, 'getSeriesQuestions'])->name('admin.exam_library.series.questions')->middleware(RedirectIfNotAdmin::class);
    Route::post('/admin/exam-library/series/{id}/questions', [App\Http\Controllers\ExamLibraryController::class, 'storeQuestion'])->name('admin.exam_library.questions.store')->middleware(RedirectIfNotAdmin::class);
    Route::put('/admin/exam-library/questions/{id}', [App\Http\Controllers\ExamLibraryController::class, 'updateQuestion'])->name('admin.exam_library.questions.update')->middleware(RedirectIfNotAdmin::class);
    Route::delete('/admin/exam-library/questions/{id}', [App\Http\Controllers\ExamLibraryController::class, 'deleteQuestion'])->name('admin.exam_library.questions.delete')->middleware(RedirectIfNotAdmin::class);
    Route::get('/admin/exam-library/questions/selection', [App\Http\Controllers\ExamLibraryController::class, 'getQuestionsForSelection'])->name('admin.exam_library.questions.selection')->middleware(RedirectIfNotAdmin::class);

    Route::get('/admin/exam_management/{vacancy_id}/edit', [ExamController::class, 'editExam'])->name('admin.exam.edit')->middleware(RedirectIfNotAdmin::class);
    Route::post('/admin/exam_management/{vacancy_id}/edit', [ExamController::class, 'updateExam'])->name('admin.exam.update')->middleware(RedirectIfNotAdmin::class);

    //Export
    Route::get('/export-job-vacancies-cos', [ExportController::class, 'exportCOS'])
        ->middleware(RedirectIfNotAdmin::class)
        ->withoutMiddleware([ViewerAccess::class, \App\Http\Middleware\EnsureAdminAbility::class, 'admin.ability:admin.exam.monitor'])
        ->name('exportJobVacancyCOS');
    Route::get('/export-job-vacancies-plantilla', [ExportController::class, 'exportPlantilla'])
        ->middleware(RedirectIfNotAdmin::class)
        ->withoutMiddleware([ViewerAccess::class, \App\Http\Middleware\EnsureAdminAbility::class, 'admin.ability:admin.exam.monitor'])
        ->name('exportJobVacancyPlantilla');
    Route::get('/export-job-vacancies-all', [ExportController::class, 'exportAllVacancies'])
        ->middleware(RedirectIfNotAdmin::class)
        ->withoutMiddleware([ViewerAccess::class, \App\Http\Middleware\EnsureAdminAbility::class, 'admin.ability:admin.exam.monitor'])
        ->name('exportJobVacancyAll');
    Route::get('/export-activities-all', [ExportController::class, 'exportActivities'])
        ->middleware(RedirectIfNotAdmin::class)
        ->withoutMiddleware([ViewerAccess::class, \App\Http\Middleware\EnsureAdminAbility::class, 'admin.ability:admin.exam.monitor'])
        ->name('exportActivities');
    Route::get('/export/reviewed-applications/{vacancy_id}', [ExportController::class, 'exportReviewedApplications'])
        ->middleware(RedirectIfNotAdmin::class)
        ->withoutMiddleware([ViewerAccess::class, \App\Http\Middleware\EnsureAdminAbility::class, 'admin.ability:admin.exam.monitor'])
        ->name('exportReviewed');
    Route::get('/export/not-reviewed-applications/{vacancy_id}', [ExportController::class, 'exportNotReviewedApplications'])
        ->middleware(RedirectIfNotAdmin::class)
        ->withoutMiddleware([ViewerAccess::class, \App\Http\Middleware\EnsureAdminAbility::class, 'admin.ability:admin.exam.monitor'])
        ->name('exportNotReviewed');

    // New Applicant List Exports
    Route::get('/export/final-selection-lineup/{vacancy_id}', [Forms\ApplicantListExportController::class, 'exportFinalSelection'])
        ->middleware(RedirectIfNotAdmin::class)
        ->name('export.final_selection');
    Route::get('/export/list-of-applicants/{vacancy_id}', [Forms\ApplicantListExportController::class, 'exportListOfApplicants'])
        ->middleware(RedirectIfNotAdmin::class)
        ->name('export.list_of_applicants');

    // Previews
    Route::get('/preview/final-selection-lineup/{vacancy_id}', [Forms\ApplicantListExportController::class, 'previewFinalSelection'])
        ->middleware(RedirectIfNotAdmin::class)
        ->name('preview.final_selection');
    Route::get('/preview/list-of-applicants/{vacancy_id}', [Forms\ApplicantListExportController::class, 'previewListOfApplicants'])
        ->middleware(RedirectIfNotAdmin::class)
        ->name('preview.list_of_applicants');


    //Import
    Route::post('/import-job-vacancy-cos', [ImportController::class, 'importCOS'])->middleware(RedirectIfNotAdmin::class)->name('importJobVacancyCOS');
    Route::post('/import-job-vacancy-plantilla', [ImportController::class, 'importPlantilla'])->middleware(RedirectIfNotAdmin::class)->name('importJobVacancyPlantilla');
    Route::get('/download-cos-template', [ImportController::class, 'downloadCOSTemplate'])->middleware(RedirectIfNotAdmin::class)->name('downloadCOSTemplate');
    Route::get('/download-plantilla-template', [ImportController::class, 'downloadPlantillaTemplate'])->middleware(RedirectIfNotAdmin::class)->name('downloadPlantillaTemplate');


});

// =========================
// Notifications
// =========================
Route::middleware(['auth:web,admin'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::get('/notifications/count', [NotificationController::class, 'unreadCount'])->name('notifications.count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all', [NotificationController::class, 'markAll'])->name('notifications.mark_all');
    Route::post('/notifications/cleanup', [NotificationController::class, 'cleanup'])->name('notifications.cleanup');
});

Route::middleware(['auth:admin', RedirectIfNotAdmin::class])->group(function () {
    Route::get('/admin/notifications', [NotificationController::class, 'adminIndex'])->name('admin.notifications.index');
});

// ==================================================================================================
// EXAM ROUTES
// EXAM ROUTES (for users taking exams)

// ==================================================================================================
Route::middleware(['auth', BlockIfAdmin::class])->group(function () {
    Route::get('/exam/{vacancy_id}/questions', [ExamController::class, 'examQuestion'])
        ->middleware('throttle:120,1')
        ->name('user.exam_question_page');
    Route::get('/exam/{vacancy_id}/lobby', [ExamController::class, 'examLobby'])
        ->middleware('throttle:120,1')
        ->name('user.exam_lobby');
    // Route::post('/exam/{vacancy_id}/start', [ExamController::class, 'startCandidateExam'])->name('user.exam_start');
    Route::post('/exam/{vacancy_id}/submit', [ExamController::class, 'submit'])
        ->middleware('throttle:30,1')
        ->name('exam.submit');
    Route::post('/exam/{vacancy_id}/autosave', [ExamController::class, 'autoSave'])
        ->middleware('throttle:240,1')
        ->name('exam.autosave');
    Route::get('/exam/status/{vacancy_id}', [ExamController::class, 'checkExamStatus'])
        ->middleware('throttle:120,1')
        ->name('exam.status.check');
    Route::get('/exam/{vacancy_id}/attempt-status', [ExamController::class, 'checkParticipantExamStatus'])
        ->middleware('throttle:120,1')
        ->name('exam.attempt_status');
    Route::get('/exam/{vacancy_id}/thankyou', fn($vacancy_id) => view('exam_user.exam_thankyou', compact('vacancy_id')))
        ->middleware('throttle:120,1')
        ->name('user.exam_thankyou');
    Route::post('/log-switch', [ExamController::class, 'logSwitch'])
        ->middleware('throttle:240,1');
});

// ==================================================================================================
// VIEWER ROUTES
// ==================================================================================================
//Route::get('/viewer', fn() => view('viewer.viewer_dashboard'))->name('viewer');
//Route::get('/viewer/exam_management', fn() => view('viewer.viewer_exam_management'))->name('viewer.exam_management');
//Route::get('/viewer/exam_management/view_exam', fn() => view('viewer.viewer_answer_view'))->name('viewer.view_exam');

// ==================================================================================================
// GOOGLE AUTH ROUTES
// ==================================================================================================
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// ==================================================================================================
// APPLICANT PROFILE LIST
// ==================================================================================================
Route::middleware([ApplicantsAccess::class, 'admin.ability:admin.applicants.monitor'])->group(function () {
    Route::get('/admin/applicant_status/{user_id}/{vacancy_id}', [AdminController::class, 'viewApplicantStatus'])->name('admin.applicant_status');
    Route::post('/admin/applicant_status/{user_id}/{vacancy_id}', [AdminController::class, 'updateApplicantStatus'])->name('admin.applicant_status.update');
    Route::post('/admin/applicant_status/{user_id}/{vacancy_id}/update-document', [AdminController::class, 'updateDocumentStatusAjax'])->name('admin.applicant_status.update_document');
    Route::get('/admin/applicant_status/{user_id}/{vacancy_id}/documents', [AdminController::class, 'getUpdatedDocuments'])->name('admin.applicant_status.get_documents');
    Route::post('/admin/applicant_status/{user_id}/{vacancy_id}/update-remarks', [AdminController::class, 'updateApplicationRemarksAjax'])->name('admin.applicant_status.update_remarks');
    Route::post('/admin/applicant_status/{user_id}/{vacancy_id}/notify', [AdminController::class, 'notifyApplicant'])->name('admin.applicant_status.notify');
    Route::get('/admin/preview-document/{user_id}/{vacancy_id}/{document_type}', [AdminController::class, 'previewDocument'])->name('admin.preview_document');

    Route::get('/admin/applicants-profile', [ShowApplicantsProfile::class, 'index'])->name('applicants_profile');
    Route::get('/admin/reviewed-applicants', [ShowApplicantsProfile::class, 'reviewedIndex'])->name('reviewed_applicants');
    Route::get('/admin/reviewed-applicants/sort', [ShowApplicantsProfile::class, 'ajaxSort'])->name('reviewed_applicants.sort');
    Route::get('/admin/applications_list', [ShowApplicantsProfile::class, 'applicationsList'])->name('applications_list');
    Route::get('/admin/applications_list/access-state', [ShowApplicantsProfile::class, 'hrDivisionAccessState'])->name('admin.applications_list.access_state');
    Route::get('/admin/reviewed/{vacancy_id}', [ShowApplicantsProfile::class, 'reviewedIndex'])->name('admin.reviewed');
    Route::get('/admin/applicants/{vacancy_id}', [ShowApplicantsProfile::class, 'index'])->name('admin.applicants');
    Route::get('/admin/applicants-profile/sort', [ShowApplicantsProfile::class, 'ajaxSortApplicants'])->name('admin.applicants.sort');
    Route::get('/admin/all-applicants/{vacancy_id}', [ShowApplicantsProfile::class, 'allApplicants'])->name('applicants_profile.all');

    // Manage Applicants Routes (New)
    // Keep static AJAX endpoints before dynamic vacancy route to avoid route collisions.
    Route::get('/admin/manage_applicants/new', [ShowApplicantsProfile::class, 'ajaxFilterNewApplicants'])->name('admin.manage_applicants.new');
    Route::get('/admin/manage_applicants/compliance', [ShowApplicantsProfile::class, 'ajaxFilterComplianceApplicants'])->name('admin.manage_applicants.compliance');
    Route::get('/admin/manage_applicants/qualified', [ShowApplicantsProfile::class, 'ajaxFilterQualifiedApplicants'])->name('admin.manage_applicants.qualified');
    Route::get('/admin/manage_applicants/no-pqe', [ShowApplicantsProfile::class, 'ajaxFilterNoPqeApplicants'])->name('admin.manage_applicants.no_pqe');
    Route::get('/admin/manage_applicants/{vacancy_id}', [ShowApplicantsProfile::class, 'manageApplicants'])->name('admin.manage_applicants');
});
// ==================================================================================================
// APPLICATION ROUTE
// CHAT-BOT ROUTES
// ==================================================================================================
Route::post('/apply/{vacancy_id}', [JobVacancyController::class, 'apply'])
    ->middleware('auth')
    ->name('application.store');



// ADMIN ROLE
/*
Route::middleware(['web', 'auth:admin', 'admin.role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('dashboard_admin');
});

Route::middleware(['web', 'auth:admin', 'admin.role:viewer'])->group(function () {
    Route::get('/viewer', fn() => view('viewer.viewer_dashboard'))->name('viewer');
});
*/

// ==================================================================================================
// TEST ROUTES
// ==================================================================================================
Route::get('/test-event', function () {
    if (!app()->environment(['local', 'testing'])) {
        abort(404);
    }
    broadcast(new PackageSent('test data', 'test'));
    return 'Event broadcasted';
});
Route::get('/admin/reviewed_applicants', [AdminController::class, 'reviewedApplicants'])->middleware(ApplicantsAccess::class)->name('reviewed_applicants_legacy');

Route::redirect('/dashboard/admin', '/admin/dashboard')
    ->middleware(RedirectIfNotAdmin::class);

//Route::get('/dashboard-progress', [JobVacancyController::class, 'pdsAndWesProgress'])->name('dashboard.progress');
//Route::get('/dashboard', [JobVacancyController::class, 'pdsAndWesProgress'])->name('dashboard_user');
//Route::get('/dashboard', [JobVacancyController::class, 'pdsAndWesProgress'])->name('dashboarduser.dashboard_user');


//Chat-Bot
Route::post('/chat', [GeminiChatController::class, 'chat'])->middleware('throttle:20,1');

//error mobile
Route::get('/mobile-locked', function () {
    return response()->view('errors.mobile');
})->name('mobile.locked');


// LIVE SERVER ROUTES
Route::get('/preview-file/{path}', function ($path) {
    // Helper to return "No Document Submitted" view
    $noDocumentView = function () {
        return response('
            <html>
            <body style="display:flex;justify-content:center;align-items:center;height:100%;margin:0;font-family:sans-serif;background-color:#f9fafb;color:#6b7280;">
                <div style="text-align:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:1rem;display:inline-block;"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                    <p style="font-size:1.125rem;font-weight:500;">No Document Submitted</p>
                </div>
            </body>
            </html>
        ', 200);
    };

    $decodedPath = base64_decode((string) $path, true);
    if (!is_string($decodedPath) || $decodedPath === '') {
        return $noDocumentView();
    }

    $decodedPath = trim(str_replace(["\0", "\r", "\n"], '', str_replace('\\', '/', $decodedPath)));
    $decodedPath = ltrim($decodedPath, '/');

    if ($decodedPath === '' || $decodedPath === 'missing') {
        return $noDocumentView();
    }

    $resolvePathWithinRoots = function (array $roots, string $relativePath): ?string {
        $normalizedRelativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if ($normalizedRelativePath === '' || str_contains($normalizedRelativePath, "\0")) {
            return null;
        }

        $relativeWithOsSeparators = str_replace('/', DIRECTORY_SEPARATOR, $normalizedRelativePath);

        foreach ($roots as $root) {
            $realRoot = realpath($root);
            if (!is_string($realRoot) || $realRoot === '') {
                continue;
            }

            $candidate = realpath($realRoot . DIRECTORY_SEPARATOR . $relativeWithOsSeparators);
            if (!is_string($candidate) || !is_file($candidate)) {
                continue;
            }

            $rootPrefix = rtrim($realRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (!str_starts_with($candidate, $rootPrefix)) {
                continue;
            }

            return $candidate;
        }

        return null;
    };

    $fullPath = $resolvePathWithinRoots([
        storage_path('app'),
        storage_path('app/public'),
        public_path('storage'),
    ], $decodedPath);

    if (!$fullPath) {
        return $noDocumentView();
    }

    $type = mime_content_type($fullPath);
    $file = file_get_contents($fullPath);

    // If it's a viewable type, show it
    if ($type === 'application/pdf' || str_starts_with($type, 'image/') || $type === 'text/plain') {
        return response($file, 200)->header("Content-Type", $type);
    }

    // Otherwise show "Preview Not Available"
    return response('
        <html>
        <body style="display:flex;justify-content:center;align-items:center;height:100%;margin:0;font-family:sans-serif;background-color:#f9fafb;color:#6b7280;">
            <div style="text-align:center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:1rem;display:inline-block;"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                <p style="font-size:1.125rem;font-weight:500;">Preview Not Available</p>
                <p style="font-size:0.875rem;margin-top:0.5rem;color:#9ca3af;">File type: ' . htmlspecialchars($type) . '</p>
            </div>
        </body>
        </html>
    ', 200);
})->where('path', '.*')->name('preview.file')->middleware('signed');

Route::get('storage/{filename}', function ($filename) {
    $normalizedFilename = trim(str_replace(["\0", "\r", "\n"], '', str_replace('\\', '/', (string) $filename)));
    $normalizedFilename = ltrim($normalizedFilename, '/');

    if ($normalizedFilename === '' || str_contains($normalizedFilename, "\0")) {
        abort(404);
    }

    $root = realpath(storage_path('app/public'));
    if (!is_string($root) || $root === '') {
        abort(404);
    }

    $candidate = realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalizedFilename));
    if (!is_string($candidate) || !is_file($candidate)) {
        abort(404);
    }

    $rootPrefix = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (!str_starts_with($candidate, $rootPrefix)) {
        abort(404);
    }

    $file = file_get_contents($candidate);
    $type = mime_content_type($candidate);

    return response($file, 200)->header("Content-Type", $type);
})->where('filename', '.*');
