<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Two-Factor Authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Add an additional layer of security to your account by enabling two-factor authentication.') }}
        </p>
    </header>

    <div class="mt-6 space-y-4">
        @if($twoFactorEnabled)
            <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ __('Two-Factor Authentication is enabled') }}
                        </p>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            {{ __('Your account is protected with an additional security layer.') }}
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('2fa.disable') }}" class="inline">
                    @csrf
                    <x-danger-button type="submit" class="text-sm">
                        {{ __('Disable 2FA') }}
                    </x-danger-button>
                </form>
            </div>
        @else
            <div class="flex items-center justify-between p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            {{ __('Two-Factor Authentication is disabled') }}
                        </p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            {{ __('Enable 2FA to add an extra layer of security to your account.') }}
                        </p>
                    </div>
                </div>
                <x-primary-button type="button" onclick="window.location='{{ route('2fa.show') }}'">
                    {{ __('Enable 2FA') }}
                </x-primary-button>
            </div>
        @endif

        {{-- Additional 2FA Management Button --}}
        {{-- <div class="flex justify-center">
            <x-secondary-button type="button" onclick="window.location='{{ route('2fa.show') }}'">
                {{ __('Manage Two-Factor Authentication') }}
            </x-secondary-button>
        </div> --}}
    </div>
</section>
