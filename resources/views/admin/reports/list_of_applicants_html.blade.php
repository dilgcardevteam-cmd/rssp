@extends('admin.reports.report_layout')

@section('title', 'List of Applicants')
@section('report_title', 'LIST OF APPLICANTS')

@section('content')
    <div class="meta-info">
        <table>
            <tr>
                <td style="width: 180px;">Date published:</td>
                <td><span class="meta-line">{{ $vacancy->created_at->format('F j, Y') }}</span></td>
            </tr>
            <tr>
                <td>Publication Control No.:</td>
                <td><span class="meta-line">{{ $vacancy->pcn_no ?: '________________' }}</span></td>
            </tr>
            <tr>
                <td>Due date of application:</td>
                <td><span class="meta-line">{{ \Carbon\Carbon::parse($vacancy->closing_date)->format('F j, Y') }}</span></td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">POSITION/LOCATION</th>
                <th style="width: 30%;">PWD/PSN<br><span style="font-size: 8px; font-weight: normal;">(Person With Disability/Person with Special Needs)</span><br>(Yes/No)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $index => $app)
                <tr>
                    <td style="padding-left: 20px;">
                        <span style="font-weight: bold; margin-right: 10px;">{{ $index + 1 }}.</span>
                        {{ $app['full_name'] }}
                    </td>
                    <td style="text-align: center;">{{ $app['pwd'] }}</td>
                </tr>
            @empty
                @for($i = 0; $i < 15; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            @endforelse
        </tbody>
    </table>

    <div style="page-break-before: always;"></div>

    <!-- Page 2 Content -->
    <div class="remarks-box" style="border-color: #0D2B70;">
        <p style="color: #0D2B70; font-size: 14px; margin-bottom: 10px;">REMARKS:</p>
        <p style="color: red; font-size: 13px; line-height: 1.6;">
            Applicant who complied with the documentary requirements, have met the minimum qualification standards (QS) of the position, and submitted on the deadline, shall proceed to the next assessment phase
        </p>
    </div>

    <div class="signatories-container clearfix" style="margin-top: 50px;">
        <div class="signatory">
            <p class="signatory-label">Prepared by:</p>
            <div style="margin-left: 0;">
                <p class="signatory-name">{{ $admin_name }}</p>
                <p class="signatory-designation">{{ $admin_designation }}</p>
                <p class="date-line">Date: <span class="underline">&nbsp;</span></p>
            </div>
        </div>
        <div class="signatory" style="float: right;">
            <p class="signatory-label">Noted by:</p>
            <div style="margin-left: 0;">
                <p class="signatory-name">{{ $rd_name }}</p>
                <p class="signatory-designation">{{ $rd_designation }}</p>
                <p class="date-line">Date: <span class="underline">&nbsp;</span></p>
            </div>
        </div>
    </div>

    @section('page_num', '2')
@endsection
