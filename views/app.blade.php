<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'DzikShop') }}</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ asset('/favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('/apple-touch-icon.png') }}">

        @routes
        @viteReactRefresh
{{--    {{--}}
{{--        Vite::useHotFile(storage_path('zergly.hot'))--}}
{{--            ->useBuildDirectory('zergly')--}}
{{--            ->useManifestFilename('zergly.json')--}}
{{--            ->withEntryPoints(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])--}}
{{--            ->createAssetPathsUsing(function (string $path, ?bool $secure) { // Customize the backend path generation for built assets...--}}
{{--                return Vite::asset($path, '/zergly');--}}
{{--            })--}}
{{--    }}--}}
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
