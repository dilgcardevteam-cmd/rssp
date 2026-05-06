a@php
    $galleryItems = $galleryItems ?? collect();
    $documentTypeOptions = $documentTypeOptions ?? [];
    $formatGalleryBytes = function ($bytes) {
        $size = (float) ($bytes ?? 0);
        if ($size < 1024) {
            return (int) $size . ' B';
        }
        if ($size < 1048576) {
            return number_format($size / 1024, 2) . ' KB';
        }
        return number_format($size / 1048576, 2) . ' MB';
    };
    $uploadedGalleryByType = $galleryItems
        ->filter(fn ($item) => filled($item->document_type))
        ->sortByDesc('updated_at')
        ->unique('document_type')
        ->keyBy('document_type');
    $missingGalleryItems = $galleryItems->filter(fn ($item) => (bool) ($item->file_missing_from_storage ?? false))->values();
    $availableGalleryItems = $galleryItems->reject(fn ($item) => (bool) ($item->file_missing_from_storage ?? false))->values();
@endphp

<div class="mt-2 rounded-xl border border-slate-200 bg-white p-4" x-data="{ showChecklist: false }">
    <button type="button" @click="showChecklist = !showChecklist"
        class="flex w-full items-center justify-between gap-3 text-left">
        <div>
            <h3 class="text-sm font-bold text-[#0D2B70]">Document Checklist</h3>
            <p class="mt-1 text-xs text-slate-500">Green means uploaded. Red means the database record exists but the file is missing. Gray means empty.</p>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-600 transition-transform duration-200"
            viewBox="0 0 20 20" fill="currentColor" :class="showChecklist ? 'rotate-180' : ''"
            aria-hidden="true">
            <path fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                clip-rule="evenodd" />
        </svg>
    </button>
    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3" x-show="showChecklist" x-transition
        style="display: none;">
        @foreach ($documentTypeOptions as $docType)
            @php
                $galleryItemForType = $uploadedGalleryByType->get($docType);
                $isUploadedType = $galleryItemForType && !($galleryItemForType->file_missing_from_storage ?? false);
                $isMissingType = $galleryItemForType && ($galleryItemForType->file_missing_from_storage ?? false);
            @endphp
            <div class="flex items-center justify-between rounded-lg border px-3 py-2 {{ $isUploadedType ? 'border-emerald-200 bg-emerald-50' : ($isMissingType ? 'border-rose-200 bg-rose-50' : 'border-slate-200 bg-slate-50') }}">
                <span class="text-xs font-semibold {{ $isUploadedType ? 'text-emerald-700' : ($isMissingType ? 'text-rose-700' : 'text-slate-600') }}">
                    {{ ucwords(str_replace('_', ' ', $docType)) }}
                </span>
                <span class="text-[11px] font-bold {{ $isUploadedType ? 'text-emerald-700' : ($isMissingType ? 'text-rose-700' : 'text-slate-500') }}">
                    {{ $isUploadedType ? 'Uploaded' : ($isMissingType ? 'Missing File' : 'Empty') }}
                </span>
            </div>
        @endforeach
    </div>
</div>

<!-- Document Delete Confirmation Modal -->
<div x-data="{ 
  confirmOpen: false, 
  targetForm: null, 
  docName: '', 
  docType: '',
  confirmDelete() {
    if (this.targetForm) {
      this.targetForm.submit();
    }
    this.confirmOpen = false;
    this.targetForm = null;
  },
  closeModal() {
    this.confirmOpen = false;
    this.targetForm = null;
  }
}" 
  x-on:open-delete-confirm.window="const detail = $event.detail; docName = detail.docName; docType = detail.docType; targetForm = $event.target.closest('form'); confirmOpen = true" 
  x-show="confirmOpen" 
class="fixed inset-0 z-[9999] overflow-y-auto" 
  x-transition:enter="ease-out duration-300"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="ease-in duration-200"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  @keyup.escape="closeModal()"
  style="display: none;">
  <!-- Backdrop -->
