<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div id="toast" class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50 hidden items-center gap-3 rounded-xl bg-gray-900 px-5 py-3 text-sm text-white shadow-lg">
        <svg class="h-5 w-5 flex-shrink-0 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
        <span id="toast-msg">Please allow pop-ups so the print version can open.</span>
    </div>
    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6">
        <div class="bg-white rounded-xl shadow p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-[#0D2B70]">{{ $title }}</h1>
                    <p class="text-sm text-gray-600">
                        This preview shows the final format before downloading as Word or PDF.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ $downloadPdfUrl }}"
                        class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 shadow-sm transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Download as PDF
                    </a>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 overflow-hidden h-[80vh] bg-gray-50">
                <iframe
                    id="previewPdfFrame"
                    title="Report Preview PDF"
                    src="{{ $previewPdfUrl }}"
                    class="w-full h-full"
                ></iframe>
            </div>
        </div>
    </main>

    <script>
        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            toast.classList.remove('hidden');
            toast.classList.add('flex');
            clearTimeout(toast._timer);
            toast._timer = setTimeout(() => {
                toast.classList.add('hidden');
                toast.classList.remove('flex');
            }, 4000);
        }
    </script>
</body>
</html>
