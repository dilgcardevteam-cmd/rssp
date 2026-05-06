<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\DocumentGalleryItem;
use App\Models\Notification;
use App\Models\UploadedDocument;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        return redirect()->route('account.settings');
    }

    public function edit()
    {
        return redirect()->route('account.settings');
    }

    public function accountSettings()
    {
        $user = Auth::user();
        $user->loadMissing(['profile', 'personalInformation']);
        $isGoogleSignup = Hash::check('google-oauth', (string) $user->password);
        $galleryViewData = $this->getDocumentGalleryViewData($user);

        return view('profile.account_settings', [
            'user' => $user,
            'personalInfo' => $user->personalInformation,
            'isGoogleSignup' => $isGoogleSignup,
            ...$galleryViewData,
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $avatarUpload = $request->file('avatar');
        if ($avatarUpload && !$avatarUpload->isValid()) {
            return back()->withErrors([
                'avatar' => 'Avatar upload failed. Please upload a valid PNG or JPG image up to 2MB.',
            ])->withInput();
        }

        if ($avatarUpload && $avatarUpload->getSize() > (2 * 1024 * 1024)) {
            return back()->withErrors([
                'avatar' => 'Avatar must not be greater than 2MB.',
            ])->withInput();
        }

        $user = Auth::user();
        $validated = $request->validated();
        $firstName = trim((string) ($validated['first_name'] ?? ''));
        $lastName = trim((string) ($validated['last_name'] ?? ''));

        // Backward compatibility: accept legacy "name" payloads when split fields are not sent.
        if ($firstName === '' && $lastName === '' && filled($validated['name'] ?? null)) {
            $parts = preg_split('/\s+/', trim((string) $validated['name'])) ?: [];
            $firstName = trim((string) array_shift($parts));
            $lastName = trim(implode(' ', $parts));
            $validated['first_name'] = $firstName;
            $validated['last_name'] = $lastName;
        }

        if ($firstName !== '' || $lastName !== '') {
            $middleInitial = filled($validated['middle_name'] ?? null)
                ? strtoupper(mb_substr(trim((string) $validated['middle_name']), 0, 1)) . '.'
                : '';
            $validated['name'] = trim(implode(' ', array_filter([
                $firstName,
                $middleInitial,
                $lastName,
            ])));
        } elseif (array_key_exists('name', $validated)) {
            $validated['name'] = trim((string) $validated['name']);
        }

        if (array_key_exists('phone', $validated)) {
            $validated['phone_number'] = preg_replace('/\D+/', '', (string) $validated['phone']);
        }
        if ($avatarUpload) {
            $file = $avatarUpload;
            $path = 'avatars/' . Auth::id() . '-' . time() . '.png';
            $imageData = file_get_contents($file->getPathname());
            // Attempt simple square resize via GD if available.
            if (function_exists('imagecreatefromstring')) {
                $src = imagecreatefromstring($imageData);
                if ($src) {
                    $w = imagesx($src);
                    $h = imagesy($src);
                    $size = 256;
                    $dst = imagecreatetruecolor($size, $size);
                    $min = min($w, $h);
                    $sx = (int) (($w - $min) / 2);
                    $sy = (int) (($h - $min) / 2);
                    imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $size, $size, $min, $min);
                    ob_start();
                    imagepng($dst);
                    $imageData = ob_get_clean();
                    imagedestroy($dst);
                    imagedestroy($src);
                }
            }
            Storage::disk('public')->put($path, $imageData);
            $validated['avatar_path'] = $path;
        }
        $user->fill($validated);
        $user->save();
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);
        if (array_key_exists('bio', $validated)) $profile->bio = $validated['bio'];
        if (array_key_exists('phone', $validated)) $profile->phone = $validated['phone'];
        if (array_key_exists('address', $validated)) $profile->address = $validated['address'];
        if (array_key_exists('preferences', $validated)) $profile->preferences = $validated['preferences'];
        $profile->save();
        return redirect()->route('account.settings')->with('settings_success', 'Profile updated successfully.');
    }

    public function avatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'avatar.required' => 'Please choose an avatar image to upload.',
            'avatar.image' => 'Avatar must be a valid image file.',
            'avatar.mimes' => 'Avatar must be a JPG or PNG image.',
            'avatar.max' => 'Avatar must not be greater than 2MB.',
        ]);
        $file = $request->file('avatar');
        if (!$file || !$file->isValid()) {
            return back()->withErrors([
                'avatar' => 'Avatar upload failed. Please upload a valid PNG or JPG image up to 2MB.',
            ])->withInput();
        }

        if ($file->getSize() > (2 * 1024 * 1024)) {
            return back()->withErrors([
                'avatar' => 'Avatar must not be greater than 2MB.',
            ])->withInput();
        }

        $path = 'avatars/'.Auth::id().'-'.time().'.png';
        $imageData = file_get_contents($file->getPathname());
        // Attempt simple square resize via GD if available
        if (function_exists('imagecreatefromstring')) {
            $src = imagecreatefromstring($imageData);
            if ($src) {
                $w = imagesx($src); $h = imagesy($src);
                $size = 256;
                $dst = imagecreatetruecolor($size, $size);
                $min = min($w, $h);
                $sx = (int)(($w - $min) / 2);
                $sy = (int)(($h - $min) / 2);
                imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $size, $size, $min, $min);
                ob_start(); imagepng($dst); $imageData = ob_get_clean();
                imagedestroy($dst); imagedestroy($src);
            }
        }
        Storage::disk('public')->put($path, $imageData);
        $user = Auth::user();
        $user->avatar_path = $path;
        $user->save();
        return back()->with('status', 'Avatar updated.');
    }

    public function password(ChangePasswordRequest $request)
    {
        $user = Auth::user();
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }
        $user->password = Hash::make($request->input('password'));
        $user->save();
        return redirect()->route('account.settings')->with('password_success', 'Password updated successfully.');
    }

    public function requestAccountDeletion(Request $request)
    {
        $user = Auth::user();
        $user->loadMissing('personalInformation');

        if (
            !Schema::hasColumn('users', 'deletion_requested_by_applicant_at')
            || !Schema::hasColumn('users', 'deletion_request_received_by_admin_at')
        ) {
            return redirect()
                ->route('account.settings')
                ->withErrors(['account_deletion_request' => 'Account deletion request is unavailable right now. Please run the latest database migration.']);
        }

        if ($user->isPendingDeletion()) {
            return redirect()
                ->route('account.settings')
                ->withErrors(['account_deletion_request' => 'Your account is already set for deletion by an administrator.']);
        }

        if (!is_null($user->deletion_requested_by_applicant_at)) {
            $requestedAt = optional($user->deletion_requested_by_applicant_at)->format('M d, Y h:i A') ?: 'N/A';
            return redirect()
                ->route('account.settings')
                ->withErrors(['account_deletion_request' => 'Deletion request already submitted on ' . $requestedAt . '.']);
        }

        $requestedAt = now();
        $user->forceFill([
            'deletion_requested_by_applicant_at' => $requestedAt,
            'deletion_request_received_by_admin_at' => $requestedAt,
        ])->save();

        $displayName = trim((string) ($user->personalInformation?->first_name ?? ''));
        $surname = trim((string) ($user->personalInformation?->surname ?? ''));
        $fullName = trim($displayName . ' ' . $surname);
        if ($fullName === '') {
            $fullName = trim((string) ($user->name ?? 'Applicant'));
        }

        if (Schema::hasTable('notifications')) {
            $requestTimestampText = $requestedAt->format('M d, Y h:i A');
            $adminLink = route('admin.applicant_records.index', [], false);
            $recipientAdmins = Admin::query()
                ->where('role', 'superadmin')
                ->where('is_active', 1)
                ->get();

            foreach ($recipientAdmins as $admin) {
                Notification::create([
                    'notifiable_type' => Admin::class,
                    'notifiable_id' => $admin->id,
                    'type' => 'warning',
                    'data' => [
                        'title' => 'Account Deletion Request',
                        'message' => $fullName . ' requested account deletion on ' . $requestTimestampText . '.',
                        'link' => $adminLink,
                        'action_url' => $adminLink,
                        'section' => 'Applicant Records',
                        'category' => 'account_deletion_request',
                        'user_id' => $user->id,
                        'applicant_code' => $user->applicant_code,
                        'requested_at' => $requestedAt->toIso8601String(),
                        'received_at' => $requestedAt->toIso8601String(),
                    ],
                ]);
            }
        }

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->event('request_account_deletion')
            ->withProperties([
                'section' => 'Account Settings',
                'user_id' => $user->id,
                'applicant_code' => $user->applicant_code,
                'requested_at' => $requestedAt->toDateTimeString(),
            ])
            ->log('Applicant requested account deletion.');

        return redirect()
            ->route('account.settings')
            ->with('settings_success', 'Deletion request submitted on ' . $requestedAt->format('M d, Y h:i A') . '. Admin review is required.');
    }

    public function storeGalleryDocument(Request $request)
    {
        $documentTypeOptions = $this->getDocumentGalleryViewData(Auth::user())['documentTypeOptions'];
        $validator = Validator::make($request->all(), [
            'gallery_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'document_type' => ['required', 'string', 'in:' . implode(',', $documentTypeOptions)],
        ]);
        if ($validator->fails()) {
            return $this->documentGalleryValidationResponse($request, $validator->errors()->toArray());
        }

        $user = Auth::user();
        $validated = $validator->validated();
        $documentType = (string) $validated['document_type'];
        $existingType = DocumentGalleryItem::where('user_id', $user->id)
            ->where('document_type', $documentType)
            ->exists();
        if ($existingType) {
            return $this->documentGalleryValidationResponse($request, [
                'document_type' => ['A file for this document type already exists. Remove it first before uploading another.'],
            ]);
        }

        $file = $request->file('gallery_document');
        ['original_name' => $originalName, 'stored_name' => $storedName, 'storage_path' => $storagePath] = $this->storeGalleryFile($file, (int) $user->id);

        DocumentGalleryItem::create([
            'user_id' => $user->id,
            'document_type' => $documentType,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'storage_path' => $storagePath,
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'file_size_8b' => (int) $file->getSize(),
        ]);

        return $this->documentGallerySuccessResponse($request, 'Document uploaded to your gallery.');
    }

    public function replaceGalleryDocument(Request $request, DocumentGalleryItem $item)
    {
        if ((int) $item->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'replacement_gallery_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
        if ($validator->fails()) {
            return $this->documentGalleryValidationResponse($request, $validator->errors()->toArray());
        }

        $file = $validator->validated()['replacement_gallery_document'];
        ['original_name' => $originalName, 'stored_name' => $storedName, 'storage_path' => $storagePath] = $this->storeGalleryFile($file, (int) $item->user_id);

        if (!empty($item->storage_path) && Storage::disk('public')->exists($item->storage_path)) {
            Storage::disk('public')->delete($item->storage_path);
        }

        $item->forceFill([
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'storage_path' => $storagePath,
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'file_size_8b' => (int) $file->getSize(),
        ])->save();

        return $this->documentGallerySuccessResponse($request, 'Missing document restored successfully.');
    }

    public function previewGalleryDocument(DocumentGalleryItem $item)
    {
        if ((int) $item->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($item->storage_path)) {
            return redirect()
                ->route('account.settings')
                ->withErrors(['gallery_document' => 'The selected file is missing from storage.']);
        }

        $mimeType = (string) ($item->mime_type ?: Storage::disk('public')->mimeType($item->storage_path) ?: 'application/octet-stream');
        $contentDisposition = in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png'], true)
            ? 'inline'
            : 'attachment';

        return response(Storage::disk('public')->get($item->storage_path), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $contentDisposition . '; filename="' . addslashes($item->original_name) . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    public function downloadGalleryDocument(DocumentGalleryItem $item)
    {
        if ((int) $item->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($item->storage_path)) {
            return redirect()
                ->route('account.settings')
                ->withErrors(['gallery_document' => 'The selected file is missing from storage.']);
        }

        return Storage::disk('public')->download($item->storage_path, $item->original_name);
    }

    public function deleteGalleryDocument(Request $request, DocumentGalleryItem $item)
    {
        if ((int) $item->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (!empty($item->storage_path) && Storage::disk('public')->exists($item->storage_path)) {
            Storage::disk('public')->delete($item->storage_path);
        }

        $item->delete();

        return $this->documentGallerySuccessResponse($request, 'Document removed from your gallery.');
    }

    private function getDocumentGalleryViewData($user): array
    {
        $galleryItems = DocumentGalleryItem::where('user_id', $user->id)
            ->latest('updated_at')
            ->get()
            ->map(function (DocumentGalleryItem $item) {
                $storagePath = trim((string) $item->storage_path);
                $item->file_missing_from_storage = $storagePath === ''
                    || strtoupper($storagePath) === 'NOINPUT'
                    || !Storage::disk('public')->exists($storagePath);

                return $item;
            });
        $documentTypeOptions = array_values(array_filter(
            UploadedDocument::DOCUMENTS,
            fn ($docType) => $docType !== 'isApproved'
        ));

        return [
            'galleryItems' => $galleryItems,
            'documentTypeOptions' => $documentTypeOptions,
        ];
    }

    private function documentGallerySuccessResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'html' => $this->renderDocumentGalleryHtml(Auth::user()),
            ]);
        }

        return redirect()
            ->route('account.settings')
            ->with('document_gallery_success', $message);
    }

    private function documentGalleryValidationResponse(Request $request, array $errors)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => collect($errors)->flatten()->first() ?: 'Please check the document details and try again.',
                'errors' => $errors,
            ], 422);
        }

        return redirect()
            ->route('account.settings')
            ->withErrors($errors)
            ->withInput();
    }

    private function renderDocumentGalleryHtml($user): string
    {
        return view('profile.partials.document_gallery_content', $this->getDocumentGalleryViewData($user))->render();
    }

    private function storeGalleryFile($file, int $userId): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeBaseName = Str::of($baseName)->replaceMatches('/[^A-Za-z0-9\-_]+/', '_')->trim('_')->limit(50, '');
        $safeBaseName = $safeBaseName === '' ? 'document' : (string) $safeBaseName;
        $storedName = now()->format('YmdHis') . '_' . Str::random(8) . '_' . $safeBaseName . ($extension !== '' ? ".{$extension}" : '');
        $storagePath = $file->storeAs("document_gallery/{$userId}", $storedName, 'public');

        return [
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'storage_path' => $storagePath,
        ];
    }
}
