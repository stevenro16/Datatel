<x-guest-layout>

    <h2 style="font-size:1.25rem;font-weight:700;color:var(--primary);margin-bottom:1rem;text-align:center;">Reset Password</h2>

    <p style="font-size:.9rem;color:#555;margin-bottom:1.25rem;line-height:1.5;">
        Forgot your password? Enter your email and we'll send a reset link.
    </p>

    @if (session('status'))
        <div class="status-msg">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:.7rem 1rem;border-radius:5px;margin-bottom:1rem;font-size:.88rem;">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   required autofocus placeholder="you@example.com">
        </div>

        <button type="submit" class="btn-primary" style="margin-top:.5rem;">Send Reset Link</button>

        <div class="form-footer" style="margin-top:1rem;">
            <a href="{{ route('login') }}">Back to login</a>
        </div>
    </form>

</x-guest-layout>
