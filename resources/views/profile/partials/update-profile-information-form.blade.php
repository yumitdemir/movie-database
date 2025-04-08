<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Demographic Information Section -->
        <div class="border-t pt-4 mt-4">
            <h3 class="text-md font-medium text-gray-900 mb-3">
                {{ __('Demographic Information') }}
            </h3>
            <p class="mt-1 mb-3 text-sm text-gray-600">
                {{ __("This information is used for statistics purposes and is optional.") }}
            </p>

            <div class="mt-4">
                <x-input-label for="gender" :value="__('Gender')" />
                <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">{{ __('Prefer not to say') }}</option>
                    <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                    <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                    <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('gender')" />
            </div>

            <div class="mt-4">
                <x-input-label for="birth_date" :value="__('Birth Date')" />
                <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" :value="old('birth_date', $user->birth_date?->format('Y-m-d'))" />
                <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
            </div>

            <div class="mt-4">
                <x-input-label for="continent" :value="__('Continent')" />
                <select id="continent" name="continent" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">{{ __('Select a continent') }}</option>
                    <option value="Africa" {{ old('continent', $user->continent) == 'Africa' ? 'selected' : '' }}>{{ __('Africa') }}</option>
                    <option value="Asia" {{ old('continent', $user->continent) == 'Asia' ? 'selected' : '' }}>{{ __('Asia') }}</option>
                    <option value="Europe" {{ old('continent', $user->continent) == 'Europe' ? 'selected' : '' }}>{{ __('Europe') }}</option>
                    <option value="North America" {{ old('continent', $user->continent) == 'North America' ? 'selected' : '' }}>{{ __('North America') }}</option>
                    <option value="South America" {{ old('continent', $user->continent) == 'South America' ? 'selected' : '' }}>{{ __('South America') }}</option>
                    <option value="Australia/Oceania" {{ old('continent', $user->continent) == 'Australia/Oceania' ? 'selected' : '' }}>{{ __('Australia/Oceania') }}</option>
                    <option value="Antarctica" {{ old('continent', $user->continent) == 'Antarctica' ? 'selected' : '' }}>{{ __('Antarctica') }}</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('continent')" />
            </div>

            <div class="mt-4">
                <x-input-label for="country" :value="__('Country')" />
                <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" :value="old('country', $user->country)" />
                <x-input-error class="mt-2" :messages="$errors->get('country')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
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
