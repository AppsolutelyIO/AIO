<x-guest-layout>
    <div class="container container-normal py-4">
        <div class="text-center mb-4">
            <x-authentication-card-logo />
        </div>
        <div class="card card-lg">
            <div class="card-body">
                {!! $policy !!}
            </div>
        </div>
    </div>
</x-guest-layout>
