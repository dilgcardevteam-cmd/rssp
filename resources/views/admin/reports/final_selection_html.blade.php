@extends('admin.reports.report_layout')

@section('title', 'Final Selection Line-up')
@section('report_title', 'FINAL SELECTION LINE-UP')

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
                <th colspan="2">POSITION/LOCATION</th>
            </tr>
            <tr>
                <th style="width: 50px; background-color: #fff;">No.</th>
                <th style="background-color: #fff;">Name of Applicants</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $index => $app)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $app['full_name'] }}</td>
                </tr>
            @empty
                @for($i = 0; $i < 10; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            @endforelse
            <tr>
                <td colspan="2" style="font-weight: bold; color: #0D2B70;">REMARKS:</td>
            </tr>
        </tbody>
    </table>

    <div style="page-break-before: always;"></div>

    <!-- Page 2 Content -->
    <div class="remarks-box" style="padding: 20px;">
        <p style="font-size: 13px; margin-bottom: 15px;">The above-named applicants shall undergo the Competency-Based Assessment (CBA) on the following dates:</p>
        
        <ul style="list-style-type: disc; margin-left: 60px; font-size: 13px; line-height: 2;">
            <li><span style="font-weight: bold;">{{ now()->format('F j, Y (l)') }}</span> for {{ strtoupper($vacancy->position_title) }}.</li>
        </ul>

        <p style="color: red; font-size: 13px; margin-top: 20px; font-weight: bold;">
            * CBA Result for {{ strtoupper($vacancy->position_title) }} shall be applied pursuant to RHRMSPB Resolution 04 s. 2023 re: Validity Period of the Competency-Based Assessment (CBA) Results.
        </p>

        <p style="margin-top: 25px; font-size: 13px;">Advisory on the guidance of the assessment shall be emailed once finalized.</p>
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
