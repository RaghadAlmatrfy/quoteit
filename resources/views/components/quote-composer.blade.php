{{-- Floating "+" button and the pop-up form to share a quote (camp design + field + media) --}}
@props(['field' => null])

@auth
    {{-- Floating "+" button --}}
    <button type="button" popovertarget="quote-popup" aria-label="{{ __('Share a quote') }}" data-sound="open" data-tour="compose"
        class="fixed bottom-8 right-32 z-40 hidden h-16 w-16 items-center lg:flex justify-center rounded-full bg-tuwaiq-purple text-white shadow-xl transition hover:scale-110 hover:bg-tuwaiq-purple-light focus:outline-none focus-visible:ring-4 focus-visible:ring-tuwaiq-purple/30">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
            stroke="currentColor" class="h-8 w-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
    </button>

    {{-- Pop-up form --}}
    <div id="quote-popup" popover
        class="m-auto max-h-[92vh] w-[92%] max-w-xl overflow-y-auto rounded-3xl bg-white p-0 shadow-2xl backdrop:bg-black/40 backdrop:backdrop-blur-sm">

        <form method="POST" action="{{ route('quotes.store') }}" enctype="multipart/form-data" class="space-y-4 p-6"
            data-composer>
            @csrf
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-tuwaiq-purple">{{ __('Share a quote') }}</h2>
                <button type="button" popovertarget="quote-popup" popovertargetaction="hide" aria-label="{{ __('Close') }}"
                    class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="h-6 w-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div>
                <textarea id="quote-body" name="content" rows="3" required minlength="3" maxlength="155" autofocus dir="auto"
                    data-counter="quote-counter" data-style-source placeholder="{{ __('Share a tech fact or insight…') }}"
                    class="input peer resize-none user-invalid:border-red-500">{{ old('content') }}</textarea>
                <div class="mt-1 flex justify-between text-xs">
                    <p class="text-gray-500 peer-user-invalid:text-red-600">{{ __('3–155 characters.') }}</p>
                    <p id="quote-counter" class="tabular-nums text-gray-500"></p>
                </div>
            </div>

            <x-quote-style-controls />

            {{-- Live preview: how the post will look --}}
            <div>
                <p class="mb-1 text-xs font-semibold text-tuwaiq-purple">{{ __('Preview') }}</p>
                <div class="min-h-16 rounded-2xl bg-tuwaiq-purple p-4 text-white shadow" data-style-preview-box>
                    <p dir="auto" class="text-lg" data-style-preview data-placeholder="{{ __('Your words appear here…') }}">{{ __('Your words appear here…') }}</p>
                </div>
            </div>

            <x-field-picker name="field" :selected="old('field', $field?->value ?? 'ai')" />

            {{-- Image / video --}}
            <div>
                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-dashed border-tuwaiq-purple/30 p-3 text-sm text-tuwaiq-purple hover:bg-tuwaiq-purple/5">
                    <x-icon name="photo" />
                    <span>{{ __('Add an image or a video (optional)') }}</span>
                    <input type="file" name="media" accept="image/*,video/*" class="sr-only" data-media-input="composer-preview">
                </label>
                <div id="composer-preview" class="mt-2 hidden overflow-hidden rounded-xl"></div>
                <p class="mt-1 text-xs text-gray-500">{{ __('Images or videos up to 20 MB.') }}</p>
            </div>

            {{-- Schedule --}}
            <details class="rounded-xl border border-tuwaiq-purple/20 p-3 text-sm text-tuwaiq-purple" data-schedule>
                <summary class="cursor-pointer font-semibold">{{ __('Schedule for later') }}</summary>
                <label class="mt-2 block">
                    <span class="mb-1 block text-xs">{{ __('Publish on') }}</span>
                    <input type="datetime-local" class="w-full rounded-lg border border-tuwaiq-purple/30 px-3 py-2" data-schedule-local>
                </label>
                <span class="mt-1 block text-xs text-gray-500">{{ __('Pick a future date and time. Only you see it until then (in Drafts).') }}</span>
                <input type="hidden" name="publish_at" data-schedule-utc>
                <button type="submit" name="intent" value="schedule" class="btn-secondary mt-3 w-full" data-sound="save">
                    {{ __('Schedule') }}</button>
            </details>

            <div class="grid grid-cols-3 gap-2">
                <button type="submit" name="intent" value="draft"
                    class="col-span-1 rounded-2xl border border-tuwaiq-purple/30 px-3 py-3 font-medium text-tuwaiq-purple hover:bg-tuwaiq-purple/5"
                    data-sound="save">{{ __('Save draft') }}</button>
                <button type="submit" name="intent" value="publish" class="btn btn-primary col-span-2" data-sound="send">
                    {{ __('Publish') }}</button>
            </div>
        </form>
    </div>
@else
    <a href="{{ route('login') }}"
        class="fixed bottom-24 end-4 z-40 rounded-full bg-tuwaiq-purple px-6 py-4 lg:bottom-8 lg:right-32 lg:end-auto text-white shadow-xl hover:bg-tuwaiq-purple-light">
        {{ __('Log in to share a quote') }}
    </a>
@endauth
