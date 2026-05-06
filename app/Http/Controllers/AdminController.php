<?php

namespace App\Http\Controllers;

use App\Support\PreviewUrl;
use App\Enums\ApplicationStatus;
use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Vacancy;
use App\Models\ExamDetail;
use App\Models\JobVacancy;
use App\Models\VacancyTitle;
use App\Models\Applications;
use App\Models\Notification;
use App\Models\UploadedDocument;
use App\Models\AdminVacancyAccess;
use App\Models\User;
use App\Models\EducationalBackground;
use App\Models\WorkExperience;
use App\Models\LearningAndDevelopment;
use App\Models\CivilServiceEligibility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


use Illuminate\Support\Facades\Mail;
use App\Mail\AdminEventNotification;



use App\Mail\NotifyApplicantOverview;
use App\Jobs\SendApplicantNotificationEmails;
use Illuminate\Support\Facades\Storage;
use App\Services\ApplicationStatusTransitionService;

class AdminController extends Controller
{
    private const ACCOUNT_TYPES = ['superadmin', 'admin', 'hr_division', 'viewer'];
    private const APPROVABLE_ACCOUNT_TYPES = ['admin', 'hr_division', 'viewer'];
    private const DOCUMENT_LABELS = [
        'application_letter' => 'Application Letter',
        'signed_pds' => 'Signed and Subscribed Personal Data Sheet',
        'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
        'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
        'cert_eligibility' => 'Certificate of Eligibility / Board Rating',
        'ipcr' => 'Performance Rating/IPCR in the last period (if applicable)',
        'non_academic' => 'Non-Academic Awards Received',
        'cert_training' => 'Certificate/s of Training Attended/Participated relevant to the position being applied',
        'designation_order' => 'List with Certified Photocopy of Duly Confirmed Designation Order/s',
        'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
        'photocopy_diploma' => 'Diploma',
        'grade_masteraldoctorate' => 'Certified Photocopy of Certificate of Grades with Masteral/Doctorate Units Earned',
        'tor_masteraldoctorate' => 'Certified Photocopy of TOR with Masteral/Doctorate Degree',
        'cert_employment' => 'Certificate of Employment (If Any)',
        'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
        'passport_photo' => '2" x 2" or Passport Size Picture',
        'other_documents' => 'Other Documents Submitted',
    ];
    private const DOCUMENT_TYPE_ALIASES = [
        'cert_eligibility' => ['cert_elegibility'],
        'cert_employment' => ['certificate_employment'],
        'grade_masteraldoctorate' => ['certificate_grades'],
        'tor_masteraldoctorate' => ['certified_tor'],
        'ipcr' => ['performance_rating'],
        'non_academic' => ['non_academic_awards'],
        'cert_training' => ['certificates_participation'],
        'designation_order' => ['designation_orders'],
        'transcript_records' => ['transcript'],
        'photocopy_diploma' => ['diploma'],
    ];
    private const COS_REQUIRED_DOCUMENTS = [
        'passport_photo',
        'signed_pds',
        'signed_work_exp_sheet',
        'photocopy_diploma',
        'application_letter',
        'cert_training',
    ];

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    private function generateUniqueUsername(string $firstName, string $lastName, string $email): string
    {
        $nameBase = Str::slug(trim($firstName . ' ' . $lastName), '_');
        $emailBase = Str::slug((string) Str::before($email, '@'), '_');
        $base = Str::lower($nameBase !== '' ? $nameBase : ($emailBase !== '' ? $emailBase : 'admin'));

        $candidate = $base;
        $suffix = 1;
        while (Admin::where('username', $candidate)->exists()) {
            $candidate = $base . '_' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    // List all admin accounts
    public function manage()
    {
        $admins = Admin::query()
            ->orderByRaw("CASE WHEN approval_status = 'pending' THEN 0 WHEN approval_status = 'declined' THEN 2 ELSE 1 END")
            ->orderBy('name')
            ->orderBy('email')
            ->get();
        $cosVacancies = $this->getCosVacanciesForHrDivisionAccess();
        $hrDivisionAccessMap = $this->getHrDivisionAccessMap($admins);
        $hrDivisionAccessLabelMap = $this->getHrDivisionAccessLabelMap($hrDivisionAccessMap);
        // $users = User::all(); // Removed to prevent fetching participants

        activity()
            ->causedBy(auth('admin')->user())
            ->event('view')
            ->withProperties(['section' => 'System Users Management'])
            ->log('Viewed admin account management.');

        return view('admin.admin_account_management', compact('admins', 'cosVacancies', 'hrDivisionAccessMap', 'hrDivisionAccessLabelMap'));
    }

    public function accountSettings()
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        activity()
            ->causedBy($admin)
            ->event('view')
            ->withProperties(['section' => 'Account Settings'])
            ->log('Viewed account settings.');

        return view('admin.admin_account_settings', compact('admin'));
    }

    public function updateAccountSettings(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'office' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
        ], [
            'email.unique' => 'The email has already been taken.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'settingsUpdate')
                ->withInput();
        }

        $validated = $validator->validated();
        $firstName = trim((string) ($validated['first_name'] ?? ''));
        $middleName = trim((string) ($validated['middle_name'] ?? ''));
        $lastName = trim((string) ($validated['last_name'] ?? ''));
        $fullName = implode(' ', array_filter([$firstName, $middleName, $lastName], fn($part) => $part !== ''));

        $admin->update([
            'name' => $fullName,
            'office' => $validated['office'],
            'designation' => $validated['designation'],
            'email' => $validated['email'],
        ]);

        activity()
            ->causedBy($admin)
            ->performedOn($admin)
            ->event('update')
            ->withProperties(['section' => 'Account Settings'])
            ->log('Updated own account profile details.');

        return redirect()->back()->with('settings_success', 'Profile details updated successfully.');
    }

