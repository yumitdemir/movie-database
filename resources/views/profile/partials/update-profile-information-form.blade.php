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

        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-secondary">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="btn btn-link p-0 text-decoration-underline">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm text-success">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Demographic Information Section -->
        <div class="border-top pt-4 mt-4">
            <h3 class="mb-3">
                {{ __('Demographic Information') }}
            </h3>
            <p class="mb-3 text-muted small">
                {{ __("This information is used for statistics purposes and is optional.") }}
            </p>

            <div class="mb-3">
                <label for="gender" class="form-label">{{ __('Gender') }}</label>
                <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                    <option value="">{{ __('Prefer not to say') }}</option>
                    <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                    <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                    <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                </select>
                @error('gender')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="birth_date" class="form-label">{{ __('Birth Date') }}</label>
                <input id="birth_date" name="birth_date" type="date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
                <small class="form-text text-muted">{{ __('You can provide your birth date or select an age group below') }}</small>
                @error('birth_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="age_group" class="form-label">{{ __('Age Group (Alternative to Birth Date)') }}</label>
                <select id="age_group" name="age_group" class="form-select @error('age_group') is-invalid @enderror">
                    <option value="">{{ __('Prefer not to say') }}</option>
                    <option value="under_18" {{ old('age_group', $user->age_group) == 'under_18' ? 'selected' : '' }}>{{ __('Under 18') }}</option>
                    <option value="18_24" {{ old('age_group', $user->age_group) == '18_24' ? 'selected' : '' }}>{{ __('18-24') }}</option>
                    <option value="25_34" {{ old('age_group', $user->age_group) == '25_34' ? 'selected' : '' }}>{{ __('25-34') }}</option>
                    <option value="35_44" {{ old('age_group', $user->age_group) == '35_44' ? 'selected' : '' }}>{{ __('35-44') }}</option>
                    <option value="45_54" {{ old('age_group', $user->age_group) == '45_54' ? 'selected' : '' }}>{{ __('45-54') }}</option>
                    <option value="55_plus" {{ old('age_group', $user->age_group) == '55_plus' ? 'selected' : '' }}>{{ __('55+') }}</option>
                </select>
                <small class="form-text text-muted">{{ __('If you prefer not to provide your exact birth date, you can select an age group instead') }}</small>
                @error('age_group')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="continent" class="form-label">{{ __('Continent') }}</label>
                <select id="continent" name="continent" class="form-select @error('continent') is-invalid @enderror">
                    <option value="">{{ __('Select a continent') }}</option>
                    <option value="Africa" {{ old('continent', $user->continent) == 'Africa' ? 'selected' : '' }}>{{ __('Africa') }}</option>
                    <option value="Asia" {{ old('continent', $user->continent) == 'Asia' ? 'selected' : '' }}>{{ __('Asia') }}</option>
                    <option value="Europe" {{ old('continent', $user->continent) == 'Europe' ? 'selected' : '' }}>{{ __('Europe') }}</option>
                    <option value="North America" {{ old('continent', $user->continent) == 'North America' ? 'selected' : '' }}>{{ __('North America') }}</option>
                    <option value="South America" {{ old('continent', $user->continent) == 'South America' ? 'selected' : '' }}>{{ __('South America') }}</option>
                    <option value="Australia/Oceania" {{ old('continent', $user->continent) == 'Australia/Oceania' ? 'selected' : '' }}>{{ __('Australia/Oceania') }}</option>
                    <option value="Antarctica" {{ old('continent', $user->continent) == 'Antarctica' ? 'selected' : '' }}>{{ __('Antarctica') }}</option>
                </select>
                @error('continent')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="country" class="form-label">{{ __('Country') }}</label>
                <select id="country" name="country" class="form-select @error('country') is-invalid @enderror">
                    <option value="">{{ __('Select a country') }}</option>
                    @php
                        $countries = [
                            'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia', 'Austria', 
                            'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan', 'Bolivia', 
                            'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cabo Verde', 'Cambodia', 
                            'Cameroon', 'Canada', 'Central African Republic', 'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo', 
                            'Costa Rica', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 
                            'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Eswatini', 'Ethiopia', 'Fiji', 'Finland', 
                            'France', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau', 
                            'Guyana', 'Haiti', 'Honduras', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 
                            'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Korea, North', 'Korea, South', 'Kosovo', 'Kuwait', 
                            'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 
                            'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius', 'Mexico', 
                            'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 
                            'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'North Macedonia', 'Norway', 'Oman', 'Pakistan', 
                            'Palau', 'Palestine', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland', 'Portugal', 'Qatar', 
                            'Romania', 'Russia', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa', 
                            'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone', 'Singapore', 
                            'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Sudan', 'Spain', 'Sri Lanka', 'Sudan', 
                            'Suriname', 'Sweden', 'Switzerland', 'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Timor-Leste', 'Togo', 
                            'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 
                            'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam', 'Yemen', 
                            'Zambia', 'Zimbabwe'
                        ];
                    @endphp
                    @foreach($countries as $countryName)
                        <option value="{{ $countryName }}" {{ old('country', $user->country) == $countryName ? 'selected' : '' }}>
                            {{ $countryName }}
                        </option>
                    @endforeach
                </select>
                @error('country')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <div class="text-success">
                    {{ __('Saved.') }}
                </div>
            @endif
        </div>
    </form>
</section>
