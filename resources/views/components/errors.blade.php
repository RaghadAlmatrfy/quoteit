@if ($errors->any())
    <div class="mb-4 rounded bg-red-100 p-3 text-sm text-red-700">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
