<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DILG Report')</title>
    <style>
        @page {
            margin: 0.5in;
            size: A4;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
            position: relative;
            min-height: 95vh;
        }
        
        /* Corner Markings */
        .corner {
            position: absolute;
            width: 20px;
            height: 20px;
            border-color: #ccc;
            border-style: solid;
            border-width: 0;
        }
        .top-left { top: 0; left: 0; border-top-width: 1px; border-left-width: 1px; }
        .top-right { top: 0; right: 0; border-top-width: 1px; border-right-width: 1px; }
        .bottom-left { bottom: 0; left: 0; border-bottom-width: 1px; border-left-width: 1px; }
        .bottom-right { bottom: 0; right: 0; border-bottom-width: 1px; border-right-width: 1px; }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        .header img {
            width: 70px;
            height: 70px;
            margin-right: 15px;
        }
        .header-text {
            display: inline-block;
            vertical-align: middle;
        }
        .header-text p {
            margin: 0;
            font-size: 12px;
            color: #666;
            font-weight: bold;
        }
        .header-text h1 {
            margin: 0;
            font-size: 22px;
            color: #555;
            letter-spacing: 1px;
            font-weight: 900;
        }

        .meta-info {
            margin-bottom: 20px;
            font-style: italic;
            font-weight: bold;
        }
        .meta-info table {
            width: 100%;
        }
        .meta-info td {
            padding: 2px 0;
        }
        .meta-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 150px;
            margin-left: 10px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #0D2B70;
        }
        table.data-table th {
            background-color: #A9D18E;
            color: #000;
            border: 1px solid #0D2B70;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        table.data-table td {
            border: 1px solid #0D2B70;
            padding: 6px;
            min-height: 25px;
        }

        .remarks-box {
            margin-top: 30px;
            border: 2px solid #0D2B70;
            padding: 15px;
            color: #0D2B70;
            font-weight: bold;
            font-size: 12px;
        }
        .remarks-box p {
            margin: 5px 0;
        }

        .signatories-container {
            margin-top: 40px;
            width: 100%;
        }
        .signatory {
            width: 45%;
            display: inline-block;
        }
        .signatory-label {
            margin-bottom: 40px;
        }
        .signatory-name {
            font-weight: 900;
            text-decoration: none;
            text-transform: uppercase;
            font-size: 13px;
        }
        .signatory-designation {
            font-style: italic;
            font-size: 11px;
            margin-top: 2px;
        }
        .date-line {
            margin-top: 15px;
            font-size: 12px;
        }
        .underline {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 250px;
            margin-left: 5px;
        }

        .footer-page {
            position: absolute;
            bottom: 10px;
            right: 20px;
            font-size: 10px;
            color: #666;
        }
        
        /* Clearfix for floats */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="corner top-left"></div>
    <div class="corner top-right"></div>
    <div class="corner bottom-left"></div>
    <div class="corner bottom-right"></div>

    <div class="header">
        @php
            $logoPath = public_path('images/dilg_logo.png');
            $logoData = "";
            if(file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
            }
        @endphp
        <img src="data:image/png;base64,{{ $logoData }}" alt="DILG Logo">
        <div class="header-text">
            <p>DILG – CORDILLERA ADMINISTRATIVE REGION</p>
            <h1>@yield('report_title')</h1>
        </div>
    </div>

    @yield('content')

    <div class="footer-page">
        Page @yield('page_num', '1') of 2
    </div>
</body>
</html>
