@extends('layout.pds_layout')
@section('title', 'Other Information')
@section('content')
@php
    $simple = in_array(request()->input('simple'), [1, '1', true, 'true'], true);
@endphp
<style>
    .pds-flow-page {
        position: relative;
        color: #163053;
        scroll-behavior: smooth;
    }

    .pds-flow-page::before {
        content: '';
        position: fixed;
        inset: 0;
        z-index: -1;
        background:
            radial-gradient(circle at top left, rgba(13, 91, 215, 0.14), transparent 28%),
            radial-gradient(circle at top right, rgba(0, 44, 118, 0.08), transparent 24%),
            linear-gradient(180deg, #f7faff 0%, #edf3fb 100%);
    }

    .pds-flow-banner {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding: 1rem 1.1rem;
        border: 1px solid rgba(164, 188, 227, 0.45);
        border-radius: 1.25rem;
        background: linear-gradient(135deg, #001a45 0%, #002c76 58%, #0b4ea8 100%);
        color: #fff;
        box-shadow: 0 18px 40px rgba(14, 36, 82, 0.18);
    }

    .pds-flow-banner p {
        margin: 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .pds-flow-banner-title {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }

    .pds-flow-banner-title .material-icons {
        font-size: 1.8rem;
        color: rgba(255, 255, 255, 0.96);
    }

    .pds-flow-banner-title strong {
        font-size: clamp(1.2rem, 1rem + 0.65vw, 1.7rem);
        line-height: 1.1;
    }

    .pds-flow-banner-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .pds-flow-banner-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.42rem 0.75rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.12);
        font-size: 0.78rem;
        line-height: 1.1;
    }

    a.pds-flow-banner-chip {
        color: inherit;
        text-decoration: none;
        transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    a.pds-flow-banner-chip:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.34);
        transform: translateY(-1px);
    }

    .pds-flow-section {
        position: relative;
        overflow: hidden;
        scroll-margin-top: 6.5rem;
        border: 1px solid rgba(153, 176, 214, 0.32);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 250, 255, 0.96) 100%);
        box-shadow: 0 16px 40px rgba(15, 36, 79, 0.08), 0 2px 8px rgba(15, 36, 79, 0.04);
    }

    .pds-flow-section::before {
        content: '';
        position: absolute;
        inset: 0 0 auto;
        height: 4px;
        background: linear-gradient(90deg, #002c76 0%, #2563eb 56%, #7fb2ff 100%);
    }

    .pds-question-card {
        border: 1px solid #d8e4f8;
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(248, 251, 255, 0.94) 0%, rgba(255, 255, 255, 0.98) 100%);
        padding: 1rem 1.1rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .pds-question-card .flex.gap-4,
    .pds-question-card .flex.gap-6 {
        flex-wrap: wrap;
    }

    .pds-question-card label.flex.items-center {
        gap: 0.55rem;
        min-height: 2.8rem;
        padding: 0.7rem 0.95rem;
        border: 1px solid #d8e4f8;
        border-radius: 0.95rem;
        background: rgba(255, 255, 255, 0.9);
        color: #27405f;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .pds-question-card label.flex.items-center:hover {
        border-color: #9cb8e8;
        box-shadow: 0 8px 18px rgba(15, 36, 79, 0.06);
        transform: translateY(-1px);
    }

    .pds-declaration-group + .pds-declaration-group {
        margin-top: 1.5rem;
    }

    .pds-declaration-group-title {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 0.85rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        border: 1px solid #d8e4f8;
        background: linear-gradient(180deg, rgba(240, 246, 255, 0.96) 0%, rgba(255, 255, 255, 0.98) 100%);
        color: #23406a;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .pds-declaration-group-title .material-icons {
        font-size: 1rem;
        color: #0d5bd7;
    }

    .pds-section-title {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .pds-section-icon {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 2.9rem;
        height: 2.9rem;
        margin-right: 0 !important;
        border-radius: 0.95rem;
        background: linear-gradient(135deg, #e6efff 0%, #f7faff 100%);
        color: #002c76;
        box-shadow: inset 0 0 0 1px rgba(115, 151, 210, 0.22);
    }

    .pds-submit-bar {
        position: sticky;
        bottom: 1rem;
        z-index: 20;
        padding: 1rem;
        border: 1px solid rgba(162, 183, 218, 0.4);
        border-radius: 1.15rem;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(12px);
        box-shadow: 0 18px 40px rgba(15, 36, 79, 0.12);
    }

    .pds-submit-button,
    .pds-back-button {
        border-radius: 0.95rem !important;
        box-shadow: 0 12px 24px rgba(0, 44, 118, 0.14);
    }

    .pds-submit-button {
        background: linear-gradient(135deg, #0d5bd7 0%, #002c76 100%) !important;
    }

    .pds-warning-footer {
        border: 1px solid rgba(231, 188, 110, 0.4);
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(255, 248, 231, 0.95) 0%, rgba(255, 252, 245, 0.98) 100%);
        color: #70511b;
        box-shadow: 0 12px 28px rgba(122, 84, 19, 0.08);
    }

    .pds-preview-fab {
        position: fixed;
        right: 1.25rem;
        bottom: 1.25rem;
        z-index: 70;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        min-height: 3.5rem;
        padding: 0.9rem 1.2rem;
        border: 1px solid rgba(189, 213, 255, 0.35);
        border-radius: 999px;
        background: linear-gradient(135deg, #002c76 0%, #0d5bd7 100%);
        color: #fff;
        font-size: 0.9rem;
        font-weight: 700;
        line-height: 1;
        box-shadow: 0 18px 36px rgba(7, 26, 67, 0.28);
        backdrop-filter: blur(12px);
        transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease, background 0.2s ease;
        touch-action: none;
        cursor: grab;
        user-select: none;
    }

    .pds-preview-fab:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 22px 42px rgba(7, 26, 67, 0.34);
    }

    .pds-preview-fab.is-dragging,
    .pds-preview-fab:active {
        cursor: grabbing;
    }

    .pds-preview-fab:disabled {
        cursor: not-allowed;
        opacity: 0.72;
        background: linear-gradient(135deg, #8a97ad 0%, #a7b2c6 100%);
        box-shadow: 0 10px 22px rgba(15, 36, 79, 0.12);
        transform: none;
    }

    @media (max-width: 640px) {
        .pds-preview-fab {
            left: 1rem;
            right: 1rem;
            bottom: calc(6.25rem + env(safe-area-inset-bottom, 0px));
            width: auto;
        }
    }
</style>
<main class="pds-flow-page {{ $simple ? 'w-full max-w-none' : 'max-w-7xl mx-auto' }} -mt-8 sm:-mt-10 px-4 sm:px-6 lg:px-8 pt-0 pb-8">
        @php
            $hasWorkExperience = !empty($data['work_experience'] ?? []) || !empty($data['work_exp'] ?? []) || !empty($data['work_exps'] ?? []);
        @endphp
        @php
            $c4GoTo = (request()->boolean('simple') || request()->boolean('open_docs')) ? 'display_wes' : 'display_c5';
            $c4RouteParams = ['go_to' => $c4GoTo];
            if (request()->boolean('open_docs')) { $c4RouteParams['open_docs'] = 1; }
            if (request()->boolean('simple'))    { $c4RouteParams['simple'] = 1; }
        @endphp
        <form id="other-info-form" class="space-y-8" action="{{ route('submit_c4', $c4RouteParams) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(request()->boolean('simple'))
                <input type="hidden" name="simple" value="1">
            @endif
            <div class="pds-flow-banner">
                <div>
                    <div class="pds-flow-banner-title">
                        <span class="material-icons">assignment_turned_in</span>
                        <strong class="font-semibold">Declarations, References and Government-Issued ID Details</strong>
                    </div>
                </div>
                <div class="pds-flow-banner-meta">
                    <a href="#declarations-section" class="pds-flow-banner-chip">
                        <span class="material-icons text-sm">fact_check</span>
                        Declarations
                    </a>
                    <a href="#references-section" class="pds-flow-banner-chip">
                        <span class="material-icons text-sm">people</span>
                        References
                    </a>
                    <a href="#government-id-section" class="pds-flow-banner-chip">
                        <span class="material-icons text-sm">badge</span>
                        Government ID Details
                    </a>
                </div>
            </div>
            <section id="declarations-section" class="pds-flow-section bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="pds-section-title mb-6">
                    <span class="material-icons pds-section-icon text-blue-600 text-3xl">fact_check</span>
                    <h2 class="text-2xl font-bold text-gray-900">Declarations</h2>
                </div>

                <div class="pds-declaration-group">
                <div class="pds-declaration-group-title">
                    <span class="material-icons">supervisor_account</span>
                    Relationship Declarations
                </div>
                <div class="question-card pds-question-card">
                    <p class="text-gray-700 font-bold mb-3">
                        34. Are you related by consanguinity or affinity to the appointing or recommending authority, or to the 
                        chief of bureau or office or to the person who has immediate supervision over you in the Office,
                        Bureau or Department where you will be appointed, 
                    </p>
                    <p class="text-gray-700 font-medium mb-3">
                        a. within the third degree?
                    </p>
                    <div class="flex gap-4 mb-4"> <!-- NUMBER 34: a -->
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input required type="radio" name="related_34_a" class="mr-2"
                                value="yes" {{ old('related_34_a', $data['related_34_a'] ?? '') == 'yes' ? 'checked' : '' }}>
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input required type="radio" name="related_34_a"  class="mr-2"
                                value="no" {{ old('related_34_a', $data['related_34_a'] ?? '') == 'no' ? 'checked' : '' }}>
                                <span>No</span>
                            </label>
                    </div>

                    <p class="text-gray-700 gap-4 mb-2">b. within the fourth degree (for Local Government Unit - Career Employees)?</p>
                    <div class="flex gap-6"> <!-- NUMBER 34: b -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input required type="radio" name="related_34_b"  class="mr-2"
                            value="yes" {{ old('related_34_b', $data['related_34_b'] ?? '') == 'yes' ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input required type="radio" name="related_34_b" class="mr-2"
                            value="no" {{ old('related_34_b', $data['related_34_b'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="related-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="related_34_b_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('related_34_b', $data['related_34_b'] ?? '') }}</textarea>
                    </div>
                </div>
                </div>

                <div class="pds-declaration-group">
                <div class="pds-declaration-group-title">
                    <span class="material-icons">gavel</span>
                    Administrative and Criminal Cases
                </div>
                <div class="question-card pds-question-card">
                    <p class="text-gray-700 font-bold mb-3">
                        35. A. Have you ever been found guilty of any administrative offense?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 35: a -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="guilty_35_a" class="mr-2" required
                            value="yes" {{ old('guilty_35_a', $data['guilty_35_a'] ?? '') == 'yes' ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="guilty_35_a" class="mr-2"
                            value="no" {{ old('guilty_35_a', $data['guilty_35_a'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="admin-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="guilty_35_a_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('guilty_35_a', $data['guilty_35_a'] ?? '') }}</textarea>
                    </div>
                </div>

                <div class="question-card pds-question-card mt-6">
                    <p class="text-gray-700 font-bold mb-3">
                        B. Have you been criminally charged before any court?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 35: b -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="criminal_35_b" class="mr-2"  required
                            value="yes" {{ old('criminal_35_b', $data['criminal_35_b'] ?? '') == 'yes' ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="criminal_35_b" class="mr-2"
                            value="no" {{ old('criminal_35_b', $data['criminal_35_b'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="criminal-details" class="detail-input hidden mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>

                        <!-- Date of Case -->
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Date of Filing</label>
                            <input type="date" name="criminal_35_b_details[date]" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            value="{{ old('criminal_35_b_array.date', $data['criminal_35_b_array']['date'] ?? '') }}" >
                        </div>

                        <!-- Status of Case -->
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Status of Case/s</label>
                            <input type="text" name="criminal_35_b_details[status]" placeholder="Enter case status..." class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            value="{{ old('criminal_35_b_array.status', $data['criminal_35_b_array']['status'] ?? '') }}" >
                        </div>
                    </div>

                </div>
                <div class="question-card pds-question-card mt-6">
                    <p class="text-gray-700 font-bold mb-3">
                        36. Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 36 -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="convicted_36" class="mr-2" required
                            value="yes" {{ old('convicted_36', $data['convicted_36'] ?? '') == 'yes' ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="convicted_36" class="mr-2"
                            value="no" {{ old('convicted_36', $data['convicted_36'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="convicted-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="convicted_36_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('convicted_36', $data['convicted_36'] ?? '') }}</textarea>
                    </div>
                </div>
                </div>

                <div class="pds-declaration-group">
                <div class="pds-declaration-group-title">
                    <span class="material-icons">policy</span>
                    Service, Election, and Residency
                </div>
                <div class="question-card pds-question-card">
                    <p class="text-gray-700 font-bold mb-3">
                        37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term
                        finish contract or phased out (abolition) in the public or private sector?
                    </p>
                    <div class="flex gap-6"> <!--NUMBER 37 -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="separated_37" class="mr-2" required
                             value="yes" {{ old('separated_37', $data['separated_37'] ?? '') == 'yes' ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="separated_37" class="mr-2"
                             value="no" {{ old('separated_37', $data['separated_37'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="separated-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="separated_37_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('separated_37', $data['separated_37'] ?? '') }}</textarea>
                    </div>
                </div>
                <div class="question-card pds-question-card mt-6">
                    <p class="text-gray-700 font-bold mb-3">
                        38. A. Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 38: a -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="candidate_38_a" class="mr-2" required
                             value="yes" {{ old('candidate_38', $data['candidate_38'] ?? '') == 'yes' ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="candidate_38_a" class="mr-2"
                             value="no" {{ old('candidate_38', $data['candidate_38'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="candidate-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="candidate_38_a_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('candidate_38', $data['candidate_38'] ?? '') }}</textarea>
                    </div>
                </div>

                <div class="question-card pds-question-card mt-6">
                    <p class="text-gray-700 font-bold mb-3">
                        B. Have you resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national and local candidate?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 38: b -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="resigned_38_b" class="mr-2" required
                             value="yes" {{ old('resigned_38_b', $data['resigned_38_b'] ?? '') == 'yes' ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="resigned_38_b" class="mr-2"
                             value="no" {{ old('resigned_38_b', $data['resigned_38_b'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="resign-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="resigned_38_b_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('resigned_38_b', $data['resigned_38_b'] ?? '') }}</textarea>
                    </div>
                </div>
                <div class="question-card pds-question-card mt-6">
                    <p class="text-gray-700 font-bold mb-3">
                        39. Have you acquired the status of an immigrant or permanent resident of another country?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 39 -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="immigrant_39"class="mr-2" required
                             value="yes" {{ old('immigrant_39', $data['immigrant_39'] ?? '') == 'yes' ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="immigrant_39" class="mr-2"
                             value="no" {{ old('immigrant_39', $data['immigrant_39'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="immigrant-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="immigrant_39_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('immigrant_39', $data['immigrant_39'] ?? '') }}</textarea>
                    </div>
                </div>
                </div>

                <div class="pds-declaration-group">
                <div class="pds-declaration-group-title">
                    <span class="material-icons">diversity_3</span>
                    Special Status Declarations
                </div>
                <p class="text-gray-700 font-bold mb-3">
                40. Pursuant to: (a) Indigenous People's Act (RA 8371); (b) Magna Carta for Disabled Persons (RA 7277, as amended); and (c) Expanded Solo Parents Welfare Act (RA 11861), please answer the following items:
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8 pb-6 border-b-2 border-gray-200">
                    <div class="question-card pds-question-card">
                        <p class="text-gray-700 font-bold mb-3">A. Are you a member of any indigenous group?</p>
                        <div class="flex gap-4"> <!-- NUMBER 40: a -->
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="indigenous_40_a" class="mr-2" required
                                 value="yes" {{ old('indigenous_40_a', $data['indigenous_40_a'] ?? '') == 'yes' ? 'checked' : '' }}>
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="indigenous_40_a" class="mr-2"
                                 value="no" {{ old('indigenous_40_a', $data['indigenous_40_a'] ?? '') == 'no' ? 'checked' : '' }}>
                                <span>No</span>
                            </label>
                        </div>
                        <div id="indigenous-details" class="relative">
                                <input type="text" name="indigenous_40_a_details" placeholder=" " class="floating-label-input mt-3 w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                                value="{{ old('indigenous_40_a', $data['indigenous_40_a'] ?? '') }}" >
                                <label class="floating-label absolute mt-3 left-4 top-3 text-gray-500 pointer-events-none">Please specify</label>
                        </div>
                    </div>

                    <div class="question-card pds-question-card">
                        <p class="text-gray-700 font-bold mb-3">B. Are you a person with disability?</p>
                        <div class="flex gap-4"> <!-- NUMBER 40: b -->
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="pwd_40_b" class="mr-2" required
                                 value="yes" {{ old('pwd_40_b', $data['pwd_40_b'] ?? '') == 'yes' ? 'checked' : '' }}>
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="pwd_40_b" class="mr-2"
                                 value="no" {{ old('pwd_40_b', $data['pwd_40_b'] ?? '') == 'no' ? 'checked' : '' }}>
                                <span>No</span>
                            </label>
                        </div>
                        <div id="pwd-details" class="relative">
                                <input type="text" name="pwd_40_b_details" placeholder=" " class="floating-label-input mt-3 w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                                value="{{ old('pwd_40_b', $data['pwd_40_b'] ?? '') }}" >
                                <label class="floating-label absolute mt-3 left-4 top-3 text-gray-500 pointer-events-none">Specify ID No.</label>
                        </div>
                    </div>

                    <div class="question-card pds-question-card">
                        <p class="text-gray-700 font-bold mb-3">C. Are you a solo parent?</p>
                        <div class="flex gap-4"> <!-- NUMBER 40: c -->
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="solo_parent_40_c" class="mr-2" required
                                 value="yes" {{ old('solo_parent_40_c', $data['solo_parent_40_c'] ?? '') == 'yes' ? 'checked' : '' }}>
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="solo_parent_40_c" class="mr-2"
                                 value="no" {{ old('solo_parent_40_c', $data['solo_parent_40_c'] ?? '') == 'no' ? 'checked' : '' }}>
                                <span>No</span>
                            </label>
                        </div>
                        <div id="solo-parent-details" class="relative">
                                <input type="text" name="solo_parent_40_c_details" placeholder=" " class="floating-label-input mt-3 w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                                value="{{ old('solo_parent_40_c', $data['solo_parent_40_c'] ?? '') }}" >
                                <label class="floating-label absolute mt-3 left-4 top-3 text-gray-500 pointer-events-none">Specify ID No.</label>
                        </div>
                    </div>
                </div>
                </div>
            </section>

            <!-- References Section -->
            <section id="references-section" class="pds-flow-section bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="pds-section-title mb-6">
                    <span class="material-icons pds-section-icon text-blue-600 text-3xl">people</span>
                    <h2 class="text-2xl font-bold text-gray-900">41. REFERENCES</h2>
                </div>

                <p class="text-gray-600 mb-6 text-sm">
                    Person not related by consanguinity or affinity to applicant /appointee
                </p>

                <div class="space-y-6">
                    <!-- Reference 1 -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">REFERENCE 1</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <input required type="text" name="ref1_name" required value="{{ old('ref1_name', $data['ref1_name'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Name <span class="text-red-500">*</span></label>
                            </div>
                            <div class="relative">
                                <input required type="text" name="ref1_address" required value="{{ old('ref1_address', $data['ref1_address'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Address <span class="text-red-500">*</span></label>
                            </div>
                            <div class="relative md:col-span-2">
                                <input
                                    required
                                    type="text"
                                    name="ref1_tel"
                                    value="{{ old('ref1_tel', $data['ref1_tel'] ?? '') }}"
                                    placeholder=" "
                                    inputmode="text"
                                    class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer {{ $errors->has('ref1_tel') ? 'error-field' : '' }}"
                                    data-reference-contact>
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">CONTACT NO. AND/OR EMAIL <span class="text-red-500">*</span></label>
                                <p class="mt-2 text-xs text-gray-500">Format: 09XX XXX XXXX or enter a valid email address.</p>
                                <p class="error-message {{ $errors->has('ref1_tel') ? '' : 'hidden' }}" data-reference-contact-error aria-live="polite">{{ $errors->first('ref1_tel') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Reference 2 -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">REFERENCE 2</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <input required type="text" name="ref2_name" required value="{{ old('ref2_name', $data['ref2_name'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Name <span class="text-red-500">*</span></label>
                            </div>
                            <div class="relative">
                                <input required type="text" name="ref2_address" required value="{{ old('ref2_address', $data['ref2_address'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Address <span class="text-red-500">*</span></label>
                            </div>
                            <div class="relative md:col-span-2">
                                <input
                                    required
                                    type="text"
                                    name="ref2_tel"
                                    value="{{ old('ref2_tel', $data['ref2_tel'] ?? '') }}"
                                    placeholder=" "
                                    inputmode="text"
                                    class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer {{ $errors->has('ref2_tel') ? 'error-field' : '' }}"
                                    data-reference-contact>
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">CONTACT NO. AND/OR EMAIL <span class="text-red-500">*</span></label>
                                <p class="mt-2 text-xs text-gray-500">Format: 09XX XXX XXXX or enter a valid email address.</p>
                                <p class="error-message {{ $errors->has('ref2_tel') ? '' : 'hidden' }}" data-reference-contact-error aria-live="polite">{{ $errors->first('ref2_tel') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Reference 3 -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">REFERENCE 3</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <input required type="text" name="ref3_name" required value="{{ old('ref3_name', $data['ref3_name'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none ">Name <span class="text-red-500">*</span></label>
                            </div>
                            <div class="relative">
                                <input required type="text" name="ref3_address" required value="{{ old('ref3_address', $data['ref3_address'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Address <span class="text-red-500">*</span></label>
                            </div>
                            <div class="relative md:col-span-2">
                                <input
                                    required
                                    type="text"
                                    name="ref3_tel"
                                    value="{{ old('ref3_tel', $data['ref3_tel'] ?? '') }}"
                                    placeholder=" "
                                    inputmode="text"
                                    class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer {{ $errors->has('ref3_tel') ? 'error-field' : '' }}"
                                    data-reference-contact>
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">CONTACT NO. AND/OR EMAIL <span class="text-red-500">*</span></label>
                                <p class="mt-2 text-xs text-gray-500">Format: 09XX XXX XXXX or enter a valid email address.</p>
                                <p class="error-message {{ $errors->has('ref3_tel') ? '' : 'hidden' }}" data-reference-contact-error aria-live="polite">{{ $errors->first('ref3_tel') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            <!-- <div>
                <p>42. I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct, and complete statement pursuant to the provisions of pertinent laws, rules, and regulations of the Republic of the Philippines. I authorize the agency head/authorized representative to verify/validate the contents stated herein.          
                    I  agree that any misrepresentation made in this document and its attachments shall cause the filing of administrative/criminal case/s against me.</p>
            </div> -->
            </section>

            <!-- Government ID Section -->
            <section id="government-id-section" class="pds-flow-section bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="pds-section-title mb-6">
                    <span class="material-icons pds-section-icon text-blue-600 text-3xl">badge</span>
                    <h2 class="text-2xl font-bold text-gray-900">Government Issued IDs (i.e.Passport, GSIS, SSS, PRC, Driver's License, etc.)</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- ID Type Dropdown -->
                    <div class="relative">
                        <select id="govt_id_type" name="govt_id_type" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white" required>
                            <option value="">Select ID Type</option>
                            @php
                                $selectedId = old('govt_id_type', $data['govt_id_type'] ?? '');
                                $selectedIdNormalized = strtolower(trim((string) $selectedId));
                                $knownIdTypes = ['passport', 'gsis', 'sss', 'philhealth', "driver's license", 'prc', "voter's id", 'philsys/national id'];
                            @endphp
                            <option value="Passport" {{ $selectedIdNormalized === 'passport' ? 'selected' : '' }}>Passport</option>
                            <option value="GSIS" {{ $selectedIdNormalized === 'gsis' ? 'selected' : '' }}>GSIS</option>
                            <option value="SSS" {{ $selectedIdNormalized === 'sss' ? 'selected' : '' }}>SSS</option>
                            <option value="PhilHealth" {{ $selectedIdNormalized === 'philhealth' ? 'selected' : '' }}>PhilHealth</option>
                            <option value="Driver's License" {{ $selectedIdNormalized === "driver's license" ? 'selected' : '' }}>Driver's License</option>
                            <option value="PRC" {{ $selectedIdNormalized === 'prc' ? 'selected' : '' }}>PRC</option>
                            <option value="Voter's ID" {{ $selectedIdNormalized === "voter's id" ? 'selected' : '' }}>Voter's ID</option>
                            <option value="PhilSys/National ID" {{ $selectedIdNormalized === 'philsys/national id' ? 'selected' : '' }}>PhilSys/National ID</option>
                            <option value="other" {{ !in_array($selectedIdNormalized, $knownIdTypes, true) && $selectedIdNormalized !== '' ? 'selected' : '' }}>Other</option>
                        </select>
                        <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Government Issued ID: <span class="text-red-500">*</span></label>
                    </div>

                    <!-- If Other, Show Input Field -->
                    <div id="other-id-wrapper" class="relative {{ !in_array($selectedIdNormalized, $knownIdTypes, true) && $selectedIdNormalized !== '' ? '' : 'hidden' }}">
                        <input type="text" id="other_id_input" name="govt_id_other" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                            placeholder="Specify other ID type"
                            value="{{ old('govt_id_other', $data['govt_id_other'] ?? '') }}">
                    </div>

                    <!-- ID Number -->
                    <div class="relative">
                        <input type="text" name="govt_id_number" required value="{{ old('govt_id_number', $data['govt_id_number'] ?? '') }}"
                        class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer" placeholder=" ">
                        <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">ID/License/Passport No.: <span class="text-red-500">*</span></label>
                    </div>

                    @php
                        $govtDateIssuedRaw = old('govt_id_date_issued', $data['govt_id_date_issued'] ?? '');
                        $govtPlaceIssuedRaw = old('govt_id_place_issued', $data['govt_id_place_issued'] ?? '');
                        $isGovtDateNotApplicable = strtoupper(trim((string) $govtDateIssuedRaw)) === 'N/A';
                        $isGovtPlaceNotApplicable = strtoupper(trim((string) $govtPlaceIssuedRaw)) === 'N/A';
                        if ($isGovtDateNotApplicable && $isGovtPlaceNotApplicable) {
                            // Keep only one not-applicable flag active to enforce exclusivity.
                            $isGovtPlaceNotApplicable = false;
                            $govtPlaceIssuedRaw = '';
                        }
                        $govtDateIssuedValue = $isGovtDateNotApplicable ? '' : $govtDateIssuedRaw;
                        $govtPlaceIssuedValue = $isGovtPlaceNotApplicable ? '' : $govtPlaceIssuedRaw;
                    @endphp

                    <!-- Date Issued -->
                    <div class="relative">
                        <input
                            type="date"
                            id="govt_id_date_issued"
                            name="govt_id_date_issued"
                            value="{{ $govtDateIssuedValue }}"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Date of Issuance</label>
                        <!-- <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-600">
                            <input
                                type="checkbox"
                                id="govt_id_date_not_applicable"
                                name="govt_id_date_not_applicable"
                                value="1"
                                {{ $isGovtDateNotApplicable ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Check if not applicable</span>
                        </label> -->
                    </div>

                    <!-- Place Issued -->
                    <div class="relative">
                        <input
                            type="text"
                            id="govt_id_place_issued"
                            name="govt_id_place_issued"
                            required
                            value="{{ $govtPlaceIssuedValue }}"
                            {{ $isGovtPlaceNotApplicable ? 'disabled' : '' }}
                            class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                            placeholder=" ">
                        <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Place of Issuance <span class="text-red-500">*</span></label>
                        <!-- <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-600">
                            <input
                                type="checkbox"
                                id="govt_id_place_not_applicable"
                                name="govt_id_place_not_applicable"
                                value="1"
                                {{ $isGovtPlaceNotApplicable ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Check if not applicable</span>
                        </label> -->
                    </div>
                </div>
            </section>

            <!-- Photo Upload Section 
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">photo_camera</span>
                    <h2 class="text-2xl font-bold text-gray-900">XIV. Photo Upload</h2>
                </div>

                <div class="photo-upload-area" id="photo-upload-area">
                    <input type="file" id="photo-upload" name="photo_upload" accept="image/*" class="hidden" >
                            <span class="material-icons text-6xl text-blue-400 mb-4 block">cloud_upload</span>
                    <p class="text-gray-700 font-medium mb-2">Click to upload or drag and drop</p>
                    <p class="text-sm text-gray-500">Passport size photo (4.5cm x 3.5cm)</p>
                            <p class="text-xs text-gray-400 mt-2">PNG, JPG, GIF up to 10MB</p>
                    <img
                        id="photo-preview"
                        src="{{ $data['photo_preview_url'] ?? '' }}"
                        alt="Uploaded Photo"
                        class="mt-2 h-48 w-48 object-cover border rounded-md shadow"
                        style="{{ empty($data['photo_preview_url']) ? 'display:none;' : '' }}">
                </div>

                <div class="mt-4 text-sm text-gray-600">
                    <p class="flex items-center mb-2">
                        <span class="material-icons text-yellow-500 mr-2">warning</span>
                        Photo must be passport size (4.5 cm x 3.5 cm / approx. 170px x 133px)
                    </p>
                    <p class="flex items-center">
                        <span class="material-icons text-yellow-500 mr-2">info</span>
                        No computer generated or photocopied picture
                    </p>
                </div>
            </section>
            -->

            <!-- Navigation and Submit -->
            <div class="pds-submit-bar flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
                <button type="button" onclick="window.location.href='{{ route('display_c3', ['simple' => 1]) }}'" class="pds-back-button use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
                <button id="save-work-exp" type="button" class="pds-submit-button w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center">
                    Save
                    <span class="material-icons ml-2">arrow_forward</span>
                </button>
            </div>
        </form>

        <button
            type="button"
            id="pdsPreviewBtn"
            class="pds-preview-fab"
            aria-controls="pdsPreviewOverlay"
            aria-haspopup="dialog"
        >
            <span class="material-icons !text-base">visibility</span>
            Preview PDS
        </button>

        <!-- Confirmation Modal -->
        <div id="other-info-modal" class="hidden fixed inset-0 z-[2147483001] flex items-center justify-center px-4 pointer-events-none">
            <div class="absolute inset-0 z-0 bg-slate-900/45 backdrop-blur-sm pointer-events-none"></div>
            <div class="relative z-10 w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl border border-blue-100 animate-fade-in pointer-events-auto" role="dialog" aria-modal="true">
                <div class="bg-gradient-to-br from-[#001a45] via-[#002c76] to-[#0b4ea8] px-6 py-5 text-white sm:px-8">
                    <div class="flex items-center gap-4">
                        <span class="material-icons flex h-12 w-12 items-center justify-center rounded-full bg-white/15 text-3xl ring-1 ring-white/25">gavel</span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-100">Oath and Legal Reminder</p>
                            <h3 class="text-xl font-bold leading-tight sm:text-2xl">Are you sure you want to submit?</h3>
                        </div>
                    </div>
                </div>
                <div class="space-y-5 px-6 py-6 sm:px-8">
                    <div class="rounded-xl border border-blue-100 bg-blue-50/70 px-4 py-4 text-sm leading-relaxed text-slate-700">
                        <p class="font-semibold text-slate-900">Declaration under oath</p>
                        <p class="mt-2">42. I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct, and complete statement pursuant to the provisions of pertinent laws, rules, and regulations of the Republic of the Philippines.</p>
                    </div>
                    <div class="space-y-3">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-blue-100 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40">
                            <input type="checkbox" id="other-info-ack-oath" class="mt-1 h-4 w-4 rounded border-slate-300 text-[#002c76] focus:ring-[#002c76]">
                            <span>I authorize the agency head/authorized representative to verify/validate the contents stated herein.</span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-blue-100 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40">
                            <input type="checkbox" id="other-info-ack-legal" class="mt-1 h-4 w-4 rounded border-slate-300 text-[#002c76] focus:ring-[#002c76]">
                            <span>I agree that any misrepresentation made in this document and its attachments shall cause the filing of administrative/criminal case/s against me.</span>
                        </label>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                        <button type="button" id="other-info-cancel" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 pointer-events-auto">Review</button>
                        <button type="button" id="other-info-confirm" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#002c76] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-900/20 hover:bg-[#001f54] pointer-events-auto disabled:cursor-not-allowed disabled:bg-blue-300 disabled:text-white/90 disabled:shadow-none disabled:hover:bg-blue-300" disabled>
                            <span class="material-icons text-base">verified</span>
                            I Declare (5)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warning Footer -->
        <footer class="pds-warning-footer mt-12 text-center text-sm text-gray-600 px-4 py-4">
            <p class="mb-2">
                <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case/s against the person concerned.
            </p>
            <p>CS FORM 212 (Revised 2025), Page 4 of 4.</p>
        </footer>
        <div id="pdsPreviewOverlay" class="hidden fixed inset-0 z-[100] bg-black bg-opacity-50 p-4 sm:p-8 flex items-center justify-center">
            <div class="bg-white w-full max-w-6xl h-[90vh] overflow-hidden rounded-xl shadow-2xl flex flex-col">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b shrink-0">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Personal Data Sheet Preview</h3>
                    <button id="pdsPreviewClose" class="p-2 rounded hover:bg-gray-100">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                <div class="p-4 sm:p-6 flex-1 min-h-0">
                    <div class="mb-3 text-xs text-gray-500">Preview is rendered from the PDF template and auto-filled from your saved PDS data.</div>
                    <div class="w-full h-[calc(100%-1.75rem)] border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                        <iframe
                            id="pdsPdfPreviewFrame"
                            title="PDS PDF Preview"
                            src="about:blank"
                            data-preview-src="{{ route('pds.preview', ['embedded' => 1]) }}"
                            scrolling="no"
                            class="w-full h-full"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </main>
    @include('partials.loader')
    @endsection
    <style>
    body {
            font-family: 'Inter', sans-serif;
        }

        .modal-open {
            overflow: hidden;
        }

        .modal-open #sidebar {
            filter: blur(6px);
            opacity: 0.8;
            transition: filter 0.2s ease, opacity 0.2s ease;
        }

        /* Custom animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* Custom focus styles */
        .custom-focus:focus {
            outline: none;
            ring: 2px;
            ring-offset: 2px;
            ring-blue-500;
            border-color: #3b82f6;
        }

        /* Floating label styles */
        .floating-label {
            transition: all 0.2s ease-out;
        }

        .floating-label-input:focus + .floating-label,
        .floating-label-input:not(:placeholder-shown) + .floating-label {
            transform: translateY(-1.25rem) scale(0.85);
            color: #3b82f6;
            background-color: white;
            padding: 0 0.25rem;
        }

        /* Glass morphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }


        /* Radio and checkbox custom styles */
        input[type="radio"], input[type="checkbox"] {
            -webkit-appearance: none;
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 0.25rem;
            transition: all 0.15s ease;
            position: relative;
            cursor: pointer;
        }

        input[type="radio"] {
            border-radius: 50%;
        }

        input[type="radio"]:checked, input[type="checkbox"]:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        input[type="radio"]:checked::after, input[type="checkbox"]:checked::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        input[type="radio"]:checked::after {
            width: 0.5rem;
            height: 0.5rem;
            background: white;
            border-radius: 50%;
        }

        input[type="checkbox"]:checked::after {
            width: 0.375rem;
            height: 0.625rem;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: translate(-50%, -60%) rotate(45deg);
        }

        /* Photo upload area styles */
        /*
        .photo-upload-area {
            border: 2px dashed #3b82f6;
            border-radius: 1rem;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            
        }

        .photo-upload-area:hover {
            background-color: #eff6ff;
            border-color: #2563eb;
        }

        .photo-upload-area.dragover {
            background-color: #dbeafe;
            border-color: #1d4ed8;
            transform: scale(0.98);
        }

        #photo-preview {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 1rem;
            z-index: 0;
            background-color: white; /* Optional: fill empty space */
        }
        */

        /* certificate styles */
        .cert-upload-area {
            border: 2px dashed #3b82f6;
            border-radius: 1rem;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .cert-upload-area:hover {
            background-color: #eff6ff;
            border-color: #2563eb;
        }

        .cert-upload-area.dragover {
            background-color: #dbeafe;
            border-color: #1d4ed8;
            transform: scale(0.98);
        }

        .question-card {
            border-left: 4px solid #3b82f6;
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-input {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f3f4f6;
            border-radius: 0.5rem;
            display: none;
        }

        .detail-input.show {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }
    </style>
    <script>
        let skipValidation = false;

        document.addEventListener('DOMContentLoaded', function() {
            const detailConfigs = [
                { name: 'related_34_b', detailId: 'related-details', toggleHidden: false },
                { name: 'guilty_35_a', detailId: 'admin-details', toggleHidden: false },
                { name: 'criminal_35_b', detailId: 'criminal-details', toggleHidden: true },
                { name: 'convicted_36', detailId: 'convicted-details', toggleHidden: false },
                { name: 'separated_37', detailId: 'separated-details', toggleHidden: false },
                { name: 'candidate_38_a', detailId: 'candidate-details', toggleHidden: false },
                { name: 'resigned_38_b', detailId: 'resign-details', toggleHidden: false },
                { name: 'immigrant_39', detailId: 'immigrant-details', toggleHidden: false },
                { name: 'indigenous_40_a', detailId: 'indigenous-details', toggleHidden: true },
                { name: 'pwd_40_b', detailId: 'pwd-details', toggleHidden: true },
                { name: 'solo_parent_40_c', detailId: 'solo-parent-details', toggleHidden: true },
            ];

            function resetField(field) {
                if (field.type === 'radio' || field.type === 'checkbox') {
                    field.checked = false;
                } else {
                    field.value = '';
                }
            }

            function toggleDetail(config, value) {
                const detailDiv = document.getElementById(config.detailId);
                if (!detailDiv) return;

                const fields = detailDiv.querySelectorAll('input, textarea, select');
                const show = value === 'yes';
                detailDiv.classList.toggle('show', show);
                if (config.toggleHidden) {
                    detailDiv.classList.toggle('hidden', !show);
                }

                fields.forEach((field) => {
                    field.required = show;
                    if (!show && value === 'no') {
                        resetField(field);
                    }
                });
            }

            detailConfigs.forEach((config) => {
                const radios = document.querySelectorAll(`input[name="${config.name}"]`);
                const checkedRadio = Array.from(radios).find((radio) => radio.checked);

                radios.forEach((radio) => {
                    radio.addEventListener('change', () => toggleDetail(config, radio.value));
                });

                toggleDetail(config, checkedRadio ? checkedRadio.value : '');
            });

            /*
            // Photo upload functionality
            const uploadArea = document.getElementById('photo-upload-area');
            const photoUpload = document.getElementById('photo-upload');
            const photoPreview = document.getElementById('photo-preview');

            uploadArea.addEventListener('click', () => photoUpload.click());

            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');

                const files = e.dataTransfer.files;
                if (files.length > 0 && files[0].type.startsWith('image/')) {
                    handlePhotoUpload(files[0]);
                }
            });

            photoUpload.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handlePhotoUpload(e.target.files[0]);
                }
            });

            function handlePhotoUpload(file) {
                if (file.size > 10 * 1024 * 1024) {
                    showAppToast('File size must be less than 10MB');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    photoPreview.src = e.target.result;
                    photoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }

            */



            const form = document.getElementById('other-info-form');
            const referenceContactInputs = Array.from(document.querySelectorAll('[data-reference-contact]'));
            const nextBtn = document.getElementById('save-work-exp');
            const modal = document.getElementById('other-info-modal');
            const modalConfirm = document.getElementById('other-info-confirm');
            const modalCancel = document.getElementById('other-info-cancel');
            const modalAckOath = document.getElementById('other-info-ack-oath');
            const modalAckLegal = document.getElementById('other-info-ack-legal');
            const focusTargets = [modalConfirm, modalCancel];
            const modalConfirmBaseLabel = 'I Declare';
            const modalConfirmDelaySeconds = 5;
            let modalConfirmCountdownTimer = null;
            let modalConfirmRemainingSeconds = modalConfirmDelaySeconds;
            const setModalConfirmLabel = (label) => {
                if (!modalConfirm) return;
                modalConfirm.innerHTML = '<span class="material-icons text-base">verified</span>' + label;
            };
            const showSystemLoader = (message = 'Loading...') => {
                const loader = document.getElementById('loader');
                if (!loader) return;

                loader.classList.remove('hidden');
                loader.classList.remove('pds-loading-nonblocking');
                loader.setAttribute('aria-busy', 'true');

                const loaderText = document.getElementById('loader-text');
                if (loaderText) {
                    loaderText.textContent = message;
                }

                const loaderLive = document.getElementById('loader-live');
                if (loaderLive) {
                    loaderLive.textContent = message;
                }
            };
            const canEnableModalConfirm = () => {
                return Boolean(
                    modalConfirm &&
                    modalConfirmRemainingSeconds <= 0 &&
                    modalAckOath?.checked &&
                    modalAckLegal?.checked
                );
            };
            const syncModalConfirmState = () => {
                if (!modalConfirm) return;
                const countdownActive = modalConfirmRemainingSeconds > 0;
                modalConfirm.disabled = !canEnableModalConfirm();
                setModalConfirmLabel(countdownActive
                    ? `${modalConfirmBaseLabel} (${modalConfirmRemainingSeconds})`
                    : modalConfirmBaseLabel);
            };
            const resetModalConfirmCountdown = () => {
                if (!modalConfirm) return;
                window.clearInterval(modalConfirmCountdownTimer);
                modalConfirmRemainingSeconds = modalConfirmDelaySeconds;
                syncModalConfirmState();

                modalConfirmCountdownTimer = window.setInterval(() => {
                    modalConfirmRemainingSeconds -= 1;
                    if (modalConfirmRemainingSeconds <= 0) {
                        modalConfirmRemainingSeconds = 0;
                        window.clearInterval(modalConfirmCountdownTimer);
                        syncModalConfirmState();
                        return;
                    }
                    syncModalConfirmState();
                }, 1000);
            };
            const toggleModal = (show) => {
                if (!modal) return;
                modal.classList.toggle('hidden', !show);
                document.body.classList.toggle('modal-open', show);
                if (show && modalConfirm) {
                    if (modalAckOath) modalAckOath.checked = false;
                    if (modalAckLegal) modalAckLegal.checked = false;
                    resetModalConfirmCountdown();
                    try {
                        modalCancel?.focus({ preventScroll: true });
                    } catch (error) {
                        modalCancel?.focus();
                    }
                } else {
                    window.clearInterval(modalConfirmCountdownTimer);
                    if (modalConfirm) {
                        modalConfirmRemainingSeconds = modalConfirmDelaySeconds;
                        syncModalConfirmState();
                    }
                    if (modalAckOath) modalAckOath.checked = false;
                    if (modalAckLegal) modalAckLegal.checked = false;
                    if (modalCancel) modalCancel.textContent = 'Review';
                }
            };
            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (form && !form.reportValidity()) {
                        return;
                    }
                    toggleModal(true);
                });
            }
            const submitOtherInfoForm = () => {
                if (!form) return;
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                    return;
                }
                form.submit();
            };

            if (modalCancel) {
                modalCancel.addEventListener('click', (event) => {
                    event.preventDefault();
                    toggleModal(false);
                });
            }

            if (modalConfirm) {
                modalConfirm.addEventListener('click', (event) => {
                    event.preventDefault();
                    if (modalConfirm.disabled) {
                        return;
                    }
                    toggleModal(false);
                    submitOtherInfoForm();
                });
            }

            [modalAckOath, modalAckLegal].forEach((checkbox) => {
                if (!checkbox) return;
                checkbox.addEventListener('change', () => {
                    syncModalConfirmState();
                });
            });

            document.addEventListener('keydown', (ev) => {
                if (modal && modal.classList.contains('hidden')) return;
                if (ev.key === 'Escape') {
                    toggleModal(false);
                }
                if (ev.key === 'Tab' && focusTargets.filter(Boolean).length) {
                    const focusable = focusTargets.filter(Boolean);
                    const current = document.activeElement;
                    const idx = focusable.indexOf(current);
                    if (idx === -1) {
                        focusable[0].focus();
                        ev.preventDefault();
                        return;
                    }
                    ev.preventDefault();
                    const nextIdx = ev.shiftKey ? (idx - 1 + focusable.length) % focusable.length : (idx + 1) % focusable.length;
                    focusable[nextIdx].focus();
                }
            });

            function formatReferencePhoneNumber(digits) {
                const trimmedDigits = digits.replace(/\D/g, '').slice(0, 11);

                if (trimmedDigits.length <= 4) {
                    return trimmedDigits;
                }

                if (trimmedDigits.length <= 7) {
                    return `${trimmedDigits.slice(0, 4)} ${trimmedDigits.slice(4)}`;
                }

                return `${trimmedDigits.slice(0, 4)} ${trimmedDigits.slice(4, 7)} ${trimmedDigits.slice(7)}`;
            }

            function setReferenceContactValidity(input, message) {
                const errorElement = input.parentElement.querySelector('[data-reference-contact-error]');
                const hasError = Boolean(message);

                input.classList.toggle('error-field', hasError);
                input.setAttribute('aria-invalid', hasError ? 'true' : 'false');
                input.setCustomValidity(message);

                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.classList.toggle('hidden', !hasError);
                }
            }

            function clearReferenceContactValidity(input) {
                setReferenceContactValidity(input, '');
            }

            function normalizeReferenceContactDraft(input) {
                const currentValue = input.value;
                const isEmailValue = currentValue.includes('@') || /[A-Za-z]/.test(currentValue);

                if (isEmailValue) {
                    return;
                }

                const digits = currentValue.replace(/\D/g, '').slice(0, 11);
                input.value = digits;
            }

            function validateReferenceContactInput(input) {
                const trimmedValue = input.value.trim();

                if (trimmedValue === '') {
                    clearReferenceContactValidity(input);
                    return true;
                }

                // Extract only digits to check if it starts with 09 or +63
                const digits = trimmedValue.replace(/\D/g, '');
                const startsWithPhone = /^09/.test(digits) || trimmedValue.includes('+63');

                // If it contains @ or starts with phone format, determine type
                if (trimmedValue.includes('@') || startsWithPhone) {
                    if (startsWithPhone) {
                        // Treat as phone number
                        const isValidPhoneNumber = /^09\d{9}$/.test(digits) || /^639\d{9}$/.test(digits);
                        const message = isValidPhoneNumber ? '' : 'Enter an 11-digit contact number in the format 09XX XXX XXXX or +63 9XX XXX XXXX.';
                        setReferenceContactValidity(input, message);
                        return isValidPhoneNumber;
                    } else {
                        // Treat as email (allow spaces)
                        const isValidEmail = /^[\w\s.+-]+@[\w.-]+\.[a-zA-Z]{2,}$/.test(trimmedValue);
                        setReferenceContactValidity(input, isValidEmail ? '' : 'Enter a valid email address.');
                        return isValidEmail;
                    }
                }

                // If no @ and doesn't start with phone pattern, treat as phone
                const isValidPhoneNumber = /^09\d{9}$/.test(digits) || /^639\d{9}$/.test(digits);
                const message = isValidPhoneNumber ? '' : 'Enter an 11-digit contact number in the format 09XX XXX XXXX or +63 9XX XXX XXXX.';
                setReferenceContactValidity(input, message);
                return isValidPhoneNumber;
            }

            referenceContactInputs.forEach((input) => {
                input.addEventListener('input', () => {
                    normalizeReferenceContactDraft(input);
                    clearReferenceContactValidity(input);
                });
                input.addEventListener('blur', () => {
                    input.value = input.value.trim();
                    validateReferenceContactInput(input);
                });
            });

            // Form submission
            form.addEventListener('submit', (e) => {
                if (skipValidation) {
                    return; // Skip this logic if flag is true
                }

                e.preventDefault();

                referenceContactInputs.forEach((input) => validateReferenceContactInput(input));

                // Force HTML5 validation
                if (!form.checkValidity()) {
                    form.reportValidity(); // Show validation messages
                    return; // Stop if invalid
                }

                // Simulate form submission
                const submitBtn = e.submitter || modalConfirm || nextBtn;
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="material-icons mr-2 animate-spin">refresh</span>Submitting...';
                }

                showSystemLoader('Saving changes...');

                form.submit();
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
        const idSelect = document.getElementById('govt_id_type');
        const otherIdWrapper = document.getElementById('other-id-wrapper');
        const otherIdInput = document.getElementById('other_id_input');
        const form = document.getElementById('other-info-form');
        const govtDateIssuedInput = document.getElementById('govt_id_date_issued');
        const govtPlaceIssuedInput = document.getElementById('govt_id_place_issued');
        const govtDateNotApplicableCheckbox = document.getElementById('govt_id_date_not_applicable');
        const govtPlaceNotApplicableCheckbox = document.getElementById('govt_id_place_not_applicable');

        function handleIdSelectChange() {
            const isOther = idSelect && idSelect.value === 'other';
            if (isOther) {
                otherIdWrapper.classList.remove('hidden');
            } else {
                otherIdWrapper.classList.add('hidden');
                otherIdInput.value = '';
            }
            if (otherIdInput) {
                otherIdInput.required = isOther;
            }
        }

        idSelect?.addEventListener('change', handleIdSelectChange);
        handleIdSelectChange();

        function upsertNotApplicableHiddenInput(fieldName, marker, shouldEnable) {
            if (!form) {
                return;
            }

            const selector = `input[type="hidden"][data-na-hidden="${marker}"]`;
            let hidden = form.querySelector(selector);

            if (shouldEnable) {
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = fieldName;
                    hidden.setAttribute('data-na-hidden', marker);
                    form.appendChild(hidden);
                }
                hidden.value = 'N/A';
                return;
            }

            if (hidden) {
                hidden.remove();
            }
        }

        function syncGovtIssueNotApplicable(source = null) {
            if (!govtDateNotApplicableCheckbox || !govtPlaceNotApplicableCheckbox) {
                return;
            }

            if (govtDateNotApplicableCheckbox.checked && govtPlaceNotApplicableCheckbox.checked) {
                if (source === 'place') {
                    govtDateNotApplicableCheckbox.checked = false;
                } else {
                    govtPlaceNotApplicableCheckbox.checked = false;
                }
            }

            if (source === 'date' && govtDateNotApplicableCheckbox.checked) {
                govtPlaceNotApplicableCheckbox.checked = false;
            }

            if (source === 'place' && govtPlaceNotApplicableCheckbox.checked) {
                govtDateNotApplicableCheckbox.checked = false;
            }

            if (govtDateIssuedInput) {
                govtDateIssuedInput.disabled = govtDateNotApplicableCheckbox.checked;
                if (govtDateNotApplicableCheckbox.checked) {
                    govtDateIssuedInput.value = '';
                }
            }

            if (govtPlaceIssuedInput) {
                govtPlaceIssuedInput.disabled = govtPlaceNotApplicableCheckbox.checked;
                govtPlaceIssuedInput.required = !govtPlaceNotApplicableCheckbox.checked;
                if (govtPlaceNotApplicableCheckbox.checked) {
                    govtPlaceIssuedInput.value = '';
                }
            }

            upsertNotApplicableHiddenInput('govt_id_date_issued', 'date-issued', govtDateNotApplicableCheckbox.checked);
            upsertNotApplicableHiddenInput('govt_id_place_issued', 'place-issued', govtPlaceNotApplicableCheckbox.checked);
        }

        govtDateNotApplicableCheckbox?.addEventListener('change', function () {
            syncGovtIssueNotApplicable('date');
        });

        govtPlaceNotApplicableCheckbox?.addEventListener('change', function () {
            syncGovtIssueNotApplicable('place');
        });

        syncGovtIssueNotApplicable();

        // Before form submission, replace `govt_id_type` value with `other_id_input` if 'Other' is selected
        form?.addEventListener('submit', function (e) {
            syncGovtIssueNotApplicable();

            if (idSelect.value === 'other' && otherIdInput.value.trim() !== '') {
                const tempInput = document.createElement('input');
                tempInput.type = 'hidden';
                tempInput.name = 'govt_id_type';
                tempInput.value = otherIdInput.value.trim();
                this.appendChild(tempInput);
                idSelect.disabled = true; // prevent submission of the default select
            }
        });
    });

    function submit(location){
        const form = document.querySelector('#other-info-form');
        const simpleParam = new URLSearchParams(window.location.search).get('simple');
        const simpleQuery = simpleParam ? `?simple=${simpleParam}` : '';
        form.action = `/pds/submit_c4/${location}${simpleQuery}`;

        skipValidation = true; // Set flag before submitting
        form.requestSubmit();
        skipValidation = false; // Reset flag after submitting
    }
    </script>
    <script>
        (function () {
            function initPdsPreview() {
                const openBtn = document.getElementById('pdsPreviewBtn');
                const overlay = document.getElementById('pdsPreviewOverlay');
                const closeBtn = document.getElementById('pdsPreviewClose');
                const frame = document.getElementById('pdsPdfPreviewFrame');
                if (!openBtn || !overlay || !closeBtn || !frame) return;

                const closeOverlay = () => overlay.classList.add('hidden');

                openBtn.addEventListener('click', async () => {
                    const lastDragAt = Number(openBtn.dataset.lastDragAt || '0');
                    if (Date.now() - lastDragAt < 250) {
                        return;
                    }

                    if (openBtn.disabled) return;

                    const originalText = openBtn.innerHTML;
                    openBtn.innerHTML = '<span class="material-icons text-sm animate-spin">autorenew</span>Preparing...';
                    openBtn.disabled = true;

                    try {
                        if (typeof window.__pdsAutosaveNow === 'function') {
                            await window.__pdsAutosaveNow({ force: true, maxWaitMs: 5000 });
                        }

                        const previewSrc = frame.dataset.previewSrc || @json(route('pds.preview'));
                        const separator = previewSrc.includes('?') ? '&' : '?';
                        frame.src = previewSrc + separator + 'ts=' + Date.now();
                        overlay.classList.remove('hidden');
                    } finally {
                        openBtn.innerHTML = originalText;
                        openBtn.disabled = false;
                    }
                });

                closeBtn.addEventListener('click', closeOverlay);
                overlay.addEventListener('click', (event) => {
                    if (event.target === overlay) {
                        closeOverlay();
                    }
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !overlay.classList.contains('hidden')) {
                        closeOverlay();
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPdsPreview, { once: true });
            } else {
                initPdsPreview();
            }
        })();
    </script>
    <script>
        (function () {
            function initDraggablePdsPreviewButton() {
                const button = document.getElementById('pdsPreviewBtn');
                if (!button) return;

                let isPointerDown = false;
                let isDragging = false;
                let pointerId = null;
                let startX = 0;
                let startY = 0;
                let originLeft = 0;
                let originTop = 0;
                let suppressClick = false;

                const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

                const getViewportBounds = () => {
                    const rect = button.getBoundingClientRect();
                    return {
                        minLeft: 8,
                        minTop: 8,
                        maxLeft: Math.max(8, window.innerWidth - rect.width - 8),
                        maxTop: Math.max(8, window.innerHeight - rect.height - 8),
                    };
                };

                const applyPosition = (left, top) => {
                    button.style.left = `${left}px`;
                    button.style.top = `${top}px`;
                    button.style.right = 'auto';
                    button.style.bottom = 'auto';
                };

                const syncToViewport = () => {
                    const rect = button.getBoundingClientRect();
                    const bounds = getViewportBounds();
                    applyPosition(
                        clamp(rect.left, bounds.minLeft, bounds.maxLeft),
                        clamp(rect.top, bounds.minTop, bounds.maxTop)
                    );
                };

                button.addEventListener('pointerdown', (event) => {
                    if (event.button !== 0) return;

                    const rect = button.getBoundingClientRect();
                    isPointerDown = true;
                    isDragging = false;
                    pointerId = event.pointerId;
                    startX = event.clientX;
                    startY = event.clientY;
                    originLeft = rect.left;
                    originTop = rect.top;

                    applyPosition(originLeft, originTop);
                    button.setPointerCapture(pointerId);
                });

                button.addEventListener('pointermove', (event) => {
                    if (!isPointerDown || event.pointerId !== pointerId) return;

                    const deltaX = event.clientX - startX;
                    const deltaY = event.clientY - startY;

                    if (!isDragging && (Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4)) {
                        isDragging = true;
                        button.classList.add('is-dragging');
                    }

                    if (!isDragging) return;

                    event.preventDefault();
                    const bounds = getViewportBounds();
                    applyPosition(
                        clamp(originLeft + deltaX, bounds.minLeft, bounds.maxLeft),
                        clamp(originTop + deltaY, bounds.minTop, bounds.maxTop)
                    );
                });

                button.addEventListener('pointerup', (event) => {
                    if (event.pointerId !== pointerId) return;

                    if (isDragging) {
                        event.preventDefault();
                        event.stopPropagation();
                        suppressClick = true;
                        button.dataset.lastDragAt = String(Date.now());
                    }

                    button.classList.remove('is-dragging');
                    isPointerDown = false;
                    isDragging = false;
                    pointerId = null;
                });

                button.addEventListener('pointercancel', () => {
                    button.classList.remove('is-dragging');
                    isPointerDown = false;
                    isDragging = false;
                    pointerId = null;
                });

                button.addEventListener('click', (event) => {
                    if (suppressClick) {
                        event.preventDefault();
                        event.stopPropagation();
                        suppressClick = false;
                    }
                }, true);

                window.addEventListener('resize', syncToViewport);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initDraggablePdsPreviewButton, { once: true });
            } else {
                initDraggablePdsPreviewButton();
            }
        })();
    </script>
    <script>
        (function () {
            function initC4ErrorToast() {
                const errorMessages = @json($errors->all());
                const sessionError = @json(session('error'));
                const firstError = sessionError || (errorMessages.length ? errorMessages[0] : '');
                if (!firstError) return;

                const message = `${firstError}${errorMessages.length > 1 ? ` (+${errorMessages.length - 1} more)` : ''}`;
                if (typeof window.showNotification === 'function') {
                    window.showNotification(message, 'error');
                    return;
                }
                if (typeof window.showAppToast === 'function') {
                    window.showAppToast(message, 'error', 6000);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initC4ErrorToast, { once: true });
            } else {
                initC4ErrorToast();
            }
        })();
    </script>
    <script>
        (function () {
            function initAutosave() {
            const form = document.getElementById('other-info-form');
            if (!form) return;

            const autosaveUrl = @json(route('pds.autosave', ['section' => 'c4']));
            const LOCAL_DRAFT_KEY = @json('dilg-car:pds:c4:draft:' . (string) (Auth::id() ?? 'guest'));
            const AUTOSAVE_INTERVAL_MS = 15000;
            const AUTOSAVE_DEBOUNCE_MS = 600;
            let isDirty = false;
            let isSubmitting = false;
            let inFlight = false;
            let queued = false;
            let isRestoringDraft = false;
            let draftVersion = 0;
            let autosaveTimer = null;

            function updateDraftStatus() {}

            function formatDraftTime(timestamp) {
                if (!timestamp) {
                    return 'just now';
                }

                const parsed = new Date(timestamp);
                if (Number.isNaN(parsed.getTime())) {
                    return 'just now';
                }

                return parsed.toLocaleTimeString([], {
                    hour: 'numeric',
                    minute: '2-digit',
                });
            }

            function getNamedFields(name) {
                return Array.from(form.elements).filter((field) => field.name === name);
            }

            function formHasMeaningfulData() {
                return Array.from(form.elements).some((field) => {
                    if (!field.name || field.disabled || field.name === '_token') {
                        return false;
                    }

                    if (['file', 'submit', 'button', 'reset'].includes(field.type)) {
                        return false;
                    }

                    if (field.type === 'radio' || field.type === 'checkbox') {
                        return field.checked;
                    }

                    return String(field.value ?? '').trim() !== '';
                });
            }

            function collectDraftData() {
                const data = {};

                Array.from(form.elements).forEach((field) => {
                    if (!field.name || field.disabled || field.name === '_token') {
                        return;
                    }

                    if (['file', 'submit', 'button', 'reset'].includes(field.type)) {
                        return;
                    }

                    if (field.type === 'radio') {
                        if (!Object.prototype.hasOwnProperty.call(data, field.name)) {
                            data[field.name] = '';
                        }

                        if (field.checked) {
                            data[field.name] = field.value;
                        }

                        return;
                    }

                    if (field.type === 'checkbox') {
                        if (!Array.isArray(data[field.name])) {
                            data[field.name] = [];
                        }

                        if (field.checked) {
                            data[field.name].push(field.value);
                        }

                        return;
                    }

                    data[field.name] = field.value;
                });

                return data;
            }

            function persistLocalDraft(unsynced = true, savedAt = new Date().toISOString()) {
                try {
                    window.localStorage.setItem(LOCAL_DRAFT_KEY, JSON.stringify({
                        unsynced,
                        savedAt,
                        data: collectDraftData(),
                    }));
                    return true;
                } catch (error) {
                    return false;
                }
            }

            function readLocalDraft() {
                try {
                    const raw = window.localStorage.getItem(LOCAL_DRAFT_KEY);
                    if (!raw) {
                        return null;
                    }

                    const parsed = JSON.parse(raw);
                    return parsed && typeof parsed === 'object' ? parsed : null;
                } catch (error) {
                    return null;
                }
            }

            function shouldShowDraftRestoreToast() {
                const navigation = performance.getEntriesByType?.('navigation')?.[0];
                return navigation?.type === 'reload' || performance.navigation?.type === 1;
            }

            function draftDataDiffers(localDraftData) {
                if (!localDraftData || typeof localDraftData !== 'object') {
                    return false;
                }

                const currentData = collectDraftData();
                const fieldNames = new Set([
                    ...Object.keys(currentData),
                    ...Object.keys(localDraftData),
                ]);

                for (const fieldName of fieldNames) {
                    const currentValue = currentData[fieldName];
                    const localValue = localDraftData[fieldName];

                    if (Array.isArray(currentValue) || Array.isArray(localValue)) {
                        const currentArray = Array.isArray(currentValue) ? currentValue : [];
                        const localArray = Array.isArray(localValue) ? localValue : [];

                        if (currentArray.length !== localArray.length) {
                            return true;
                        }

                        if (currentArray.some((value, index) => value !== localArray[index])) {
                            return true;
                        }

                        continue;
                    }

                    if (String(currentValue ?? '') !== String(localValue ?? '')) {
                        return true;
                    }
                }

                return false;
            }

            function restoreLocalDraftIfNeeded() {
                const localDraft = readLocalDraft();
                if (!localDraft || !localDraft.data || typeof localDraft.data !== 'object') {
                    return false;
                }

                const localDraftDiffersFromForm = draftDataDiffers(localDraft.data);

                if (!localDraftDiffersFromForm) {
                    return false;
                }

                const restoredFields = new Set();
                isRestoringDraft = true;

                Object.entries(localDraft.data).forEach(([name, value]) => {
                    const fields = getNamedFields(name);
                    if (!fields.length) {
                        return;
                    }

                    const firstField = fields[0];

                    if (firstField.type === 'radio') {
                        const checkedField = fields.find((field) => field.value === value) ?? null;
                        fields.forEach((field) => {
                            field.checked = field.value === value;
                        });
                        if (checkedField) {
                            restoredFields.add(checkedField);
                        }
                        return;
                    }

                    if (firstField.type === 'checkbox') {
                        const values = Array.isArray(value) ? value : [value];
                        fields.forEach((field) => {
                            field.checked = values.includes(field.value);
                        });
                        restoredFields.add(firstField);
                        return;
                    }

                    const normalizedValue = firstField.type === 'date' && String(value).toUpperCase() === 'N/A'
                        ? ''
                        : String(value ?? '');

                    firstField.value = normalizedValue;
                    restoredFields.add(firstField);
                });

                restoredFields.forEach((field) => {
                    field.dispatchEvent(new Event('change', { bubbles: true }));

                    if (!['radio', 'checkbox', 'select-one', 'select-multiple'].includes(field.type)) {
                        field.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });

                isRestoringDraft = false;

                isDirty = true;
                updateDraftStatus(`Unsynced draft restored from this browser at ${formatDraftTime(localDraft.savedAt)}.`, 'warning');

                if (shouldShowDraftRestoreToast() && typeof window.showAppToast === 'function') {
                    window.showAppToast('A saved C4 draft was restored from this browser.', 'warning', 4000);
                }

                return true;
            }

            const markDirty = () => {
                if (isRestoringDraft) {
                    return;
                }

                isDirty = true;
                draftVersion += 1;
                window.clearTimeout(autosaveTimer);
                autosaveTimer = window.setTimeout(() => {
                    saveDraft(false);
                }, AUTOSAVE_DEBOUNCE_MS);
                updateDraftStatus(
                    navigator.onLine
                        ? 'Saving draft...'
                        : 'Offline: draft saved on this device.',
                    navigator.onLine ? 'info' : 'warning'
                );
                persistLocalDraft(true);
            };

            form.addEventListener('input', markDirty);
            form.addEventListener('change', markDirty);
            form.addEventListener('submit', () => {
                isSubmitting = true;
                window.clearTimeout(autosaveTimer);
                persistLocalDraft(true);
            });

            async function saveDraft(force = false) {
                if (isSubmitting) return;
                if (!force && !isDirty) return;
                if (inFlight) {
                    queued = true;
                    return;
                }
                if (!navigator.onLine) {
                    persistLocalDraft(true);
                    return;
                }

                inFlight = true;
                const versionAtRequestStart = draftVersion;
                try {
                    const formData = new FormData(form);
                    const response = await fetch(autosaveUrl, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    });
                    if (response.ok) {
                        const payload = await response.json().catch(() => null);
                        if (payload?.ok === true && draftVersion === versionAtRequestStart) {
                            isDirty = false;
                            persistLocalDraft(false, payload?.saved_at ?? new Date().toISOString());
                        } else {
                            persistLocalDraft(true);
                        }
                    } else {
                        persistLocalDraft(true);
                    }
                } catch (error) {
                    persistLocalDraft(true);
                } finally {
                    inFlight = false;
                    if (queued) {
                        queued = false;
                        saveDraft(true);
                    }
                }
            }

            const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

            async function flushDraftNow(options = {}) {
                const force = options.force === true;
                const parsedMaxWaitMs = Number(options.maxWaitMs);
                const maxWaitMs = Number.isFinite(parsedMaxWaitMs) && parsedMaxWaitMs > 0
                    ? parsedMaxWaitMs
                    : 1200;
                const startedAt = Date.now();

                while (inFlight && (Date.now() - startedAt) < maxWaitMs) {
                    if (force) {
                        queued = true;
                    }
                    await sleep(80);
                }

                if (inFlight) {
                    return false;
                }

                await saveDraft(force);

                while ((inFlight || queued) && (Date.now() - startedAt) < maxWaitMs) {
                    if (!inFlight && queued) {
                        queued = false;
                        await saveDraft(force);
                        continue;
                    }
                    await sleep(80);
                }

                return !(inFlight || queued);
            }

            window.__pdsAutosaveNow = flushDraftNow;

            const restoredDraft = restoreLocalDraftIfNeeded();
            if (restoredDraft && navigator.onLine) {
                saveDraft(true);
            }

            setInterval(() => saveDraft(false), AUTOSAVE_INTERVAL_MS);

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    window.clearTimeout(autosaveTimer);
                    persistLocalDraft(true);
                }
                if (document.hidden && isDirty) {
                    saveDraft(true);
                }
            });

            window.addEventListener('online', () => {
                saveDraft(true);
            });

            window.addEventListener('offline', () => {
                persistLocalDraft(true);
            });

            window.addEventListener('pagehide', () => {
                window.clearTimeout(autosaveTimer);
                persistLocalDraft(true);
                if (!isDirty || isSubmitting || !navigator.sendBeacon || !navigator.onLine) return;
                const formData = new FormData(form);
                navigator.sendBeacon(autosaveUrl, formData);
            });

            window.addEventListener('beforeunload', () => {
                window.clearTimeout(autosaveTimer);
                persistLocalDraft(true);
                if (!isDirty || isSubmitting || !navigator.sendBeacon || !navigator.onLine) return;
                const formData = new FormData(form);
                navigator.sendBeacon(autosaveUrl, formData);
            });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAutosave, { once: true });
            } else {
                initAutosave();
            }
        })();
    </script>
    
    
