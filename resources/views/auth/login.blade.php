<x-guest-layout>

    <h2 style="font-size:1.25rem;font-weight:700;color:var(--primary);margin-bottom:1.25rem;text-align:center;">Sign In</h2>

    @if (session('status'))
        <div class="status-msg">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert-error" style="background:#fee2e2;color:#991b1b;padding:.7rem 1rem;border-radius:5px;margin-bottom:1rem;font-size:.88rem;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username" placeholder="you@example.com">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div style="position:relative;">
                <input type="password" id="password" name="password"
                       required autocomplete="current-password" placeholder="••••••••"
                       style="padding-right:2.5rem;width:100%;">
                <button type="button" id="pw-toggle"
                        onclick="togglePassword()"
                        tabindex="-1"
                        title="Show / hide password"
                        style="position:absolute;right:.65rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;color:#9ca3af;line-height:0;transition:color .15s;"
                        onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#9ca3af'">
                    {{-- Eye open (default) --}}
                    <svg id="pw-eye-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    {{-- Eye closed (shown when visible) --}}
                    <svg id="pw-eye-closed" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         style="display:none;">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>
        </div>

        <script>
        function togglePassword() {
            var input  = document.getElementById('password');
            var open   = document.getElementById('pw-eye-open');
            var closed = document.getElementById('pw-eye-closed');
            var show   = input.type === 'password';
            input.type        = show ? 'text' : 'password';
            open.style.display   = show ? 'none'  : '';
            closed.style.display = show ? ''      : 'none';
        }
        </script>

        <div class="remember">
            <input type="checkbox" id="remember_me" name="remember" style="width:auto;margin:0;">
            <label for="remember_me" style="margin:0;font-weight:400;font-size:.88rem;color:#555;">Remember me</label>
        </div>

        <button type="submit" class="btn-primary" style="margin-top:.75rem;">Log in</button>

        <div class="form-footer" style="margin-top:1rem;">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Forgot your password?</a>
            @endif
            <a href="{{ route('register') }}">Create an account</a>
        </div>
    </form>

</x-guest-layout>
