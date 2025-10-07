{{-- resources/views/components/app-layout.blade.php --}}
@props(['header' => null])

@include('layouts.app-user', [
    'header' => $header,
    'slot' => $slot,
])
