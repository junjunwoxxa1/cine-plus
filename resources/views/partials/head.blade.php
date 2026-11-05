<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<script>
    (function () {
        function registerLivewire419Handler() {
            if (window.Livewire && window.Livewire.hook) {
                window.Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419) {
                            if (typeof preventDefault === 'function') preventDefault();
                            window.location.reload();
                        }
                    });
                });
            }
        }
        if (window.Livewire) {
            registerLivewire419Handler();
        } else {
            document.addEventListener('livewire:init', registerLivewire419Handler);
        }
    })();
</script>
