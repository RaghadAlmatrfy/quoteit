{{-- New: footer with the developer's links --}}
<footer class="mt-16 lg:pr-28 lg:pl-56 xl:pl-0 border-t border-white/20 bg-[#10081F]/90 text-tuwaiq-text backdrop-blur-md">
    <div class="mx-auto flex max-w-7xl flex-col items-center gap-4 px-6 py-8 text-center sm:flex-row sm:justify-between sm:text-start">
        <div>
            <p class="text-lg font-bold">{{ __('Tuwaiq Quotes') }}</p>
            <p class="text-sm text-tuwaiq-text/70">{{ __('Developed by :name', ['name' => __('Raghad Almatrfy')]) }}</p>
        </div>

        <ul class="flex flex-wrap items-center justify-center gap-3 text-sm">
            <li>
                <a href="https://linkedin.com/in/raghad-almatrfy-3ab180363" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 transition hover:border-tuwaiq-orange hover:text-tuwaiq-orange">
                    <x-icon name="briefcase" class="h-4 w-4" /> LinkedIn
                </a>
            </li>
            <li>
                <a href="https://x.com/raghad_almatrfy" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 transition hover:border-tuwaiq-orange hover:text-tuwaiq-orange">
                    <x-icon name="x-letter" class="h-4 w-4" /> X
                </a>
            </li>
            <li>
                <a href="https://raghadalmatrfy.github.io" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 transition hover:border-tuwaiq-orange hover:text-tuwaiq-orange">
                    <x-icon name="globe" class="h-4 w-4" /> raghadalmatrfy.github.io
                </a>
            </li>
        </ul>
    </div>
    <p class="border-t border-white/10 py-3 text-center text-xs text-tuwaiq-text/60">
        © {{ date('Y') }} {{ __('Tuwaiq Quotes') }} · {{ __('Tuwaiq Club bootcamp project') }}
    </p>
</footer>
