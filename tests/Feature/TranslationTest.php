<?php

use Illuminate\Support\Facades\Http;

test('a quote is translated inside the site', function () {
    Http::fake([
        'translate.googleapis.com/*' => Http::response([[['Clean data beats a complex model.', 'x']]]),
    ]);

    $this->getJson('/translate?to=en&text='.urlencode('البيانات النظيفة تتفوق على النموذج المعقد'))
        ->assertOk()
        ->assertJson(['translation' => 'Clean data beats a complex model.', 'to' => 'en']);
});

test('the second translation service is used if the first one fails', function () {
    Http::fake([
        'translate.googleapis.com/*' => Http::response('down', 500),
        'api.mymemory.translated.net/*' => Http::response(['responseData' => ['translatedText' => 'مرحبا']]),
    ]);

    $this->getJson('/translate?to=ar&text=Hello')->assertOk()->assertJson(['translation' => 'مرحبا']);
});

test('a clear message is shown when translation is not available', function () {
    Http::fake(['*' => Http::response('down', 500)]);

    $this->getJson('/translate?to=ar&text=Hello')->assertStatus(503)->assertJsonStructure(['message']);
});

test('only Arabic and English are allowed', function () {
    $this->getJson('/translate?to=fr&text=Hello')->assertUnprocessable();
});

test('translation still works when PHP has no certificate list (Windows)', function () {
    Http::fake(['translate.googleapis.com/*' => Http::response([[['Hello', 'x']]])]);

    $this->getJson('/translate?to=en&text='.urlencode('مرحبا'))->assertOk()->assertJson(['translation' => 'Hello']);

    expect(resource_path('certs/cacert.pem'))->toBeFile();
});
