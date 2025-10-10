{{-- resources/views/auth/twofactor-challenge.blade.php --}}
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Please enter the 6-digit authentication code from your Authenticator app to complete login.') }}
    </div>

    @if (session('error'))
        <div class="mb-4 font-medium text-sm text-red-600">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('2fa.verify') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Authentication Code')" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verify Code') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
