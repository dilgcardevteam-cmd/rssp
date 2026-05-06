@php
@endphp
<style>
  [x-cloak] { display: none !important; }
</style>
<tr class="text-sm text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
  <td class="w-[10%] px-3 py-2 text-center font-semibold">
    <div class="flex items-center justify-center gap-1.5">
      <div class="h-2.5 w-2.5 rounded-full {{ $vacancy->status === 'OPEN' ? 'bg-green-500' : 'bg-red-500' }}"></div>
      {{ $vacancy->vacancy_id }}
    </div>
  </td>
  <td class="w-[25%] px-3 py-2 text-center">
    <p>{{ $vacancy->position_title }}</p>
    @php
      $vacancyTypeRaw = trim((string) ($vacancy->vacancy_type ?? ''));
      $vacancyTypeLabel = strcasecmp($vacancyTypeRaw, 'cos') === 0
        ? 'Contract of Service'
        : ($vacancyTypeRaw !== '' ? $vacancyTypeRaw : '');
    @endphp
    <p class="text-xs italic text-[#0D2B70]/70">
      {{ $vacancyTypeLabel }}@if(filled($vacancy->plantilla_item_no)), <span class="font-bold text-[#0D2B70]">{{ $vacancy->plantilla_item_no }}</span>@endif
    </p>
  </td>
  <td class="w-[15%] px-3 py-2 text-center">&#8369;{{ number_format($vacancy->monthly_salary, 2) }}</td>
  <td class="w-[15%] px-3 py-2 text-center">{{ \Carbon\Carbon::parse($vacancy->closing_date)->format('F j, Y') }}</td>
  <td class="w-[25%] px-3 py-2 text-center">{{ $vacancy->place_of_assignment }}</td>
  <td class="w-[10%] px-3 py-2 text-center">
    <div class="flex items-center justify-center gap-2">
      <button
        onclick="event.stopPropagation(); window.location.href='{{ route('vacancies.edit', $vacancy->vacancy_id) }}'"
        class="use-loader rounded-md border border-[#0D2B70] px-2.5 py-1 text-xs font-bold text-[#0D2B70] transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md"
        aria-label="Edit Vacancy"
        title="Edit Vacancy"
      >
        Edit
      </button>

      <div x-data="{ open: false, dropUp: false }" 
           x-on:reports-dropdown-opened.window="if ($event.detail.id !== '{{ $vacancy->vacancy_id }}') open = false"
           x-init="$watch('open', value => { 
               if(value) { 
                   let container = $el.closest('.overflow-auto');
                   if (container) {
                       let rect = $el.getBoundingClientRect();
                       let containerRect = container.getBoundingClientRect();
                       dropUp = (rect.bottom > containerRect.bottom - 150);
                   } else {
                       dropUp = ($el.getBoundingClientRect().bottom > window.innerHeight - 150);
                   }
                   $dispatch('reports-dropdown-opened', { id: '{{ $vacancy->vacancy_id }}' }); 
               } 
           })"
           class="relative inline-block">
        <button
          @click.stop="open = !open"
          class="rounded-md border border-[#0D2B70] bg-[#0D2B70] px-2.5 py-1 text-xs font-bold text-white transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70]/90 hover:shadow-md"
        >
          Reports
        </button>
        <div
          x-show="open"
          @click.away="open = false"
          x-cloak
          x-transition
          :class="dropUp ? 'bottom-full mb-1 origin-bottom-right' : 'mt-1 origin-top-right'"
          class="absolute right-0 z-50 w-48 rounded-md border border-slate-200 bg-white py-1 shadow-lg"
        >
          <a href="{{ route('preview.final_selection', $vacancy->vacancy_id) }}" target="_blank" class="block px-4 py-2 text-left text-[11px] font-semibold text-[#0D2B70] hover:bg-slate-100">
            Final Selection Line-up
          </a>
          <a href="{{ route('preview.list_of_applicants', $vacancy->vacancy_id) }}" target="_blank" class="block px-4 py-2 text-left text-[11px] font-semibold text-[#0D2B70] hover:bg-slate-100 border-t border-slate-100">
            List of Applicants
          </a>
        </div>
      </div>
    </div>
  </td>
</tr>