<div class="fixed inset-0 z-[9998] bg-black/40" @click="closeModal()"></div>
  
  <!-- Modal panel -->
  <div class="fixed inset-0 flex min-h-full items-end justify-center p-4 md:items-center sm:p-6">
class="w-full max-w-md z-[10000] transform overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200 transition-all">
      <div class="p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-2">Delete Document?</h3>
        <p class="text-sm text-slate-600 mb-6 leading-relaxed">This will permanently remove the document record from your gallery. This action cannot be undone.</p>
        
        <div class="mb-6 rounded-xl bg-gradient-to-r from-slate-50 to-slate-100 p-4">
          <div class="font-semibold text-slate-900 text-base mb-1" x-text="docType"></div>
          <div class="text-slate-600 text-sm truncate max-w-full" x-text="docName"></div>
        </div>
        
        <div class="flex items-center gap-3 justify-end">
          <button 
            @click="closeModal()"
            class="px-4 py-2.5 rounded-xl border border-slate-200 font-semibold text-sm text-slate-700 hover:border-slate-300 hover:bg-slate-50 transition-all">
            Cancel
          </button>
          <button 
            @click="confirmDelete()"
            class="px-6 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 font-semibold text-sm text-white shadow-sm hover:shadow-md transition-all">
            Delete Document
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="mt-4 rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-slate-50 p-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h3 class="text-base font-bold text-[#0D2B70]">How to use your document gallery</h3>
            <p class="mt-1 text-sm text-slate-600">Follow these steps when saving a file here.</p>
        </div>
        <div class="flex flex-wrap gap-2 text-xs font-semibold">
            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700">Uploaded</span>
            <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-rose-700">Needs replacement</span>
            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-600">Not uploaded yet</span>
        </div>
    </div>
    <div class="mt-4 grid gap-3 md:grid-cols-3">
        <div class="rounded-xl border border-white/80 bg-white/90 p-3 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Step 1</p>
            <p class="mt-1 text-sm font-semibold text-slate-800">Choose the document type</p>
            <p class="mt-1 text-xs leading-5 text-slate-600">Pick what kind of file you are uploading, like Application Letter or Transcript of Records.</p>
        </div>
        <div class="rounded-xl border border-white/80 bg-white/90 p-3 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Step 2</p>
            <p class="mt-1 text-sm font-semibold text-slate-800">Select the file from your device</p>
            <p class="mt-1 text-xs leading-5 text-slate-600">Choose a PDF, JPG, JPEG, or PNG file that is 10MB or smaller.</p>
        </div>
        <div class="rounded-xl border border-white/80 bg-white/90 p-3 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Step 3</p>
            <p class="mt-1 text-sm font-semibold text-slate-800">Save it to your gallery</p>
            <p class="mt-1 text-xs leading-5 text-slate-600">If a card below says Missing File, use the replacement box on that card instead of uploading a duplicate.</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('profile.document_gallery.store') }}" enctype="multipart/form-data"
    class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm" data-gallery-async
    x-data="{
        selectedDocumentType: @js(old('document_type', '')),
        pickerOpen: false,
        documentTypeMeta: @js(collect($documentTypeOptions)->mapWithKeys(function ($docType) use ($uploadedGalleryByType) {
            $galleryItemForType = $uploadedGalleryByType->get($docType);
            $status = 'empty';
            if ($galleryItemForType) {
                $status = ($galleryItemForType->file_missing_from_storage ?? false) ? 'missing' : 'uploaded';
            }

            return [$docType => [
                'label' => ucwords(str_replace('_', ' ', $docType)),
                'status' => $status,
            ]];
        })->all()),
        selectedLabel() {
            return this.documentTypeMeta[this.selectedDocumentType]?.label || 'Select a document type';
        }
    }">
    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h3 class="text-lg font-bold text-[#0D2B70]">Upload a new document</h3>
            <p class="text-sm text-slate-600">Use this only for document types that are not yet in your gallery.</p>
        </div>
        <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">
            One file per document type
        </span>
    </div>
    @if (!empty($documentTypeOptions))
        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h4 class="text-sm font-bold text-amber-800">Document type status</h4>
                    <p class="mt-1 text-sm text-amber-700">This guide shows which document types are already added, missing, or still empty before you choose one below.</p>
                </div>
                <span class="inline-flex rounded-full border border-amber-200 bg-white px-3 py-1 text-xs font-semibold text-amber-700">
                    {{ $uploadedGalleryByType->count() }} {{ $uploadedGalleryByType->count() === 1 ? 'type is' : 'types are' }} already added
                </span>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-semibold">
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-3 py-1 text-emerald-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    Added and available
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-white px-3 py-1 text-rose-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                    Added but file is missing
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-600">
                    <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                    Not uploaded yet
                </span>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($documentTypeOptions as $docType)
                    @php
                        $galleryItemForType = $uploadedGalleryByType->get($docType);
                    @endphp
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $galleryItemForType ? (($galleryItemForType->file_missing_from_storage ?? false) ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700') : 'border-slate-200 bg-white text-slate-600' }}">
                        {{ ucwords(str_replace('_', ' ', $docType)) }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
    @csrf
    <div class="mt-4 grid gap-4 lg:grid-cols-[1fr,1fr,220px]">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="mb-3 flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">1</span>
                <div>
                    <label class="block text-sm font-semibold text-slate-800">Choose document type</label>
                    <p class="text-xs text-slate-500">Select the kind of document you want to save.</p>
                </div>
            </div>
            <input type="hidden" name="document_type" :value="selectedDocumentType">
            <div class="relative" @click.outside="pickerOpen = false">
                <button type="button"
                    @click="pickerOpen = !pickerOpen"
                    class="flex w-full items-center justify-between rounded-xl border px-3 py-2.5 text-left text-sm outline-none transition"
                    :class="selectedDocumentType
                        ? 'border-emerald-300 bg-emerald-50 text-emerald-800'
                        : 'border-slate-300 bg-white text-slate-700'">
                    <span class="truncate" x-text="selectedLabel()"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 transition-transform duration-200"
                        :class="pickerOpen ? 'rotate-180' : ''"
                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="pickerOpen" x-transition
                    class="absolute left-0 right-0 z-30 mt-2 max-h-72 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"
                    style="display: none;">
                    <template x-for="[value, meta] in Object.entries(documentTypeMeta)" :key="value">
                        <button type="button"
                            @click="if (meta.status === 'empty') { selectedDocumentType = value; pickerOpen = false; }"
                            class="mb-1 flex w-full items-center justify-between rounded-xl border px-3 py-2 text-left text-sm transition last:mb-0"
                            :class="meta.status === 'uploaded'
                                ? 'cursor-not-allowed border-emerald-200 bg-emerald-50 text-emerald-700'
                                : (meta.status === 'missing'
                                    ? 'cursor-not-allowed border-rose-200 bg-rose-50 text-rose-700'
                                    : 'border-slate-200 bg-white text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700')">
                            <span class="pr-3 font-medium" x-text="meta.label"></span>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-bold"
                                :class="meta.status === 'uploaded'
                                    ? 'bg-white text-emerald-700'
                                    : (meta.status === 'missing'
                                        ? 'bg-white text-rose-700'
                                        : 'bg-slate-100 text-slate-500')"
                                x-text="meta.status === 'uploaded'
                                    ? 'Added'
                                    : (meta.status === 'missing' ? 'Missing' : 'Empty')"></span>
                        </button>
                    </template>
                </div>
            </div>
            <div class="mt-3 rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-blue-700" x-show="!selectedDocumentType">
                Pick a document type first to unlock the file upload and save button.
            </div>
            <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700" x-show="selectedDocumentType">
                Ready to upload:
                <span x-text="selectedLabel()"></span>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="mb-3 flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">2</span>
                <div>
                    <label class="block text-sm font-semibold text-slate-800">Choose file</label>
                    <p class="text-xs text-slate-500">Pick the file from your computer or phone.</p>
                </div>
            </div>
            <input type="file" name="gallery_document" accept=".pdf,.jpg,.jpeg,.png" required :disabled="!selectedDocumentType"
                :class="selectedDocumentType ? 'border-slate-300 bg-white text-slate-700' : 'border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed'"
                class="w-full rounded-xl border px-3 py-2 text-sm">
            <p class="mt-3 text-xs font-medium" :class="selectedDocumentType ? 'text-emerald-700' : 'text-slate-400'">
                <span x-show="selectedDocumentType">File upload unlocked.</span>
                <span x-show="!selectedDocumentType">Choose a document type in Step 1 to unlock this field.</span>
            </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="mb-3 flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">3</span>
                <div>
                    <p class="block text-sm font-semibold text-slate-800">Save document</p>
                    <p class="text-xs text-slate-500">Click once after checking the file.</p>
                </div>
            </div>
            <button type="submit" :disabled="!selectedDocumentType"
                :class="selectedDocumentType ? 'bg-[#0D2B70] text-white hover:bg-[#0A2259]' : 'bg-slate-300 text-slate-500 cursor-not-allowed'"
                class="w-full rounded-xl px-4 py-3 text-sm font-semibold transition">
                Upload and Save
            </button>
            <p class="mt-3 text-xs font-medium" :class="selectedDocumentType ? 'text-emerald-700' : 'text-slate-400'">
                <span x-show="selectedDocumentType">Save button unlocked.</span>
                <span x-show="!selectedDocumentType">This button will activate after you choose a document type.</span>
            </p>
        </div>
    </div>
    <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-600">
        Allowed files: PDF, JPG, JPEG, PNG. Maximum size: 10MB.
    </div>
    @error('gallery_document')
        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
    @enderror
    @error('document_type')
        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
    @enderror
</form>

<div class="mt-4">
    @if ($galleryItems->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
            No saved documents yet. Upload files above to build your reusable document gallery.
        </div>
    @else
        @if ($missingGalleryItems->isNotEmpty())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-rose-800">Documents That Need Attention</h3>
                        <p class="mt-1 text-sm text-rose-700">These records are saved in the database, but their files are missing. Upload a replacement file below each card to fix them.</p>
                    </div>
                    <span class="inline-flex rounded-full border border-rose-200 bg-white px-3 py-1 text-xs font-semibold text-rose-700">
                        {{ $missingGalleryItems->count() }} {{ $missingGalleryItems->count() === 1 ? 'document' : 'documents' }} need attention
                    </span>
                </div>
            </div>

            <div class="mt-3 grid gap-4 md:grid-cols-2">
                @foreach ($missingGalleryItems as $item)
                    @php
                        $documentTypeLabel = $item->document_type ? ucwords(str_replace('_', ' ', $item->document_type)) : 'General / Unclassified';
                    @endphp
                    <article class="rounded-2xl border border-rose-200 bg-rose-50/50 p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="inline-flex rounded-full border border-rose-200 bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-rose-700">
                                    Action Needed
                                </div>
                                <p class="mt-3 text-base font-bold text-slate-800">{{ $documentTypeLabel }}</p>
                                <p class="mt-1 truncate text-sm text-slate-600">{{ $item->original_name }}</p>
                                <p class="mt-2 text-xs text-slate-500">
                                    {{ $formatGalleryBytes($item->file_size_8b) }} | {{ optional($item->created_at)->format('M d, Y h:i A') }}
                                </p>
                            </div>
                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase text-slate-600">
                                {{ strtoupper(pathinfo((string) $item->original_name, PATHINFO_EXTENSION) ?: 'FILE') }}
                            </span>
                        </div>

                        <div class="mt-4 rounded-xl border border-rose-200 bg-white p-4">
                            <p class="text-sm font-semibold text-rose-700">This file cannot be opened because it is missing from storage.</p>
                            <p class="mt-1 text-sm text-slate-600">To fix this, choose the correct file below and click <span class="font-semibold text-slate-800">Upload Replacement</span>.</p>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="rounded-lg border border-rose-200 bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-700">
                                Preview not available
                            </span>
                            <form method="POST" action="{{ route('profile.document_gallery.delete', $item->id) }}" data-gallery-async>
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                    @click="$dispatch('open-delete-confirm', { 
                                      docName: @js($item->original_name), 
                                      docType: @js($documentTypeLabel)
                                    })"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"> 
                                    Remove from List
                                </button>
                            </form>
                        </div>

                        <div class="mt-4 rounded-2xl border border-rose-200 bg-white p-4">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-rose-100 text-xs font-bold text-rose-700">1</span>
                                <div>
                                    <p class="text-sm font-semibold text-rose-800">Choose a replacement file</p>
                                    <p class="text-xs text-slate-500">Use the correct file for this document type.</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('profile.document_gallery.replace', $item->id) }}" enctype="multipart/form-data"
                                class="mt-4 grid gap-3 lg:grid-cols-[1fr,220px]" data-gallery-async>
                                @csrf
                                <input type="file" name="replacement_gallery_document" accept=".pdf,.jpg,.jpeg,.png" required
                                    class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2.5 text-sm text-slate-700">
                                <button type="submit" data-gallery-loading-button
                                    class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                                    <span class="js-gallery-btn-label">Upload Replacement</span>
                                    <span class="js-gallery-btn-loader hidden items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                        <span>Uploading...</span>
                                    </span>
                                </button>
                            </form>
                            @error('replacement_gallery_document')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($availableGalleryItems->isNotEmpty())
            <div class="mt-6" x-data="savedDocumentsSearch()">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-[#0D2B70]">Saved Documents Ready to Use</h3>
                        <p class="text-sm text-slate-600">These files are available for preview and download.</p>
                    </div>
                    <div class="flex flex-col gap-3 lg:items-end">
                        <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">
                            {{ $availableGalleryItems->count() }} {{ $availableGalleryItems->count() === 1 ? 'document' : 'documents' }} available
                        </span>
                        <div class="relative w-full lg:w-80">
                            <input type="text"
                                x-model="searchTerm"
                                @input="queueSearch()"
                                placeholder="Search saved documents"
                                class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-11 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            <div x-show="isSearchPending" x-transition.opacity class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-blue-600" style="display: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 grid gap-4 md:grid-cols-2" x-ref="availableDocumentsGrid">
                    @foreach ($availableGalleryItems as $item)
                        @php
                            $documentTypeLabel = $item->document_type ? ucwords(str_replace('_', ' ', $item->document_type)) : 'General / Unclassified';
                            $searchText = strtolower(trim($documentTypeLabel . ' ' . (string) $item->original_name));
                        @endphp
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                            data-search-text="{{ e($searchText) }}"
                            x-show="matchesDocument($el.dataset.searchText)">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-emerald-700">
                                        Ready to use
                                    </div>
                                    <p class="mt-3 text-base font-bold text-slate-800" x-html="highlightText(@js($documentTypeLabel))"></p>
                                    <p class="mt-1 truncate text-sm text-slate-600" x-html="highlightText(@js((string) $item->original_name))"></p>
                                    <p class="mt-2 text-xs text-slate-500">
                                        {{ $formatGalleryBytes($item->file_size_8b) }} | {{ optional($item->created_at)->format('M d, Y h:i A') }}
                                    </p>
                                </div>
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase text-slate-600">
                                    {{ strtoupper(pathinfo((string) $item->original_name, PATHINFO_EXTENSION) ?: 'FILE') }}
                                </span>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <a href="{{ route('profile.document_gallery.preview', $item->id) }}" target="_blank" rel="noopener"
                                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Open File
                                </a>
                                <a href="{{ route('profile.document_gallery.download', $item->id) }}"
                                    class="rounded-lg border border-blue-300 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                    Download Copy
                                </a>
                                <form method="POST" action="{{ route('profile.document_gallery.delete', $item->id) }}" data-gallery-async>
                                    @csrf
                                    @method('DELETE')
                                <button type="button"
                                    @click="$dispatch('open-delete-confirm', { 
                                      docName: @js($item->original_name), 
                                      docType: @js($documentTypeLabel)
                                    })"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"> 
                                        Remove from List
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div x-show="!hasMatches()" x-transition class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500" style="display: none;">
                    No saved documents matched your search.
                </div>
            </div>
        @endif
    @endif
</div>
