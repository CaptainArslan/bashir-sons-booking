<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two-Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                {{-- Success & Error messages --}}
                @if (session('success'))
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 font-medium text-sm text-red-600">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Check if 2FA is enabled --}}
                @if (isset($enabled) && $enabled)
                    <div class="text-center">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800">
                            Two-Factor Authentication is Enabled
                        </h3>

                        <p class="text-gray-600 mb-6">
                            Your account is protected with 2FA. You can disable it anytime.
                        </p>

                        <form action="{{ route('2fa.disable') }}" method="POST">
                            @csrf
                            <x-primary-button class="bg-red-600 hover:bg-red-700">
                                {{ __('Disable Two-Factor Authentication') }}
                            </x-primary-button>
                        </form>
                    </div>
                @else
                    {{-- 2FA Setup Section --}}
                    <div>
                        <h3 class="text-lg font-semibold mb-4 text-gray-800">
                            Enable Two-Factor Authentication
                        </h3>

                        <p class="text-gray-600 mb-4">
                            Scan this QR code with your <strong>Google Authenticator</strong> or
                            <strong>Authy</strong> app.
                        </p>

                        {{-- QR Code --}}
                        <div class="flex justify-center my-6">
                            @if (isset($QR_Image))
                                {!! $QR_Image !!}
                            @endif
                        </div>

                        {{-- Manual secret key --}}
                        <p class="text-gray-700 mb-4">
                            If you can’t scan the QR code, enter this secret key manually in your app:
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded text-gray-800">
                                {{ $secret ?? 'N/A' }}
                            </span>
                        </p>

                        {{-- Verification form --}}
                        <form method="POST" action="{{ route('2fa.enable') }}" class="mt-6">
                            @csrf
                            <input type="hidden" name="secret" value="{{ $secret ?? '' }}">

                            <div class="mb-4">
                                <x-input-label for="code" :value="__('Enter 6-digit code from your Authenticator app')" />
                                <x-text-input id="code" type="text" name="code" class="block mt-1 w-full"
                                    placeholder="123456" required autofocus />
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>

                            <x-primary-button>
                                {{ __('Enable Two-Factor Authentication') }}
                            </x-primary-button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
