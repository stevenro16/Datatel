<x-guest-layout>

    <h2 style="font-size:1.25rem;font-weight:700;color:var(--primary);margin-bottom:1.25rem;text-align:center;">Create Account</h2>

    @if ($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:.7rem 1rem;border-radius:5px;margin-bottom:1rem;font-size:.88rem;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   required autofocus autocomplete="name" placeholder="Jane Smith">
        </div>

        <div class="form-group">
            <label for="title">Title / Position</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}"
                   autocomplete="organization-title" placeholder="e.g. IT Manager, Office Administrator">
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   required autocomplete="username" placeholder="you@example.com">
        </div>

        <div class="form-group">
            <label for="company_id">Company</label>
            <select id="company_id" name="company_id"
                    style="width:100%;padding:.55rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;background:#fff;">
                <option value="">-- Select your company (optional) --</option>
                @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                    {{ $company->name }}
                </option>
                @endforeach
                <option value="other" {{ old('company_id') === 'other' ? 'selected' : '' }}>
                    Other (not listed)
                </option>
            </select>
        </div>

        <div class="form-group" id="company-other-group"
             style="display:{{ old('company_id') === 'other' ? 'block' : 'none' }};">
            <label for="company_name_other">Company Name</label>
            <input type="text" id="company_name_other" name="company_name_other"
                   value="{{ old('company_name_other') }}"
                   placeholder="Enter your company name"
                   style="width:100%;padding:.55rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            <div style="font-size:.78rem;color:#888;margin-top:.3rem;">
                An administrator will create your company record when approving your account.
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   required autocomplete="new-password" placeholder="Minimum 8 characters">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   required autocomplete="new-password" placeholder="Re-enter password">
        </div>

        <button type="submit" class="btn-primary" style="margin-top:.75rem;">Create Account</button>

        <div class="form-footer" style="margin-top:1rem;">
            <a href="{{ route('login') }}">Already have an account? Log in</a>
        </div>
    </form>

<script>
(function () {
    const sel   = document.getElementById('company_id');
    const group = document.getElementById('company-other-group');
    const input = document.getElementById('company_name_other');

    function toggle() {
        const show = sel.value === 'other';
        group.style.display = show ? 'block' : 'none';
        input.required = show;
    }

    sel.addEventListener('change', toggle);
    toggle();
})();
</script>

</x-guest-layout>
