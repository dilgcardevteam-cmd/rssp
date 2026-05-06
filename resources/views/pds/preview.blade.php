<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDS Preview</title>
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
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900">Personal Data Sheet Preview</h1>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        onclick="openCleanPrint()"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800"
                    >
                        Print 
                    </button>
                    <a
                        href="{{ route('export.pds', ['download' => 1, 'force_fpdi' => 1]) }}"
                        class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
                    >
                        Download
                    </a>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 overflow-hidden h-[80vh] bg-gray-50">
                <iframe
                    id="previewPdfFrame"
                    title="PDS Preview PDF"
                    src="{{ route('export.pds', ['preview' => 1, 'force_fpdi' => 1, 'v' => now()->format('Uu')]) }}"
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

        function openCleanPrint() {
            const printWindow = window.open(@json(route('export.pds', ['print' => 1, 'force_fpdi' => 1])), '_blank', 'noopener');
            if (!printWindow) {
                showToast('Please allow pop-ups so the print version can open.');
            }
        }

        document.addEventListener('keydown', function (event) {
            const key = (event.key || '').toLowerCase();
            if ((event.ctrlKey || event.metaKey) && key === 'p') {
                event.preventDefault();
                openCleanPrint();
            }
        });
    </script>
</body>
</html>
