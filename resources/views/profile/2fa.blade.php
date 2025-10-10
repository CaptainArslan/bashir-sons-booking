<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Two-Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Flash Messages --}}
                    @if (session('success'))
                        <div
                            class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-900 dark:text-green-300">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div
                            class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-900 dark:text-red-300">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- If 2FA is enabled --}}
                    @isset($enabled)
                        <div class="space-y-6">
                            <p class="text-gray-700 dark:text-gray-300">
                                {{ __('Two-Factor Authentication is currently enabled on your account.') }}
                            </p>

                            <form method="POST" action="{{ route('2fa.disable') }}">
                                @csrf
                                <x-primary-button>
                                    {{ __('Disable Two-Factor Authentication') }}
                                </x-primary-button>
                            </form>
                        </div>
                    @else
                        {{-- Enable 2FA --}}
                        <div class="space-y-6">
                            <p class="text-gray-700 dark:text-gray-300">
                                {{ __('Scan this QR code with your Google Authenticator app and then enter the generated code to enable 2FA.') }}
                            </p>

                            {{-- QR Code --}}
                            <div class="flex justify-center py-4">
                                <div class="bg-white p-4 rounded-lg shadow">
                                    {!! $QR_Image !!}
                                </div>
                            </div>

                            {{-- Secret Key --}}
                            <div class="text-center">
                                <p class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                                    {{ __('Secret Key:') }}
                                </p>
                                <p class="font-mono text-gray-900 dark:text-gray-100">
                                    {{ $secret }}
                                </p>
                            </div>

                            {{-- Verification Form --}}
                            <form method="POST" action="{{ route('2fa.enable') }}" class="mt-6 space-y-4 max-w-md mx-auto">
                                @csrf
                                <input type="hidden" name="secret" value="{{ $secret }}">

                                <div>
                                    <x-input-label for="code" :value="__('Enter Authenticator Code')" />
                                    <x-text-input id="code" class="block mt-1 w-full" type="text" name="code"
                                        required autofocus placeholder="123456" />
                                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                </div>

                                <div class="flex justify-end pt-4">
                                    <x-primary-button>
                                        {{ __('Enable Two-Factor Authentication') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
