@forelse ($vacancies as $vacancy)
    @include('partials.admin_job_vacancy_card', ['vacancy' => $vacancy, 'index' => $loop->index])
@empty
    <div class="text-center text-gray-500 font-semibold text-3xl mt-10">
        <i data-feather="info" class="w-7 h-7 inline-block mr-2 text-gray-400 font-montserrat"></i>
        No Job Vacancy
    </div>
@endforelse
