@props(['icon', 'label', 'href', 'active' => false])

<a href="{{ $href }}"
   class="flex items-center gap-3 px-3 py-3 rounded-md text-base font-semibold use-loader
   {{ $active ? 'bg-[#002C76] text-white' : 'text-[#002C76] hover:bg-[#002C76]/10' }}">
    <i data-feather="{{ $icon }}" class="w-5 h-5 stroke-[2.5]"></i>
    <span>{{ $label }}</span>
</a>
