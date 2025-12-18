{{--
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
--}}

<section>
    <x-ui.card title="Update Password" padding="p-6">
        <p class="text-sm text-gray-600 mb-6">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>

        <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <!-- Current Password Field -->
            <div>
                <label for="update_password_current_password" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Current Password') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input id="update_password_current_password"
                           name="current_password"
                           type="password"
                           autocomplete="current-password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors pr-10"
                           placeholder="Enter your current password">
                    <button type="button"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            onclick="togglePasswordVisibility('update_password_current_password', this)">
                        {{-- Eye (show password) --}}
                        <span id="eye-update_password_current_password">
                            <x-ui.icon name="eye"></x-ui.icon>
                        </span>

                        {{-- Crossed Eye (hide password) --}}
                        <span id="eye-off-update_password_current_password" class="hidden">
                            <x-ui.icon name="crossed-eye"></x-ui.icon>
                        </span>
                    </button>
                </div>
                @error('current_password', 'updatePassword')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password Field -->
            <div>
                <label for="update_password_password" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('New Password') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input id="update_password_password"
                           name="password"
                           type="password"
                           autocomplete="new-password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors pr-10"
                           placeholder="Enter your new password">
                    <button type="button"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            onclick="togglePasswordVisibility('update_password_password', this)">
                        {{-- Eye (show password) --}}
                        <span id="eye-update_password_password">
                            <x-ui.icon name="eye"></x-ui.icon>
                        </span>

                        {{-- Crossed Eye (hide password) --}}
                        <span id="eye-off-update_password_password" class="hidden">
                            <x-ui.icon name="crossed-eye"></x-ui.icon>
                        </span>
                    </button>
                </div>
                @error('password', 'updatePassword')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    <i class="lni lni-information mr-1"></i>
                    Password must be at least 8 characters long
                </p>
            </div>

            <!-- Confirm Password Field -->
            <div>
                <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Confirm Password') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input id="update_password_password_confirmation"
                           name="password_confirmation"
                           type="password"
                           autocomplete="new-password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors pr-10"
                           placeholder="Confirm your new password">
                    <button type="button"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            onclick="togglePasswordVisibility('update_password_password_confirmation', this)">
                        {{-- Eye (show password) --}}
                        <span id="eye-update_password_password_confirmation">
                            <x-ui.icon name="eye"></x-ui.icon>
                        </span>

                        {{-- Crossed Eye (hide password) --}}
                        <span id="eye-off-update_password_password_confirmation" class="hidden">
                            <x-ui.icon name="crossed-eye"></x-ui.icon>
                        </span>
                    </button>
                </div>
                @error('password_confirmation', 'updatePassword')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div>
                    @if (session('status') === 'password-updated')
                        <div x-data="{ show: true }"
                             x-show="show"
                             x-transition
                             x-init="setTimeout(() => show = false, 3000)"
                             class="flex items-center text-green-600 bg-green-50 px-4 py-2 rounded-lg">
                            <i class="lni lni-checkmark-circle mr-2"></i>
                            <span class="font-medium">{{ __('Password updated successfully!') }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center space-x-3">
                    <button type="submit"
                            class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <div class="flex items-center">
                            <i class="lni lni-lock mr-2"></i>
                            {{ __('Update Password') }}
                        </div>
                    </button>
                </div>
            </div>
        </form>
    </x-ui.card>
</section>

@push('scripts')
    <script>
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);

            const eye = document.getElementById(`eye-${inputId}`);
            const eyeOff = document.getElementById(`eye-off-${inputId}`);

            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.add('hidden');
                eyeOff.classList.remove('hidden');
            } else {
                input.type = 'password';
                eye.classList.remove('hidden');
                eyeOff.classList.add('hidden');
            }
        }
    </script>
@endpush