    public function updateOwnPassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:current_password',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/',
            ],
        ], [
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password.different' => 'The new password must be different from your current password.',
            'new_password.regex' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'passwordUpdate');
        }

        if (!Hash::check((string) $request->input('current_password'), (string) $admin->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'The current password is incorrect.'], 'passwordUpdate');
        }

        $admin->password = Hash::make((string) $request->input('new_password'));
        $admin->save();

        activity()
            ->causedBy($admin)
            ->performedOn($admin)
            ->event('update')
            ->withProperties(['section' => 'Account Settings'])
            ->log('Updated own account password.');

        return redirect()->back()->with('password_success', 'Password updated successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'office' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
            'account_type' => ['required', Rule::in(self::ACCOUNT_TYPES)],
        ], [
            'email.unique' => 'The email has already been taken.',
        ]);

        $actor = Auth::guard('admin')->user();
        if (($validated['account_type'] ?? null) === 'superadmin' && $actor?->role !== 'superadmin') {
            return redirect()->back()->with('error', 'Only a superadmin can create another superadmin account.');
        }

        $firstName = trim((string) ($validated['first_name'] ?? ''));
        $middleName = trim((string) ($validated['middle_name'] ?? ''));
        $lastName = trim((string) ($validated['last_name'] ?? ''));
        $fullName = trim(implode(' ', array_filter([$firstName, $lastName, $middleName], fn($part) => $part !== '')));
        $generatedUsername = $this->generateUniqueUsername($firstName, $lastName, (string) ($validated['email'] ?? ''));

        $payload = [
            'username' => $generatedUsername,
            'name' => $fullName,
            'office' => $validated['office'],
            'designation' => $validated['designation'],
            'email' => $validated['email'],
            'password' => Hash::make((string) $validated['password']),
            'role' => $validated['account_type'],
        ];

        $createdAdmin = Admin::create($payload);
        if (($createdAdmin->role ?? null) === 'superadmin' && (int) ($createdAdmin->is_active ?? 1) === 1) {
            $this->deactivateOtherSuperadmins((int) $createdAdmin->id);
        }

        activity()
            ->causedBy(auth('admin')->user())
            ->performedOn($createdAdmin)
            ->event('create')
            ->withProperties([
                'username' => $generatedUsername,
                'email' => $validated['email'],
                'section' => 'System Users Management',
            ])
            ->log('Created a new admin account.');

        return redirect()->back()->with('success', 'Admin account created successfully!');
    }


    public function deactivate($id)
    {
        $admin = Admin::findOrFail($id);
        $authUser = Auth::guard('admin')->user();

        if (!$authUser) {
            return redirect()->route('admin.login')->with('error', 'You must be logged in.');
        }

        if ($authUser->id == $admin->id) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($admin->role === 'superadmin' && $authUser->role !== 'superadmin') {
            return redirect()->back()->with('error', 'Only a superadmin can deactivate a superadmin account.');
        }

        $admin->is_active = false;
        $admin->save();

        activity()
            ->causedBy(auth('admin')->user())
            ->performedOn($admin)
            ->event('deactivate')
            ->withProperties(['deactivated_admin_id' => $admin->id, 'section' => 'System Users Management'])
            ->log('Deactivated an admin account.');


        return redirect()->back()->with('success', 'Admin deactivated successfully.');
    }

    public function activate($id)
    {
        $admin = Admin::findOrFail($id);
        $authUser = Auth::guard('admin')->user();

        if ((string) ($admin->approval_status ?? 'approved') === 'pending') {
            return redirect()->back()->with('error', 'Pending accounts must be approved or declined first.');
        }

        if ($admin->role === 'superadmin' && $authUser?->role !== 'superadmin') {
            return redirect()->back()->with('error', 'Only a superadmin can activate a superadmin account.');
        }

        $admin->is_active = true;
        $admin->save();
        if (($admin->role ?? null) === 'superadmin') {
            $this->deactivateOtherSuperadmins((int) $admin->id);
        }

        activity()
            ->causedBy(auth('admin')->user())
            ->performedOn($admin)
            ->event('activate')
            ->withProperties(['activated_admin_id' => $admin->id, 'section' => 'System Users Management'])
            ->log('Activated an admin account.');

        return redirect()->back()->with('success', 'Admin activated successfully.');
    }

    public function approve(Request $request, $id)
    {
        $targetAdmin = Admin::findOrFail($id);
        $authUser = Auth::guard('admin')->user();

        if (!$authUser || ($authUser->role ?? null) !== 'superadmin') {
            return redirect()->back()->with('error', 'Only superadmin can approve user registrations.');
        }

        if ((int) $targetAdmin->id === (int) $authUser->id) {
            return redirect()->back()->with('error', 'You cannot approve your own account.');
        }

        if ((string) ($targetAdmin->approval_status ?? 'approved') !== 'pending') {
            return redirect()->back()->with('error', 'Only pending accounts can be approved.');
        }

        $validated = $request->validate([
            'approval_role' => ['required', Rule::in(self::APPROVABLE_ACCOUNT_TYPES)],
        ]);

        $targetAdmin->update([
            'role' => $validated['approval_role'],
            'approval_status' => 'approved',
            'approved_by' => $authUser->id,
            'approved_at' => now(),
            'declined_at' => null,
            'is_active' => 1,
        ]);

        $roleLabel = match ($validated['approval_role']) {
            'admin' => 'Admin (HR)',
            'hr_division' => 'HR Division',
            'viewer' => 'Viewer',
            default => ucfirst((string) $validated['approval_role']),
        };

        Notification::create([
            'notifiable_type' => 'App\Models\Admin',
            'notifiable_id' => $targetAdmin->id,
            'type' => 'info',
            'data' => [
                'category' => 'account_approval',
                'title' => 'Account Approved',
                'message' => 'Your registration has been approved. Assigned role: ' . $roleLabel . '.',
                'action_url' => route('admin.login', [], false),
                'level' => 'success',
            ],
        ]);

        activity()
            ->causedBy($authUser)
            ->performedOn($targetAdmin)
            ->event('approve')
            ->withProperties([
                'approved_admin_id' => $targetAdmin->id,
                'assigned_role' => $validated['approval_role'],
                'section' => 'System Users Management',
            ])
            ->log('Approved a pending admin registration.');

        return redirect()->back()->with('success', 'Account approved successfully.');
    }

    public function decline($id)
    {
        $targetAdmin = Admin::findOrFail($id);
        $authUser = Auth::guard('admin')->user();

        if (!$authUser || ($authUser->role ?? null) !== 'superadmin') {
            return redirect()->back()->with('error', 'Only superadmin can decline user registrations.');
        }

        if ((int) $targetAdmin->id === (int) $authUser->id) {
            return redirect()->back()->with('error', 'You cannot decline your own account.');
        }

        if ((string) ($targetAdmin->approval_status ?? 'approved') !== 'pending') {
            return redirect()->back()->with('error', 'Only pending accounts can be declined.');
        }

        $targetAdmin->update([
            'approval_status' => 'declined',
            'approved_by' => $authUser->id,
            'approved_at' => null,
            'declined_at' => now(),
            'is_active' => 1,
        ]);

        Notification::create([
            'notifiable_type' => 'App\Models\Admin',
            'notifiable_id' => $targetAdmin->id,
            'type' => 'warning',
            'data' => [
                'category' => 'account_approval',
                'title' => 'Account Declined',
                'message' => 'Your registration request was declined. Please contact the superadmin for details.',
                'action_url' => route('admin.login', [], false),
                'level' => 'warning',
            ],
        ]);

        activity()
            ->causedBy($authUser)
            ->performedOn($targetAdmin)
            ->event('decline')
            ->withProperties([
                'declined_admin_id' => $targetAdmin->id,
                'section' => 'System Users Management',
            ])
            ->log('Declined a pending admin registration.');

        return redirect()->back()->with('success', 'Account declined successfully.');
    }

    private function getReviewedApplications(?int $limit = null)
    {
        /*
        activity()
            ->causedBy(auth()->guard('admin')->user())
            ->log('Viewed reviewed applicants list.');
        */

        $query = Applications::with(['personalInformation', 'vacancy'])
            ->whereRaw('LOWER(TRIM(status)) <> ?', [strtolower(ApplicationStatus::PENDING->value)])
            ->whereHas('personalInformation')
            ->latest();

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function dashboard(Request $request)
    {
        //info('check');
        $selectedYear = $request->query('year', now()->year);

        // Get all years with applications, or default to current year
        $years = DB::table('applications')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        // If no years found, add current year as default
        if (empty($years)) {
            $years = [now()->year];
        }

        // Get monthly application counts for selected year
        $monthlyApplicants = DB::table('applications')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        //info('check');

        // Generate month labels (Jan, Feb, Mar, etc.)
        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Initialize all months with 0
        $monthCounts = array_fill(0, 12, 0);

        // Fill in actual counts
        foreach ($monthlyApplicants as $record) {
            $monthIndex = (int) $record->month - 1; // Convert to 0-based index
            $monthCounts[$monthIndex] = (int) $record->total;
        }

        //info('check');

        $chartLabels = $monthLabels;
        $chartData = $monthCounts;

        $openVacanciesQuery = Vacancy::query()->where('status', 'OPEN');
        $openVacancyCount = (clone $openVacanciesQuery)->count();
        $cosVacancyCount = (clone $openVacanciesQuery)
            ->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
            ->count();
        $plantillaVacancyCount = max($openVacancyCount - $cosVacancyCount, 0);
        $openVacancies = collect();

        $onGoingApplications = Applications::query()
            ->select(['id', 'user_id', 'vacancy_id', 'status', 'created_at'])
            ->with(['personalInformation', 'vacancy'])
            ->whereIn('status', ['Incomplete', 'Pending'])
            ->latest('created_at')
            ->limit(6)
            ->get();

        $onGoingApplicationsCount = Applications::query()
            ->whereIn('status', ['Incomplete', 'Pending'])
            ->count();

        $reviewedApplications = $this->getReviewedApplications(10);
        $reviewedApplicationsCount = Applications::query()
            ->whereRaw('LOWER(TRIM(status)) <> ?', [strtolower(ApplicationStatus::PENDING->value)])
            ->count();

        $systemUsersCount = Admin::query()->where('is_active', 1)->count();
        $systemUsers = collect();

        $now = Carbon::now()->toDateTimeString();
        $upcomingExamsCount = ExamDetail::query()
            ->whereRaw("TIMESTAMP(`date`, `time`) > ?", [$now])
            ->count();
        $upcomingExams = collect();

        /*
        activity()
            ->causedBy(auth()->guard('admin')->user())
            ->log('Viewed admin dashboard.');
        */

        return view('admin.dashboard_admin', [
            'openVacancies' => $openVacancies,
            'openVacancyCount' => $openVacancyCount,
            'cosVacancyCount' => $cosVacancyCount,
            'plantillaVacancyCount' => $plantillaVacancyCount,
            'onGoingApplications' => $onGoingApplications,
            'onGoingApplicationsCount' => $onGoingApplicationsCount,
            'reviewedApplications' => $reviewedApplications,
            'reviewedApplicationsCount' => $reviewedApplicationsCount,
            'systemUsers' => $systemUsers,
            'systemUsersCount' => $systemUsersCount,
            'upcomingExams' => $upcomingExams,
            'upcomingExamsCount' => $upcomingExamsCount,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'selectedYear' => $selectedYear,
            'years' => $years,
        ]);
        //info('check');

    }

    public function reviewedApplicants()
    {
        $reviewedApplications = $this->getReviewedApplications();
        /*
        activity()
            ->causedBy(auth()->guard('admin')->user())
            ->log('Viewed reviewed applicants list.');
        */
        return view('admin.reviewed_applicants', compact('reviewedApplications'));
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $authUser = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'office' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins')->ignore($admin->id)],
            'account_type' => ['required', Rule::in(self::ACCOUNT_TYPES)],
        ]);

        // If validation fails, flash errors as a single string or array
        if ($validator->fails()) {
            return redirect()->back()
                ->with('_editing', $admin->id)
                ->with('error', $validator->errors()->all()) // flash as array of errors
                ->withInput();
        }

        $validated = $validator->validated();
        $firstName = trim((string) ($validated['first_name'] ?? ''));
        $middleName = trim((string) ($validated['middle_name'] ?? ''));
        $lastName = trim((string) ($validated['last_name'] ?? ''));
        $fullName = implode(' ', array_filter([$firstName, $middleName, $lastName], fn($part) => $part !== ''));

        if ((string) ($admin->approval_status ?? 'approved') === 'pending' && ($validated['account_type'] ?? null) !== ($admin->role ?? null)) {
            return redirect()->back()
                ->with('_editing', $admin->id)
                ->with('error', ['Pending account role can only be set through the Approve action.'])
                ->withInput();
        }

        if (($admin->role ?? null) !== 'superadmin' && ($validated['account_type'] ?? null) === 'superadmin') {
            return redirect()->back()
                ->with('_editing', $admin->id)
                ->with('error', ['Superadmin account type cannot be assigned through Edit Account.'])
                ->withInput();
        }

        if (($admin->role ?? null) === 'superadmin' && ($validated['account_type'] ?? null) !== 'superadmin') {
            return redirect()->back()
                ->with('_editing', $admin->id)
                ->with('error', ['Superadmin account type cannot be changed through Edit Account.'])
                ->withInput();
        }

        if (
            (($validated['account_type'] ?? null) === 'superadmin' || $admin->role === 'superadmin')
            && $authUser?->role !== 'superadmin'
        ) {
            return redirect()->back()
                ->with('_editing', $admin->id)
                ->with('error', ['Only a superadmin can create, edit, or downgrade a superadmin account.'])
                ->withInput();
        }

        // Flash '_editing' so mother blade knows which admin was edited
        session()->flash('_editing', $admin->id);

        $admin->update([
            'name' => $fullName,
            'office' => $validated['office'],
            'designation' => $validated['designation'],
            'email' => $validated['email'],
            'role' => $validated['account_type'],
        ]);
        if (($admin->role ?? null) === 'superadmin' && (int) ($admin->is_active ?? 1) === 1) {
            $this->deactivateOtherSuperadmins((int) $admin->id);
        }

        activity()
            ->causedBy(auth('admin')->user())
            ->performedOn($admin)
            ->event('update')
            ->withProperties(['updated_admin_id' => $admin->id, 'section' => 'System Users Management'])
            ->log('Updated an admin account.');

        return redirect()->back()
            ->with('success', 'Admin account updated successfully!');
    }

    public function updateHrDivisionVacancyAccess(Request $request, $id)
    {
        $targetAdmin = Admin::findOrFail($id);
        $authUser = Auth::guard('admin')->user();

        if (!$authUser || ($authUser->role ?? null) !== 'superadmin') {
            return redirect()->back()->with('error', 'Only superadmin can update HR Division vacancy access.');
        }

        if (($targetAdmin->role ?? null) !== 'hr_division') {
            return redirect()->back()->with('error', 'Vacancy access can only be assigned to HR Division accounts.');
        }

        if (!Schema::hasTable('admin_vacancy_accesses')) {
            return redirect()->back()->with('error', 'Vacancy access table is not ready. Please run database migrations first.');
        }

        $validated = $request->validate([
            'vacancy_ids' => ['nullable', 'array'],
            'vacancy_ids.*' => [
                'string',
                Rule::exists('job_vacancies', 'vacancy_id')->where(function ($query) {
                    $query->whereRaw('UPPER(vacancy_type) = ?', ['COS']);
                }),
            ],
        ]);

        $vacancyIds = collect($validated['vacancy_ids'] ?? [])
            ->map(fn($value) => trim((string) $value))
            ->filter(fn($value) => $value !== '')
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($targetAdmin, $vacancyIds) {
            AdminVacancyAccess::where('admin_id', $targetAdmin->id)->delete();

            if (empty($vacancyIds)) {
                return;
            }

            $timestamp = now();
            $rows = array_map(function (string $vacancyId) use ($targetAdmin, $timestamp) {
                return [
                    'admin_id' => $targetAdmin->id,
                    'vacancy_id' => $vacancyId,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }, $vacancyIds);

            AdminVacancyAccess::insert($rows);
        });

        activity()
            ->causedBy($authUser)
            ->performedOn($targetAdmin)
            ->event('update')
            ->withProperties([
                'updated_admin_id' => $targetAdmin->id,
                'granted_vacancy_ids' => $vacancyIds,
                'section' => 'System Users Management',
            ])
            ->log('Updated HR Division COS vacancy access.');

        $assignedCount = count($vacancyIds);
        return redirect()->back()->with('success', "HR Division access updated. {$assignedCount} COS position(s) assigned.");
    }

    private function getCosVacanciesForHrDivisionAccess()
    {
        return JobVacancy::query()
            ->select(['vacancy_id', 'position_title', 'status'])
            ->where('vacancy_type', 'COS')
            ->orderBy('position_title')
            ->orderBy('vacancy_id')
            ->get();
    }

    private function getHrDivisionAccessMap($admins): array
    {
        $hrDivisionIds = $admins
            ->where('role', 'hr_division')
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        if (empty($hrDivisionIds)) {
            return [];
        }

        if (!Schema::hasTable('admin_vacancy_accesses')) {
            return [];
        }

        return AdminVacancyAccess::query()
            ->whereIn('admin_id', $hrDivisionIds)
            ->orderBy('vacancy_id')
            ->get(['admin_id', 'vacancy_id'])
            ->groupBy('admin_id')
            ->map(function ($rows) {
                return $rows->pluck('vacancy_id')->values()->all();
            })
            ->toArray();
    }

    private function getHrDivisionAccessLabelMap(array $hrDivisionAccessMap): array
    {
        $vacancyIds = collect($hrDivisionAccessMap)
            ->flatten(1)
            ->map(fn($vacancyId) => trim((string) $vacancyId))
            ->filter(fn($vacancyId) => $vacancyId !== '')
            ->unique()
            ->values()
            ->all();

        if (empty($vacancyIds)) {
            return [];
        }

        $vacancyTitleMap = JobVacancy::query()
            ->whereIn('vacancy_id', $vacancyIds)
            ->select(['vacancy_id', 'position_title'])
            ->get()
            ->mapWithKeys(function ($vacancy) {
                $vacancyId = trim((string) ($vacancy->vacancy_id ?? ''));
                $title = trim((string) ($vacancy->position_title ?? ''));

                return [$vacancyId => ($title !== '' ? $title : $vacancyId)];
            })
            ->toArray();

        return collect($hrDivisionAccessMap)
            ->map(function ($assignedVacancyIds) use ($vacancyTitleMap) {
                return collect($assignedVacancyIds)
                    ->map(function ($vacancyId) use ($vacancyTitleMap) {
                        $vacancyId = trim((string) $vacancyId);
                        if ($vacancyId === '') {
                            return null;
                        }

                        return $vacancyTitleMap[$vacancyId] ?? $vacancyId;
                    })
                    ->filter(fn($value) => $value !== null && $value !== '')
                    ->unique()
                    ->values()
                    ->all();
            })
            ->toArray();
    }

    private function hasHrDivisionVacancyAccess(?string $vacancyId): bool
    {
        $admin = Auth::guard('admin')->user();
        if (($admin->role ?? null) !== 'hr_division') {
            return true;
        }

        $vacancyId = trim((string) $vacancyId);
        if ($vacancyId === '') {
            return false;
        }

        return JobVacancy::query()
            ->where('vacancy_id', $vacancyId)
            ->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
            ->where(function ($query) use ($admin, $vacancyId) {
                $hasScope = false;

                if (Schema::hasColumn('job_vacancies', 'created_by_admin_id')) {
                    $query->where('created_by_admin_id', $admin->id);
                    $hasScope = true;
                }

                if (Schema::hasTable('admin_vacancy_accesses')) {
                    if ($hasScope) {
                        $query->orWhereExists(function ($sub) use ($admin, $vacancyId) {
                            $sub->selectRaw('1')
                                ->from('admin_vacancy_accesses')
                                ->where('admin_vacancy_accesses.admin_id', $admin->id)
                                ->where('admin_vacancy_accesses.vacancy_id', $vacancyId);
                        });
                    } else {
                        $query->whereExists(function ($sub) use ($admin, $vacancyId) {
                            $sub->selectRaw('1')
                                ->from('admin_vacancy_accesses')
                                ->where('admin_vacancy_accesses.admin_id', $admin->id)
                                ->where('admin_vacancy_accesses.vacancy_id', $vacancyId);
                        });
                    }
                    $hasScope = true;
                }

                if (!$hasScope) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->exists();
    }

    private function denyHrDivisionVacancyAccess(Request $request, ?string $vacancyId)
    {
        if ($this->hasHrDivisionVacancyAccess($vacancyId)) {
            return null;
        }

        $message = 'Access denied. This COS vacancy is not available to your HR Division account.';
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return redirect()->route('applications_list')->with('error', $message);
    }

    private function isCancelledApplication(?Applications $application): bool
    {
        return strtolower(trim((string) ($application->status ?? ''))) === 'cancelled';
    }

    private function cancelledApplicationActionResponse(Request $request)
    {
        $message = 'This application was cancelled by the applicant. No further actions are allowed.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'application_status' => 'Cancelled',
            ], 422);
        }

        return redirect()->back()->with('error', $message);
    }

    private function deactivateOtherSuperadmins(int $activeSuperadminId): void
    {
        Admin::where('role', 'superadmin')
            ->where('id', '!=', $activeSuperadminId)
            ->where('is_active', 1)
            ->update(['is_active' => 0]);
    }

    public function search(Request $request)
    {
        $search = trim((string) $request->input('query', ''));
        $role = trim((string) $request->input('role', ''));
        $status = trim((string) $request->input('status', ''));

        if (!in_array($role, self::ACCOUNT_TYPES, true)) {
            $role = '';
        }

        if (!in_array($status, ['active', 'inactive', 'pending', 'declined'], true)) {
            $status = '';
        }

        $admins = Admin::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('office', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->when($status !== '', function ($query) use ($status) {
                if ($status === 'active') {
                    $query->where('is_active', 1)
                        ->where(function ($statusQuery) {
                            $statusQuery->whereNull('approval_status')
                                ->orWhere('approval_status', 'approved');
                        });
                    return;
                }

                if ($status === 'inactive') {
                    $query->where('is_active', 0)
                        ->where(function ($statusQuery) {
                            $statusQuery->whereNull('approval_status')
                                ->orWhere('approval_status', 'approved');
                        });
                    return;
                }

                if ($status === 'pending') {
                    $query->where('approval_status', 'pending');
                    return;
                }

                if ($status === 'declined') {
                    $query->where('approval_status', 'declined');
                }
            })
            ->orderByRaw("CASE WHEN approval_status = 'pending' THEN 0 WHEN approval_status = 'declined' THEN 2 ELSE 1 END")
            ->orderBy('name')
            ->orderBy('email')
            ->get();
        $hrDivisionAccessMap = $this->getHrDivisionAccessMap($admins);
        $hrDivisionAccessLabelMap = $this->getHrDivisionAccessLabelMap($hrDivisionAccessMap);
        /*
        activity()
            ->causedBy(auth()->guard('admin')->user())
            ->withProperties(['query' => $request->input('query')])
            ->log('Searched for admins.');
        */
        return view('partials.admin_list', compact('admins', 'hrDivisionAccessMap', 'hrDivisionAccessLabelMap'))->render();
    }

    private function getApplicantDocuments($user_id, $application)
    {
        $uploadedDocuments = $this->loadUploadedDocumentsMap((int) $user_id, (string) $application->vacancy_id);
        $reusableDocuments = $this->loadReusableUploadedDocumentsMap((int) $user_id, (string) $application->vacancy_id);
        $documents = [];

        foreach (UploadedDocument::DOCUMENTS as $docType) {
            if ($docType === 'isApproved')
                continue;

            $doc = $uploadedDocuments->get($docType);

            if ($docType === 'application_letter') {
                $status = $application->file_status ?? 'Not Submitted';
                // If status is null/empty for application letter, it might mean not submitted if file is missing,
                // but usually there's a file_storage_path.
                // Let's rely on file existence check in previewDocument, but here we just generate the link.
                $hasFile = !empty($application->file_storage_path);
                $fileRevisionRequestedCount = (int) ($application->file_revision_requested_count ?? 0);
                $fileRevisionSubmittedAt = $application->file_revision_submitted_at ?? null;
                $fileRevisionLockReason = $this->getNeedsRevisionRestrictionReason(
                    $application,
                    $fileRevisionRequestedCount,
                    $fileRevisionSubmittedAt,
                    true
                );

                $documents[] = [
                    'id' => 'application_letter',
                    'name' => self::DOCUMENT_LABELS['application_letter'],
                    'text' => self::DOCUMENT_LABELS['application_letter'],
                    'status' => $status,
                    'preview' => route('admin.preview_document', ['user_id' => $user_id, 'vacancy_id' => $application->vacancy_id, 'document_type' => 'application_letter']),
                    'remarks' => $application->file_remarks ?? '',
                    'original_name' => $application->file_original_name ?? '',
                    'has_file' => $hasFile,
                    'last_modified_by' => $application->file_last_modified_by ?? 'N/A',
                    'revision_requested_count' => $fileRevisionRequestedCount,
                    'revision_submitted_at' => $fileRevisionSubmittedAt,
                    'revision_locked' => !is_null($fileRevisionLockReason),
                    'revision_lock_reason' => $fileRevisionLockReason,
                    'isBold' => true,
                ];
            } else {
                $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                $hasFile = $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
                if (!$hasFile) {
                    $fallbackDoc = $this->resolveUploadedDocument($reusableDocuments, $docType);
                    $fallbackHasFile = $fallbackDoc && !empty($fallbackDoc->storage_path) && $fallbackDoc->storage_path !== 'NOINPUT';
                    if ($fallbackHasFile) {
                        $doc = $fallbackDoc;
                        $hasFile = true;
                    }
                }

                $docStatus = trim((string) ($doc->status ?? ''));
                $isRevisionStatus = in_array(strtolower($docStatus), ['needs revision', 'disapproved with deficiency'], true);
                $status = $hasFile
                    ? ($docStatus !== '' ? $doc->status : 'Pending')
                    : ($isRevisionStatus ? $doc->status : 'Not Submitted');
                $revisionState = $this->getUploadedDocumentRevisionState(
                    (int) $user_id,
                    (string) $application->vacancy_id,
                    (string) $docType,
                    $doc
                );
                $revisionRequestedCount = (int) ($revisionState['requested_count'] ?? 0);
                $revisionSubmittedAt = $revisionState['submitted_at'] ?? null;
                $revisionLockReason = $this->getNeedsRevisionRestrictionReason(
                    $application,
                    $revisionRequestedCount,
                    $revisionSubmittedAt,
                    true
                );
                $documents[] = [
                    'id' => $docType,
                    'name' => self::DOCUMENT_LABELS[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'text' => self::DOCUMENT_LABELS[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'status' => $status,
                    'preview' => route('admin.preview_document', ['user_id' => $user_id, 'vacancy_id' => $application->vacancy_id, 'document_type' => $docType]),
                    'remarks' => $doc ? ($doc->remarks ?: '') : '',
                    'original_name' => $doc->original_name ?? '',
                    'has_file' => $hasFile,
                    'last_modified_by' => $doc->last_modified_by ?? 'N/A',
                    'revision_requested_count' => $revisionRequestedCount,
                    'revision_submitted_at' => $revisionSubmittedAt,
                    'revision_locked' => !is_null($revisionLockReason),
                    'revision_lock_reason' => $revisionLockReason,
                    'isBold' => true,
                ];
            }
        }
        return $documents;
    }

    private function resolveUploadedDocument($uploadedDocuments, string $docType): ?UploadedDocument
    {
        $doc = $uploadedDocuments->get($docType);
        if ($doc && $doc->storage_path !== 'NOINPUT') {
            return $doc;
        }
        foreach (self::DOCUMENT_TYPE_ALIASES[$docType] ?? [] as $alias) {
            $aliasDoc = $uploadedDocuments->get($alias);
            if ($aliasDoc && $aliasDoc->storage_path !== 'NOINPUT') {
                return $aliasDoc;
            }
        }
        return $doc ?: null;
    }

    private function loadUploadedDocumentsMap(int $userId, ?string $vacancyId = null)
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $docsQuery = UploadedDocument::where('user_id', $userId);
        if ($supportsVacancyScopedDocs) {
            if (!empty($vacancyId)) {
                // Backward compatibility:
                // include legacy documents with null vacancy_id, but prioritize exact vacancy match.
                $docsQuery->where(function ($query) use ($vacancyId) {
                    $query->where('vacancy_id', $vacancyId)
                        ->orWhereNull('vacancy_id');
                });
                $docsQuery->orderByRaw("CASE WHEN vacancy_id = ? THEN 0 ELSE 1 END", [$vacancyId]);
            } else {
                $docsQuery->whereNull('vacancy_id');
            }
        }

        $docs = $docsQuery
            ->orderByDesc('updated_at')
            ->get();

        return $docs
            ->unique('document_type')
            ->keyBy('document_type');
    }

    private function loadReusableUploadedDocumentsMap(int $userId, ?string $vacancyId = null)
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');

        $docsQuery = UploadedDocument::where('user_id', $userId)
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');

        if ($supportsVacancyScopedDocs && !empty($vacancyId)) {
            $docsQuery->orderByRaw(
                "CASE WHEN vacancy_id = ? THEN 0 WHEN vacancy_id IS NULL THEN 1 ELSE 2 END",
                [(string) $vacancyId]
            );
        } elseif ($supportsVacancyScopedDocs) {
            $docsQuery->orderByRaw('CASE WHEN vacancy_id IS NULL THEN 0 ELSE 1 END');
        }

        $docs = $docsQuery
            ->orderByDesc('updated_at')
            ->get();

        return $docs
            ->unique('document_type')
            ->keyBy('document_type');
    }

    private function getRequiredDocsByTrack(): array
    {
        $allDocumentTypes = array_values(array_filter(
            UploadedDocument::DOCUMENTS,
            fn($doc) => $doc !== 'isApproved'
        ));

        return [
            'COS' => self::COS_REQUIRED_DOCUMENTS,
            'Plantilla' => array_values(array_diff(
                $allDocumentTypes,
                [
                    'tor_masteraldoctorate',
                    'grade_masteraldoctorate',
                    'cert_lgoo_induction',
                    'other_documents',
                    'pqe_result',
                    'ipcr',
                    'non_academic',
                    'designation_order',
                    'cert_employment',
                ]
            )),
        ];
    }

    private function getSupportingDocumentTypes(): array
    {
        return array_values(array_filter(
            UploadedDocument::DOCUMENTS,
            fn ($doc) => $doc !== 'isApproved'
        ));
    }

    private function normalizeSupportingDocumentSelection($selection): array
    {
        if (is_string($selection)) {
            $decodedSelection = json_decode($selection, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $selection = $decodedSelection;
            }
        }

        if (!is_array($selection)) {
            return [];
        }

        $allowedTypes = array_fill_keys($this->getSupportingDocumentTypes(), true);
        $normalizedSelection = [];

        foreach ($selection as $documentType) {
            $documentType = (string) $documentType;
            if (isset($allowedTypes[$documentType])) {
                $normalizedSelection[] = $documentType;
            }
        }

        return array_values(array_unique($normalizedSelection));
    }

    private function resolveVacancySupportingDocumentSelection(?JobVacancy $vacancy = null): array
    {
        if (!$vacancy) {
            return [];
        }

        if ($vacancy->supporting_documents_required !== null) {
            return $this->normalizeSupportingDocumentSelection($vacancy->supporting_documents_required);
        }

        if (!Schema::hasTable('vacancy_titles')) {
            return [];
        }

        $positionTitleCandidates = array_values(array_unique(array_filter([
            trim((string) $vacancy->getRawOriginal('position_title')),
            trim((string) $vacancy->position_title),
        ])));

        if (empty($positionTitleCandidates)) {
            return [];
        }

        $normalizedTrack = $this->normalizeTrack($vacancy->vacancy_type ?? null);
        $templateVacancy = VacancyTitle::query()
            ->where(function ($query) use ($positionTitleCandidates) {
                foreach ($positionTitleCandidates as $index => $positionTitle) {
                    if ($index === 0) {
                        $query->where('position_title', $positionTitle);
                    } else {
                        $query->orWhere('position_title', $positionTitle);
                    }
                }
            })
            ->whereRaw("UPPER(TRIM(COALESCE(vacancy_type, ''))) = ?", [strtoupper($normalizedTrack)])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (!$templateVacancy || $templateVacancy->supporting_documents_required === null) {
            return [];
        }

        return $this->normalizeSupportingDocumentSelection($templateVacancy->supporting_documents_required);
    }

    private function getRequiredDocumentIdsForVacancy(?JobVacancy $vacancy = null): array
    {
        $requiredDocumentIds = $this->resolveVacancySupportingDocumentSelection($vacancy);

        if (empty($requiredDocumentIds)) {
            $requiredDocumentIds = $this->getRequiredDocsByTrack()[$this->normalizeTrack($vacancy?->vacancy_type)] ?? [];
        }

        usort($requiredDocumentIds, function ($a, $b) {
            $labelA = strtolower(self::DOCUMENT_LABELS[$a] ?? $a);
            $labelB = strtolower(self::DOCUMENT_LABELS[$b] ?? $b);
            return $labelA <=> $labelB;
        });

        return array_values(array_unique($requiredDocumentIds));
    }

    private function normalizeTrack(?string $track): string
    {
        return strcasecmp((string) $track, 'COS') === 0 ? 'COS' : 'Plantilla';
    }

    private function hasRevisionDeadlinePassed(?Applications $application): bool
    {
        if (!$application || empty($application->deadline_date) || empty($application->deadline_time)) {
            return false;
        }

        try {
            $deadline = Carbon::parse($application->deadline_date . ' ' . $application->deadline_time);
            return now()->greaterThan($deadline);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function getNeedsRevisionRestrictionReason(
        ?Applications $application,
        int $revisionRequestedCount,
        ?string $revisionSubmittedAt,
        bool $allowExistingActiveCycle = false
    ): ?string
    {
        $hasActiveRevisionCycle = $revisionRequestedCount >= 1 && empty($revisionSubmittedAt);
        if ($allowExistingActiveCycle && $hasActiveRevisionCycle) {
            return null;
        }

        if ($this->hasRevisionDeadlinePassed($application)) {
            return 'Cannot set Needs Revision because the revision deadline has already passed.';
        }

        if ($revisionRequestedCount >= 2) {
            return 'Cannot set Needs Revision again. This application has already exhausted the final revision opportunity.';
        }

        if ($hasActiveRevisionCycle) {
            return 'Needs Revision is already active for this document. Wait for the applicant to submit a revised file before setting it again.';
        }

        return null;
    }

    private function getUploadedDocumentRevisionState(int $userId, string $vacancyId, string $docType, ?UploadedDocument $primaryDoc = null): array
    {
        $requestedCount = (int) ($primaryDoc->revision_requested_count ?? 0);
        $submittedAt = $primaryDoc->revision_submitted_at ?? null;
        $hadRevisionStatus = in_array(
            strtolower(trim((string) ($primaryDoc->status ?? ''))),
            ['needs revision', 'disapproved with deficiency'],
            true
        );

        if (!Schema::hasColumn('uploaded_documents', 'revision_requested_count')) {
            return [
                'requested_count' => $requestedCount,
                'submitted_at' => $submittedAt,
            ];
        }

        $query = UploadedDocument::query()
            ->where('user_id', $userId)
            ->where('document_type', $docType);

        if (Schema::hasColumn('uploaded_documents', 'vacancy_id') && $vacancyId !== '') {
            $query->where(function ($q) use ($vacancyId) {
                $q->where('vacancy_id', $vacancyId)
                    ->orWhereNull('vacancy_id');
            });
        }

        $rows = $query->get(['revision_requested_count', 'revision_submitted_at', 'status']);
        foreach ($rows as $row) {
            $requestedCount = max($requestedCount, (int) ($row->revision_requested_count ?? 0));
            if (empty($submittedAt) && !empty($row->revision_submitted_at)) {
                $submittedAt = $row->revision_submitted_at;
            }
            $rowStatus = strtolower(trim((string) ($row->status ?? '')));
            if (in_array($rowStatus, ['needs revision', 'disapproved with deficiency'], true)) {
                $hadRevisionStatus = true;
            }
        }

        // Backward-compatible fallback for legacy rows that predate revision tracking columns.
        if ($requestedCount < 1 && $hadRevisionStatus) {
            $requestedCount = 1;
        }

        return [
            'requested_count' => $requestedCount,
            'submitted_at' => $submittedAt,
        ];
    }

    private function markApplicationFileRevisionRequested(Applications $application): void
    {
        if (!Schema::hasColumn('applications', 'file_revision_requested_count')) {
            return;
        }

        $currentCount = (int) ($application->file_revision_requested_count ?? 0);
        $nextCount = min(2, max(0, $currentCount) + 1);

        if ($nextCount !== $currentCount) {
            $application->file_revision_requested_count = $nextCount;
        }

        if (Schema::hasColumn('applications', 'file_revision_requested_at')) {
            $application->file_revision_requested_at = now();
        }
    }

    private function markUploadedDocumentRevisionRequested(int $userId, string $vacancyId, string $docType): void
    {
        if (!Schema::hasColumn('uploaded_documents', 'revision_requested_count')) {
            return;
        }

        $query = UploadedDocument::query()
            ->where('user_id', $userId)
            ->where('document_type', $docType);

        if (Schema::hasColumn('uploaded_documents', 'vacancy_id') && $vacancyId !== '') {
            $query->where(function ($q) use ($vacancyId) {
                $q->where('vacancy_id', $vacancyId)
                    ->orWhereNull('vacancy_id');
            });
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return;
        }

        $currentMaxCount = (int) $rows->max(function ($row) {
            return (int) ($row->revision_requested_count ?? 0);
        });
        $targetCount = min(2, max(0, $currentMaxCount) + 1);

        foreach ($rows as $row) {
            $dirty = false;
            if ((int) ($row->revision_requested_count ?? 0) < $targetCount) {
                $row->revision_requested_count = $targetCount;
                $dirty = true;
            }
            if (Schema::hasColumn('uploaded_documents', 'revision_requested_at')) {
                $row->revision_requested_at = now();
                $dirty = true;
            }
            if ($dirty) {
                $row->save();
            }
        }
    }

    private function sortDocumentsAscending(array $documents): array
    {
        usort($documents, function ($a, $b) {
            $labelA = strtolower((string) ($a['text'] ?? $a['name'] ?? $a['id'] ?? ''));
            $labelB = strtolower((string) ($b['text'] ?? $b['name'] ?? $b['id'] ?? ''));
            return $labelA <=> $labelB;
        });

        return $documents;
    }

    private function isDocumentSubmitted(array $doc): bool
    {
        $status = (string) ($doc['status'] ?? 'Not Submitted');
        $hasFile = (bool) ($doc['has_file'] ?? false);

        if ($doc['id'] === 'application_letter') {
            return $hasFile && $status !== 'Not Submitted';
        }

        return $hasFile;
    }

    private function filterDocumentsForNotify(array $documents, array $requiredDocumentIds): array
    {
        $requiredLookup = array_fill_keys($requiredDocumentIds, true);

        $filtered = array_values(array_filter($documents, function ($doc) use ($requiredLookup) {
            $docId = (string) ($doc['id'] ?? '');
            if ($docId === '') {
                return false;
            }

            if (isset($requiredLookup[$docId])) {
                return true;
            }

            // Optional documents: include only when a file was actually submitted.
            return $this->isDocumentSubmitted($doc);
        }));

        return $this->sortDocumentsAscending($filtered);
    }

    public function viewApplicantStatus($user_id, $vacancy_id)
    {
        if ($accessDeniedResponse = $this->denyHrDivisionVacancyAccess(request(), (string) $vacancy_id)) {
            return $accessDeniedResponse;
        }

        $application = Applications::with(['personalInformation', 'vacancy', 'user'])
            ->where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->first();

        if (!$application || !$application->vacancy) {
            abort(404, 'Application or vacancy not found.');
        }

        $pi = $application->personalInformation;
        $vacancy = $application->vacancy;

        // Get name from PDS if available, otherwise fall back to user's name
        $formattedName = $pi
            ? trim(
                $pi->first_name . ' ' .
                ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                $pi->surname . ' ' .
                $pi->name_extension
            )
            : ($application->user ? $application->user->name : 'N/A');

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();
        $adminName = $application->updatedByAdmin?->username ?? null;

        $documents = $this->sortDocumentsAscending($this->getApplicantDocuments($user_id, $application));
        $isCancelledApplication = $this->isCancelledApplication($application);
        $requiredDocumentIds = $this->getRequiredDocumentIdsForVacancy($vacancy);

        activity()
            ->causedBy(auth('admin')->user())
            ->performedOn($application)
            ->event('view')
            ->withProperties(['user_id' => $user_id, 'vacancy_id' => $vacancy_id, 'section' => 'Application List'])
            ->log('Viewed applicant status.');


        return view('admin.applicant_status', [
            'applicant_name' => $formattedName,
            'place_of_assignment' => $vacancy->place_of_assignment,
            'compensation' => $vacancy->monthly_salary,
            'job_applied' => $vacancy->position_title,
            'user_id' => $user_id,
            'vacancy_id' => $vacancy_id,
            'application' => $application,
            'examDetail' => $examDetail,
            'documents' => $documents,
            'admin_name' => $adminName,
            'vacancy_type' => $vacancy->vacancy_type, // Needed for Phase 4
            'requiredDocumentIds' => $requiredDocumentIds,
            'isCancelledApplication' => $isCancelledApplication,
        ]);
    }

    public function updateApplicantStatus(Request $request, $user_id, $vacancy_id)
    {
        if ($accessDeniedResponse = $this->denyHrDivisionVacancyAccess($request, (string) $vacancy_id)) {
            return $accessDeniedResponse;
        }

        $request->validate([
            'status' => 'required|string',
            'deadline_date' => [
                'nullable',
                'date',
                Rule::requiredIf(function () use ($request) {
                    return strcasecmp((string) $request->input('qs_result'), 'Needs Revisions') === 0;
                }),
            ],
            'deadline_time' => 'nullable|date_format:H:i',
            'qs_education' => 'nullable|string',
            'qs_eligibility' => 'nullable|string',
            'qs_experience' => 'nullable|string',
            'qs_training' => 'nullable|string',
            'qs_result' => ['nullable', 'string', Rule::in(['Qualified', 'Needs Revisions', 'Not Qualified'])],
            'application_remarks' => 'nullable|string',
        ]);

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();

        if ($this->isCancelledApplication($application)) {
            return $this->cancelledApplicationActionResponse($request);
        }

        $supportsAppRevisionTracking = Schema::hasColumn('applications', 'file_revision_requested_count')
            && Schema::hasColumn('applications', 'file_revision_requested_at');
        $supportsDocRevisionTracking = Schema::hasColumn('uploaded_documents', 'revision_requested_count')
            && Schema::hasColumn('uploaded_documents', 'revision_requested_at');

        $qsFields = ['qs_education', 'qs_eligibility', 'qs_experience', 'qs_training', 'qs_result'];
        $qsExplicitlyProvided = collect($qsFields)->contains(fn($field) => $request->has($field));

        $documentStatuses = $request->input('document_statuses', []);
        $documentRemarks = $request->input('document_remarks', []);

        // Track changes
        $changes = [];

        // Compare and store changed application fields
        $fieldsToCheck = [
            'status',
            'deadline_date',
            'deadline_time',
            'qs_education',
            'qs_eligibility',
            'qs_experience',
            'qs_training',
            'qs_result',
            'application_remarks',
        ];


        foreach ($fieldsToCheck as $field) {
            $newValue = $request->input($field);
            $oldValue = $application->$field;

            // Special formatting for time comparison
            if ($field === 'deadline_time') {
                $newValue = $newValue ? date('H:i', strtotime($newValue)) : null;
                $oldValue = $oldValue ? date('H:i', strtotime($oldValue)) : null;
            }

            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
                $application->$field = $newValue;
            }
        }

        // Recalculate QS when admin did not explicitly provide QS values.
        // Manual overrides are still respected when QS fields are included in the request.
        if (!$qsExplicitlyProvided) {
            $calculatedQs = $this->recalculateQualificationStatus((int) $user_id, (string) $vacancy_id);
            foreach ($calculatedQs as $field => $value) {
                $oldValue = $application->$field;
                if ($oldValue !== $value) {
                    $changes[$field] = [
                        'old' => $oldValue,
                        'new' => $value,
                    ];
                    $application->$field = $value;
                }
            }
        }

        // Application letter status and remarks
        $file_status = $documentStatuses['application_letter'] ?? null;
        $file_remarks = $documentRemarks['application_letter'] ?? null;
        $isFileNeedsRevision = in_array(strtolower(trim((string) $file_status)), ['needs revision', 'disapproved with deficiency'], true);
        $fileRevisionRequestedCount = (int) ($application->file_revision_requested_count ?? 0);
        $fileRevisionSubmittedAt = $application->file_revision_submitted_at ?? null;
        $hasActiveFileRevisionCycle = $fileRevisionRequestedCount >= 1 && empty($fileRevisionSubmittedAt);

        if ($isFileNeedsRevision) {
            $fileRestrictionReason = $this->getNeedsRevisionRestrictionReason(
                $application,
                $fileRevisionRequestedCount,
                $fileRevisionSubmittedAt,
                true
            );
            if (!is_null($fileRestrictionReason)) {
                return redirect()->back()->with('error', $fileRestrictionReason)->withInput();
            }
            if ($supportsAppRevisionTracking && !$hasActiveFileRevisionCycle) {
                $this->markApplicationFileRevisionRequested($application);
            }
        }

        if ($application->file_status !== $file_status) {
            $changes['application_letter_status'] = [
                'old' => $application->file_status,
                'new' => $file_status
            ];
            $application->file_status = $file_status;
        }

        if ($application->file_remarks !== $file_remarks) {
            $changes['application_letter_remarks'] = [
                'old' => $application->file_remarks,
                'new' => $file_remarks
            ];
            $application->file_remarks = $file_remarks;
        }

        $application->updated_by_admin_id = Auth::guard('admin')->id();
        $application->save();

        // Update Uploaded Documents and track changes
        $uploadedDocuments = $this->loadUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
        foreach ($documentStatuses as $docType => $status) {
            if ($docType === 'application_letter') {
                continue;
            }

            $document = $this->resolveUploadedDocument($uploadedDocuments, $docType);

            if ($document) {
                $doc_changes = [];
                $isDocNeedsRevision = in_array(strtolower(trim((string) $status)), ['needs revision', 'disapproved with deficiency'], true);
                $docRevisionState = null;
                $hasActiveDocRevisionCycle = false;
                if ($isDocNeedsRevision) {
                    $docRevisionState = $this->getUploadedDocumentRevisionState(
                        (int) $user_id,
                        (string) $vacancy_id,
                        (string) $docType,
                        $document
                    );
                    $docRevisionRequestedCount = (int) ($docRevisionState['requested_count'] ?? 0);
                    $docRevisionSubmittedAt = $docRevisionState['submitted_at'] ?? null;
                    $hasActiveDocRevisionCycle = $docRevisionRequestedCount >= 1 && empty($docRevisionSubmittedAt);
                    $docRestrictionReason = $this->getNeedsRevisionRestrictionReason(
                        $application,
                        $docRevisionRequestedCount,
                        $docRevisionSubmittedAt,
                        true
                    );
                    if (!is_null($docRestrictionReason)) {
                        return redirect()->back()->with('error', $docRestrictionReason)->withInput();
                    }
                }

                if ($document->status !== $status) {
                    $doc_changes['status'] = [
                        'old' => $document->status,
                        'new' => $status
                    ];
                    $document->status = $status;
                }

                $newRemark = $documentRemarks[$docType] ?? null;
                if ($document->remarks !== $newRemark) {
                    $doc_changes['remarks'] = [
                        'old' => $document->remarks,
                        'new' => $newRemark
                    ];
                    $document->remarks = $newRemark;
                }

                if (!empty($doc_changes)) {
                    $changes["document_$docType"] = $doc_changes;
                    $document->save();
                }

                if ($isDocNeedsRevision && $supportsDocRevisionTracking && !$hasActiveDocRevisionCycle) {
                    $this->markUploadedDocumentRevisionRequested((int) $user_id, (string) $vacancy_id, (string) $docType);
                }
            }
        }

        //dd($changes);

        // Notify other admins if there are changes
        // Notify other admins if there are changes
        if (!empty($changes)) {
            $admins = Admin::where('id', '!=', Auth::guard('admin')->id())->get();
            $applicantName = User::find($user_id)->name ?? 'Applicant';
            $positionTitle = JobVacancy::where('vacancy_id', $vacancy_id)->value('position_title');

            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\ApplicantRecordModifiedNotification(
                    Auth::guard('admin')->user()->name,
                    $applicantName,
                    $changes,
                    $positionTitle,
                    $user_id,
                    $vacancy_id
                ));
            }
        }

        // Log only if there are changes
        if (!empty($changes)) {
            activity()
                ->causedBy(auth('admin')->user())
                ->performedOn(User::find($user_id))
                ->event('update')
                ->withProperties([
                    'user_id' => $user_id,
                    'vacancy_id' => $vacancy_id,
                    'changes' => $changes,
                    'section' => 'Application List'
                ])
                ->log('Updated applicant status and documents.');
        }

        return redirect()->back()->with('success', 'Changes updated successfully.');
    }

    public function updateDocumentStatusAjax(Request $request, $user_id, $vacancy_id)
    {
        if ($accessDeniedResponse = $this->denyHrDivisionVacancyAccess($request, (string) $vacancy_id)) {
            return $accessDeniedResponse;
        }

        $request->validate([
            'document_type' => 'required|string',
            'status' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $documentType = $request->input('document_type');
        $status = $request->input('status');
        $remarks = $request->input('remarks');

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();

        if ($this->isCancelledApplication($application)) {
            return $this->cancelledApplicationActionResponse($request);
        }

        // Get applicant name
        $applicantName = User::find($user_id)->name ?? 'Applicant';
        $docName = $documentType === 'application_letter' ? 'Application Letter' : ucwords(str_replace('_', ' ', $documentType));

        $now = now()->timezone('Asia/Manila')->format('M d, Y h:i A');
        $adminName = Auth::guard('admin')->user()->name;
        $lastModifiedStr = "{$adminName} on {$now}";
        $normalizedStatus = strtolower(trim((string) $status));
        $isNeedsRevisionStatus = in_array($normalizedStatus, ['needs revision', 'disapproved with deficiency'], true);
        $supportsAppRevisionTracking = Schema::hasColumn('applications', 'file_revision_requested_count')
            && Schema::hasColumn('applications', 'file_revision_requested_at')
            && Schema::hasColumn('applications', 'file_revision_submitted_at');
        $supportsDocRevisionTracking = Schema::hasColumn('uploaded_documents', 'revision_requested_count')
            && Schema::hasColumn('uploaded_documents', 'revision_requested_at')
            && Schema::hasColumn('uploaded_documents', 'revision_submitted_at');

        if ($documentType === 'application_letter') {
            $fileRevisionRequestedCount = (int) ($application->file_revision_requested_count ?? 0);
            $fileRevisionSubmittedAt = $application->file_revision_submitted_at ?? null;
            $hasActiveFileRevisionCycle = $fileRevisionRequestedCount >= 1 && empty($fileRevisionSubmittedAt);

            if ($request->has('status') && $isNeedsRevisionStatus) {
                $restrictionReason = $this->getNeedsRevisionRestrictionReason(
                    $application,
                    $fileRevisionRequestedCount,
                    $fileRevisionSubmittedAt,
                    true
                );
                if (!is_null($restrictionReason)) {
                    return response()->json([
                        'success' => false,
                        'message' => $restrictionReason,
                    ], 422);
                }
            }

            if ($request->has('status'))
                $application->file_status = $status;
            if ($request->has('remarks'))
                $application->file_remarks = $remarks;

            if ($request->has('status') && $isNeedsRevisionStatus && $supportsAppRevisionTracking && !$hasActiveFileRevisionCycle) {
                $this->markApplicationFileRevisionRequested($application);
            }

            // Update last modified by
            $application->file_last_modified_by = $lastModifiedStr;

            $application->save();

        } else {
            $uploadedDocuments = $this->loadUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
            $document = $this->resolveUploadedDocument($uploadedDocuments, $documentType);
            $revisionState = null;
            $hasActiveDocRevisionCycle = false;

            if ($request->has('status') && $isNeedsRevisionStatus) {
                $revisionState = $this->getUploadedDocumentRevisionState(
                    (int) $user_id,
                    (string) $vacancy_id,
                    (string) $documentType,
                    $document
                );
                $docRevisionRequestedCount = (int) ($revisionState['requested_count'] ?? 0);
                $docRevisionSubmittedAt = $revisionState['submitted_at'] ?? null;
                $hasActiveDocRevisionCycle = $docRevisionRequestedCount >= 1 && empty($docRevisionSubmittedAt);
                $restrictionReason = $this->getNeedsRevisionRestrictionReason(
                    $application,
                    $docRevisionRequestedCount,
                    $docRevisionSubmittedAt,
                    true
                );
                if (!is_null($restrictionReason)) {
                    return response()->json([
                        'success' => false,
                        'message' => $restrictionReason,
                    ], 422);
                }
            }

            if ($document) {
                if ($request->has('status'))
                    $document->status = $status;

                // Only update remarks if explicitly provided, but force it to empty string if verified
                // or ensure it is not null if it is being updated
                if ($request->has('remarks')) {
                    $document->remarks = $remarks ?? '';
                } elseif ($status === 'Verified') {
                    // When verifying, we often clear remarks, but we must ensure we don't save NULL
                    $document->remarks = '';
                }

                // Update last modified by
                $document->last_modified_by = $lastModifiedStr;

                $document->save();
                if ($request->has('status') && $isNeedsRevisionStatus && $supportsDocRevisionTracking && !$hasActiveDocRevisionCycle) {
                    $this->markUploadedDocumentRevisionRequested((int) $user_id, (string) $vacancy_id, (string) $documentType);
                }
            } else {
                // If document doesn't exist, create a placeholder record so status/remarks can be saved
                // This handles cases where admin wants to mark a missing document as "Needs Revision" or add remarks
                $createPayload = [
                    'user_id' => $user_id,
                    'document_type' => $documentType,
                    'status' => $status ?? 'Pending',
                    'remarks' => $remarks ?? '',
                    'last_modified_by' => $lastModifiedStr, // Add last modified by
                    'original_name' => '', // Placeholder
                    'stored_name' => '',   // Placeholder
                    'storage_path' => '',  // Placeholder
                    'mime_type' => '',     // Placeholder
                    'file_size_8b' => 0,   // Placeholder
                ];
                if (Schema::hasColumn('uploaded_documents', 'vacancy_id')) {
                    $createPayload['vacancy_id'] = $vacancy_id;
                }
                if ($supportsDocRevisionTracking && $request->has('status') && $isNeedsRevisionStatus) {
                    $createPayload['revision_requested_count'] = 1;
                    $createPayload['revision_requested_at'] = now();
                }
                UploadedDocument::create($createPayload);
            }

        }

        // Notify other admins if status changed
        // Notify other admins if status changed
        if ($request->has('status')) {
            $admins = Admin::where('id', '!=', Auth::guard('admin')->id())->get();
            $positionTitle = JobVacancy::where('vacancy_id', $vacancy_id)->value('position_title');

            $changesOrMessage = [
                "$docName Status" => $status
            ];

            // Should potentially include remarks if changed
            if ($request->has('remarks') && !empty($remarks)) {
                $changesOrMessage["$docName Remarks"] = $remarks;
            }

            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\ApplicantRecordModifiedNotification(
                    Auth::guard('admin')->user()->name,
                    $applicantName,
                    $changesOrMessage,
                    $positionTitle,
                    $user_id,
                    $vacancy_id
                ));
            }
        }

        $revisionRequestedCount = 0;
        $revisionSubmittedAt = null;
        if ($documentType === 'application_letter') {
            $revisionRequestedCount = (int) ($application->file_revision_requested_count ?? 0);
            $revisionSubmittedAt = $application->file_revision_submitted_at ?? null;
        } else {
            $latestDocs = $this->loadUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
            $latestDoc = $this->resolveUploadedDocument($latestDocs, $documentType);
            $revisionState = $this->getUploadedDocumentRevisionState(
                (int) $user_id,
                (string) $vacancy_id,
                (string) $documentType,
                $latestDoc
            );
            $revisionRequestedCount = (int) ($revisionState['requested_count'] ?? 0);
            $revisionSubmittedAt = $revisionState['submitted_at'] ?? null;
        }
        $revisionLockReason = $this->getNeedsRevisionRestrictionReason(
            $application,
            $revisionRequestedCount,
            $revisionSubmittedAt,
            true
        );

        return response()->json([
            'success' => true,
            'revision_requested_count' => $revisionRequestedCount,
            'revision_submitted_at' => $revisionSubmittedAt,
            'revision_locked' => !is_null($revisionLockReason),
            'revision_lock_reason' => $revisionLockReason,
        ]);
    }

    /**
     * Get updated documents for AJAX refresh
     */
    public function getUpdatedDocuments(Request $request, $user_id, $vacancy_id)
    {
        if ($accessDeniedResponse = $this->denyHrDivisionVacancyAccess($request, (string) $vacancy_id)) {
            return $accessDeniedResponse;
        }

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->first();

        $documents = $this->sortDocumentsAscending($this->getApplicantDocuments($user_id, $application));

        return response()->json([
            'documents' => $documents,
            'application' => [
                'status' => $application->status ?? 'Pending',
                'is_cancelled' => $this->isCancelledApplication($application),
                'file_last_modified_by' => $application->file_last_modified_by ?? null,
                'deadline_date' => $application->deadline_date ?? null,
                'deadline_time' => $application->deadline_time ?? null,
            ]
        ]);
    }

    public function updateApplicationRemarksAjax(Request $request, $user_id, $vacancy_id)
    {
        if ($accessDeniedResponse = $this->denyHrDivisionVacancyAccess($request, (string) $vacancy_id)) {
            return $accessDeniedResponse;
        }

        $request->validate([
            'application_remarks' => 'nullable|string',
        ]);

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();

        if ($this->isCancelledApplication($application)) {
            return $this->cancelledApplicationActionResponse($request);
        }

        $application->application_remarks = $request->input('application_remarks');
        $application->updated_by_admin_id = Auth::guard('admin')->id();
        $application->save();

        // Notify other admins
        $admins = Admin::where('id', '!=', Auth::guard('admin')->id())->get();
        $applicantName = User::find($user_id)->name ?? 'Applicant';
        $positionTitle = JobVacancy::where('vacancy_id', $vacancy_id)->value('position_title');

        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\ApplicantRecordModifiedNotification(
                Auth::guard('admin')->user()->name,
                $applicantName,
                ['Application Remarks' => $request->input('application_remarks')],
                $positionTitle,
                $user_id,
                $vacancy_id
            ));
        }

        return response()->json(['success' => true]);
    }

    public function notifyApplicant(Request $request, $user_id, $vacancy_id)
    {
        if ($accessDeniedResponse = $this->denyHrDivisionVacancyAccess($request, (string) $vacancy_id)) {
            return $accessDeniedResponse;
        }

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();

        if ($this->isCancelledApplication($application)) {
            return $this->cancelledApplicationActionResponse($request);
        }

        // Update Deadline and QS data if provided in request
        // Only normalize empty strings when the field is explicitly submitted.
        // This prevents notify-only actions from wiping existing deadline values.
        if ($request->exists('deadline_date') && $request->input('deadline_date') === '') {
            $request->merge(['deadline_date' => null]);
        }
        if ($request->exists('deadline_time') && $request->input('deadline_time') === '') {
            $request->merge(['deadline_time' => null]);
        }

        $validatedData = $request->validate([
            'deadline_enabled' => 'nullable|boolean',
            'deadline_date' => [
                'nullable',
                'date',
                Rule::requiredIf(function () use ($request) {
                    return strcasecmp((string) $request->input('qs_result'), 'Needs Revisions') === 0;
                }),
            ],
            'deadline_time' => 'nullable|date_format:H:i',
            'qs_education' => 'nullable|string',
            'qs_eligibility' => 'nullable|string',
            'qs_experience' => 'nullable|string',
            'qs_training' => 'nullable|string',
            'qs_result' => ['required', 'string', Rule::in(['Qualified', 'Needs Revisions', 'Not Qualified'])],
        ]);

        $selectedQsResult = trim((string) ($validatedData['qs_result'] ?? 'Not Qualified'));
        if ($selectedQsResult === '') {
            $selectedQsResult = 'Not Qualified';
        }
        $selectedQsResultNormalized = strtolower($selectedQsResult);
        $deadlineEnabled = $selectedQsResultNormalized === 'needs revisions';

        if (!$deadlineEnabled) {
            $application->deadline_date = null;
            $application->deadline_time = null;
            $application->deadline_warning_sent_at = null;
        } else {
            if ($request->exists('deadline_date')) {
                $application->deadline_date = !empty($validatedData['deadline_date'])
                    ? $validatedData['deadline_date']
                    : null;
            }
            if ($request->exists('deadline_time')) {
                $application->deadline_time = !empty($validatedData['deadline_time'])
                    ? date('H:i', strtotime($validatedData['deadline_time']))
                    : '17:00';
            }
            $application->deadline_warning_sent_at = null;
        }

        foreach (['qs_education', 'qs_eligibility', 'qs_experience', 'qs_training', 'qs_result'] as $field) {
            if ($request->has($field)) {
                $application->$field = $validatedData[$field];
            }
        }
        $application->save();

        // We trust the HR's explicitly validated QS variables instead of overwriting them.
        // Removed recalculateQualificationStatus from here to respect the modal's selected radio buttons.

        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->first();
        $requiredDocumentIds = $this->getRequiredDocumentIdsForVacancy($vacancy);

        $documents = $this->sortDocumentsAscending($this->getApplicantDocuments($user_id, $application));
        $userDocumentsSnapshot = $this->sortDocumentsAscending($this->buildUserDocumentsSnapshot($user_id, $application));
        $notifyDocumentsSnapshot = $this->filterDocumentsForNotify($userDocumentsSnapshot, $requiredDocumentIds);

        // --- Calculate Progress (required docs only, aligned with admin progress UI) ---
        $requiredLookup = array_fill_keys($requiredDocumentIds, true);
        $requiredDocuments = array_values(array_filter($documents, function ($doc) use ($requiredLookup) {
            $docId = (string) ($doc['id'] ?? '');
            return $docId !== '' && isset($requiredLookup[$docId]);
        }));
        $progressSourceDocs = !empty($requiredDocuments) ? $requiredDocuments : $documents;
        $totalDocuments = count($progressSourceDocs);
        $verifiedCount = collect($progressSourceDocs)->whereIn('status', ['Verified', 'Okay/Confirmed'])->count();
        $progressPercentage = $totalDocuments > 0 ? round(($verifiedCount / $totalDocuments) * 100) : 0;
        $progressCount = "$verifiedCount/$totalDocuments";

        // --- Logic Check for Application Status Update ---
        $revisionStatusesNormalized = ['needs revision', 'disapproved with deficiency'];

        $hasFinalRevisionFailure = collect($documents)->contains(function ($doc) use ($revisionStatusesNormalized) {
            $status = strtolower(trim((string) ($doc['status'] ?? '')));
            $requestedCount = (int) ($doc['revision_requested_count'] ?? 0);
            return in_array($status, $revisionStatusesNormalized, true) && $requestedCount >= 2;
        });

        if ($hasFinalRevisionFailure) {
            $application->qs_result = 'Not Qualified';
            $application->deadline_date = null;
            $application->deadline_time = null;
        }

        $currentQsResultNormalized = strtolower(trim((string) ($application->qs_result ?? 'Not Qualified')));
        $isQualifiedByQs = !$hasFinalRevisionFailure && $currentQsResultNormalized === 'qualified';
        $isNeedsRevisionsByQs = !$hasFinalRevisionFailure && $currentQsResultNormalized === 'needs revisions';
        $isRejectedByQs = $hasFinalRevisionFailure || $currentQsResultNormalized === 'not qualified';

        if ($isRejectedByQs) {
            $application->qs_result = 'Not Qualified';
            $application->deadline_date = null;
            $application->deadline_time = null;
            $application->deadline_warning_sent_at = null;
        } elseif ($isNeedsRevisionsByQs) {
            $application->qs_result = 'Needs Revisions';
        }

        if ($isQualifiedByQs) {
            // Qualified applicants no longer need a compliance deadline.
            $application->deadline_date = null;
            $application->deadline_time = null;
            $application->deadline_warning_sent_at = null;
        }

        $complianceNoticeMode = $isRejectedByQs
            ? 'disqualified_final'
            : ($isNeedsRevisionsByQs ? 'final_warning' : 'default');

        // Logic:
        // 1. Not Qualified -> reject application.
        // 2. Qualified -> mark as qualified.
        // 3. Needs Revisions -> move to compliance stage.

        $statusTransitions = app(ApplicationStatusTransitionService::class);
        if ($isRejectedByQs) {
            $application->status = 'Not Qualified';
        } elseif ($isQualifiedByQs) {
            $application->status = ApplicationStatus::QUALIFIED->value;
        } elseif ($isNeedsRevisionsByQs) {
            if ($statusTransitions->canTransition($application->status, ApplicationStatus::COMPLIANCE->value)) {
                $application->status = ApplicationStatus::COMPLIANCE->value;
            } else {
                $application->status = ApplicationStatus::COMPLIANCE->value;
            }
        }

        $application->save();
        // -------------------------------------------------

        // --- Retrieve Job Vacancy Details ---
        $placeOfAssignment = $vacancy->place_of_assignment ?? 'N/A';
        $compensation = $vacancy->monthly_salary ?? 0;
        $vacancyType = $vacancy->vacancy_type ?? 'Plantilla';

        // --- Format Deadline ---
        $deadline = null;
        if ($application->deadline_date && $application->deadline_time) {
            try {
                $deadline = \Carbon\Carbon::parse($application->deadline_date . ' ' . $application->deadline_time)->format('F d, Y h:i A');
            } catch (\Exception $e) {
                $deadline = 'No deadline set';
            }
        } else {
            $deadline = 'No deadline set';
        }

        // --- Retrieve Qualification Standards ---
        $qsEducation = $application->qs_education ?? 'no';
        $qsEligibility = $application->qs_eligibility ?? 'no';
        $qsExperience = $application->qs_experience ?? 'no';
        $qsTraining = $application->qs_training ?? 'no';
        $qsResult = $application->qs_result ?? 'Not Qualified';

        $userEmail = User::where('id', $user_id)->value('email');

        if (!$userEmail) {
            return response()->json(['success' => false, 'message' => 'User email not found.'], 404);
        }

        $messageTitle = 'Application Status Update';
        $messageBody = 'Your application has been reviewed. Please see the updated status.';
        $messageLevel = 'info';
        if ($isRejectedByQs) {
            $messageTitle = 'Application Not Qualified';
            $messageBody = 'I am sorry to inform you that, you are not qualified for this position.';
            $messageLevel = 'error';
        } elseif ($isNeedsRevisionsByQs) {
            $messageTitle = 'Final Opportunity to Comply';
            $messageBody = "This is your final opportunity to comply. Once your document/s are marked as 'Needs Revision' again, you will be considered not qualified and will no longer have the opportunity to comply again.";
            $messageLevel = 'warning';
        } elseif ($isQualifiedByQs) {
            $messageTitle = 'Application Qualified';
            $messageBody = 'Your application has been marked as qualified.';
            $messageLevel = 'success';
        }

        Notification::create([
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user_id,
            'type' => 'info',
            'created_at' => now(),
            'updated_at' => now(),
            'data' => [
                'type' => 'application_overview',
                'vacancy_id' => $vacancy_id,
                'title' => $messageTitle,
                'message' => $messageBody,
                'level' => $messageLevel,
                'action_url' => route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id], false),
                'documents' => $notifyDocumentsSnapshot,
                'application_status' => $application->status,
                'application_remarks' => $application->application_remarks,
                'qs_education' => $qsEducation,
                'qs_eligibility' => $qsEligibility,
                'qs_experience' => $qsExperience,
                'qs_training' => $qsTraining,
                'qs_result' => $qsResult,
                'deadline_date' => $application->deadline_date,
                'deadline_time' => $application->deadline_time,
                'compliance_notice_mode' => $complianceNoticeMode,
                'last_modified_by' => Auth::guard('admin')->user()->name,
                'notified_at' => now()->toDateTimeString()
            ]
        ]);

        // Notify all admins (except actor) about the notification sent to applicant
        $admins = Admin::where('id', '!=', Auth::guard('admin')->id())->get();
        $actorName = Auth::guard('admin')->user()->name;
        $applicantName = User::where('id', $user_id)->value('name');
        $positionTitle = JobVacancy::where('vacancy_id', $vacancy_id)->value('position_title');

        $timezone = config('app.timezone') ?: 'UTC';
        $timestamp = now()->timezone($timezone)->format('Y-m-d H:i:s T');

        $verifiedDocs = array_values(array_filter($userDocumentsSnapshot, function ($d) {
            $status = strtoupper((string) ($d['status'] ?? ''));
            return in_array($status, ['VERIFIED', 'NEEDS REVISION']);
        }));

        $adminMailPayloads = [];
        foreach ($admins as $admin) {
            Notification::create([
                'notifiable_type' => 'App\Models\Admin',
                'notifiable_id' => $admin->id,
                'type' => 'info',
                'created_at' => now(),
                'updated_at' => now(),
                'data' => [
                    'title' => 'Applicant Notified',
                    'message' => $actorName . ' notified ' . ($applicantName ?: 'Applicant'),
                    'link' => route('admin.applicant_status', ['user_id' => $user_id, 'vacancy_id' => $vacancy_id], false),
                ]
            ]);

            if ($admin->email) {
                $adminMailPayloads[] = [
                    'email' => $admin->email,
                    'actor_name' => $actorName,
                    'applicant_name' => $applicantName,
                    'vacancy_id' => $vacancy_id,
                    'position_title' => $positionTitle,
                    'documents' => $verifiedDocs,
                    'timestamp' => $timestamp,
                    'timezone' => $timezone,
                ];
            }
        }

        (new SendApplicantNotificationEmails(
            $userEmail,
            [
                'user_id' => $user_id,
                'vacancy_id' => $vacancy_id,
                'notify_documents_snapshot' => $userDocumentsSnapshot,
                'application_remarks' => $application->application_remarks,
                'place_of_assignment' => $placeOfAssignment,
                'compensation' => $compensation,
                'deadline' => $deadline,
                'qs_education' => $qsEducation,
                'qs_eligibility' => $qsEligibility,
                'qs_experience' => $qsExperience,
                'qs_training' => $qsTraining,
                'qs_result' => $qsResult,
                'compliance_notice_mode' => $complianceNoticeMode,
                'progress_percentage' => $progressPercentage,
                'progress_count' => $progressCount,
                'vacancy_type' => $vacancyType,
                'reviewer_name' => Auth::guard('admin')->user()->name,
            ],
            $adminMailPayloads
        ))->handle();

        return response()->json([
            'success' => true,
            'message' => 'Applicant notified successfully. Email sent directly.',
        ]);
    }

    private function buildUserDocumentsSnapshot($user_id, $application): array
    {
        $uploadedDocuments = $this->loadUploadedDocumentsMap((int) $user_id, (string) $application->vacancy_id);
        $reusableDocuments = $this->loadReusableUploadedDocumentsMap((int) $user_id, (string) $application->vacancy_id);
        $documents = [];

        foreach (UploadedDocument::DOCUMENTS as $docType) {
            if ($docType === 'isApproved')
                continue;

            if ($docType === 'application_letter') {
                $hasFile = !empty($application->file_storage_path);
                $documents[] = [
                    'id' => 'application_letter',
                    'doc_id' => null,
                    'name' => self::DOCUMENT_LABELS['application_letter'],
                    'text' => self::DOCUMENT_LABELS['application_letter'],
                    'status' => $application->file_status ?? 'Not Submitted',
                    'preview' => PreviewUrl::forPath($application->file_storage_path),
                    'remarks' => $application->file_remarks ?? '',
                    'original_name' => $application->file_original_name ?? '',
                    'has_file' => $hasFile,
                    'last_modified_by' => $application->file_last_modified_by ?? null,
                    'isBold' => true,
                ];
                continue;
            }

            $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
            $hasFile = $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
            if (!$hasFile) {
                $fallbackDoc = $this->resolveUploadedDocument($reusableDocuments, $docType);
                $fallbackHasFile = $fallbackDoc && !empty($fallbackDoc->storage_path) && $fallbackDoc->storage_path !== 'NOINPUT';
                if ($fallbackHasFile) {
                    $doc = $fallbackDoc;
                    $hasFile = true;
                }
            }
            $docStatus = trim((string) ($doc->status ?? ''));
            $isRevisionStatus = in_array(strtolower($docStatus), ['needs revision', 'disapproved with deficiency'], true);
            $documents[] = [
                'id' => $docType,
                'doc_id' => $doc->id ?? null,
                'name' => self::DOCUMENT_LABELS[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                'text' => self::DOCUMENT_LABELS[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                'status' => $hasFile ? ($doc->status ?? 'Pending') : ($isRevisionStatus ? $doc->status : 'Not Submitted'),
                'preview' => $hasFile ? PreviewUrl::forPath($doc->storage_path) : '',
                'remarks' => $doc ? ($doc->remarks ?: '') : '',
                'original_name' => $doc->original_name ?? '',
                'has_file' => $hasFile,
                'last_modified_by' => $doc->last_modified_by ?? null,
                'isBold' => true,
            ];
        }

        return $documents;
    }

    private function recalculateQualificationStatus(int $userId, string $vacancyId): array
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancyId)->first();
        if (!$vacancy) {
            return [
                'qs_education' => 'na',
                'qs_eligibility' => 'na',
                'qs_experience' => 'na',
                'qs_training' => 'na',
                'qs_result' => 'Qualified',
            ];
        }

        $qualificationGate = app(JobVacancyController::class)->evaluateQualificationGateForApplicant($userId, $vacancy);
        $checks = (array) ($qualificationGate['checks'] ?? []);

        $educationCheck = (array) ($checks['education'] ?? []);
        $eligibilityCheck = (array) ($checks['eligibility'] ?? []);
        $experienceCheck = (array) ($checks['experience'] ?? []);
        $trainingCheck = (array) ($checks['training'] ?? []);

        $educationMet = !($educationCheck['required'] ?? false)
            ? 'na'
            : (($educationCheck['met'] ?? false) ? 'yes' : 'no');
        $eligibilityMet = !($eligibilityCheck['required'] ?? false)
            ? 'na'
            : (($eligibilityCheck['met'] ?? false) ? 'yes' : 'no');
        $experienceMet = !($experienceCheck['required'] ?? false)
            ? 'na'
            : (($experienceCheck['met'] ?? false) ? 'yes' : 'no');
        $trainingMet = !($trainingCheck['required'] ?? false)
            ? 'na'
            : (($trainingCheck['met'] ?? false) ? 'yes' : 'no');

        $requiredStatuses = collect([$educationMet, $eligibilityMet, $experienceMet, $trainingMet])
            ->filter(fn($value) => $value !== 'na');
        $qsResult = $requiredStatuses->isEmpty() || $requiredStatuses->every(fn($value) => $value === 'yes')
            ? 'Qualified'
            : 'Not Qualified';

        return [
            'qs_education' => $educationMet,
            'qs_eligibility' => $eligibilityMet,
            'qs_experience' => $experienceMet,
            'qs_training' => $trainingMet,
            'qs_result' => $qsResult,
        ];
    }

    private function normalizeRequirement(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $lower = strtolower($value);
        if (in_array($lower, ['na', 'n/a', 'none', 'not applicable', '-'], true)) {
            return null;
        }
        return $value;
    }

    private function parseRequirementMonths(string $value): ?int
    {
        $lower = strtolower($value);
        if (preg_match('/(\d+(?:\.\d+)?)/', $lower, $matches)) {
            $amount = (float) $matches[1];
            if (str_contains($lower, 'month')) {
                return (int) round($amount);
            }
            if (str_contains($lower, 'year')) {
                return (int) round($amount * 12);
            }
        }
        return null;
    }

    private function parseRequirementHours(string $value): ?int
    {
        $lower = strtolower($value);
        if (preg_match('/(\d+(?:\.\d+)?)/', $lower, $matches)) {
            $amount = (float) $matches[1];
            return (int) round($amount);
        }
        return null;
    }

    private function arrayHasValue($array): bool
    {
        if (!is_array($array)) {
            return false;
        }
        foreach ($array as $value) {
            if (is_array($value)) {
                if ($this->arrayHasValue($value)) {
                    return true;
                }
            } elseif (is_scalar($value) && trim((string) $value) !== '') {
                return true;
            }
        }
        return false;
    }

    public function previewDocument($user_id, $vacancy_id, $document_type)
    {
        if ($accessDeniedResponse = $this->denyHrDivisionVacancyAccess(request(), (string) $vacancy_id)) {
            return $accessDeniedResponse;
        }

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->first();

        if (!$application) {
            abort(404);
        }

        $path = null;

        if ($document_type === 'application_letter') {
            $path = $application->file_storage_path;
        } else {
            $uploadedDocuments = $this->loadUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
            $doc = $this->resolveUploadedDocument($uploadedDocuments, $document_type);
            if (!$doc || empty($doc->storage_path) || $doc->storage_path === 'NOINPUT') {
                $reusableDocuments = $this->loadReusableUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
                $doc = $this->resolveUploadedDocument($reusableDocuments, $document_type);
            }
            if ($doc) {
                $path = $doc->storage_path;
            }
        }

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

        if (!$path || $path === 'NOINPUT') {
            return $noDocumentView();
        }

        // Check explicit paths
        $possiblePaths = [
            storage_path('app/' . $path),
            storage_path('app/public/' . $path),
            public_path('storage/' . $path)
        ];

        $fullPath = null;
        foreach ($possiblePaths as $p) {
            if (file_exists($p)) {
                $fullPath = $p;
                break;
            }
        }

        if (!$fullPath) {
            // Try Storage facade as fallback
            if (Storage::exists($path)) {
                $file = Storage::get($path);
                $type = Storage::mimeType($path);
                return response($file, 200)->header("Content-Type", $type);
            }
            return $noDocumentView();
        }

        $file = file_get_contents($fullPath);
        $type = mime_content_type($fullPath);

        return response($file, 200)->header("Content-Type", $type);
    }

}
