@auth
    @php
        $dash = match(auth()->user()->role) {
            'admin'    => route('admin.dashboard'),
            'employee' => route('employee.calendar'),
            default    => route('portal.work-orders.index'),
        };
        $label = match(auth()->user()->role) {
            'admin'    => 'Admin Dashboard',
            'employee' => 'My Schedule',
            default    => 'My Portal',
        };
    @endphp
    <a href="{{ $dash }}" class="pub-btn-portal">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        {{ $label }}
    </a>
@else
    {{-- Login trigger button --}}
    <button type="button" onclick="openLoginModal()" class="pub-btn-portal">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
        Sign In
    </button>

    {{-- ── Login Modal ── --}}
    <div id="login-modal"
         onclick="if(event.target===this)closeLoginModal()"
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
        <div style="background:#fff;border-radius:14px;box-shadow:0 24px 64px rgba(0,0,0,.28);width:100%;max-width:420px;overflow:hidden;">

            {{-- Header --}}
            <div style="background:#1A3C5E;padding:1.1rem 1.75rem;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:.65rem;">
                    <div style="width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">🔒</div>
                    <span style="color:#fff;font-size:1.05rem;font-weight:700;letter-spacing:.01em;">Sign In</span>
                </div>
                <button type="button" onclick="closeLoginModal()"
                        style="background:rgba(255,255,255,.15);border:none;color:#fff;width:30px;height:30px;border-radius:6px;font-size:1.15rem;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center;">×</button>
            </div>

            {{-- Body --}}
            <div style="padding:1.75rem 2rem 2rem;">
                {{-- Validation errors --}}
                @if($errors->any())
                <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:.65rem .9rem;border-radius:7px;margin-bottom:1rem;font-size:.86rem;line-height:1.5;">
                    @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" onsubmit="handleLoginSubmit(this)">
                    @csrf

                    {{-- Email --}}
                    <div style="margin-bottom:.9rem;">
                        <label for="modal-email" style="display:block;font-size:.84rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Email Address</label>
                        <input type="email" id="modal-email" name="email"
                               value="{{ old('email') }}"
                               required autofocus autocomplete="username"
                               placeholder="you@example.com"
                               style="width:100%;padding:.6rem .9rem;border:1.5px solid #d1d5db;border-radius:7px;font-size:.93rem;outline:none;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;"
                               onfocus="this.style.borderColor='#2E86C1';this.style.boxShadow='0 0 0 3px rgba(46,134,193,.15)'"
                               onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                    </div>

                    {{-- Password --}}
                    <div style="margin-bottom:.85rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.3rem;">
                            <label for="modal-password" style="font-size:.84rem;font-weight:600;color:#374151;">Password</label>
                            @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" style="font-size:.78rem;color:#2E86C1;text-decoration:none;font-weight:500;">Forgot password?</a>
                            @endif
                        </div>
                        <div style="position:relative;">
                            <input type="password" id="modal-password" name="password"
                                   required autocomplete="current-password"
                                   placeholder="••••••••"
                                   style="width:100%;padding:.6rem 2.4rem .6rem .9rem;border:1.5px solid #d1d5db;border-radius:7px;font-size:.93rem;outline:none;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;"
                                   onfocus="this.style.borderColor='#2E86C1';this.style.boxShadow='0 0 0 3px rgba(46,134,193,.15)'"
                                   onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                            <button type="button" tabindex="-1" title="Show / hide password"
                                    onclick="toggleModalPassword()"
                                    style="position:absolute;right:.65rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;color:#9ca3af;line-height:0;transition:color .15s;"
                                    onmouseover="this.style.color='#1A3C5E'" onmouseout="this.style.color='#9ca3af'">
                                <svg id="modal-eye-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg id="modal-eye-closed" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Checkboxes --}}
                    <div style="display:flex;flex-wrap:wrap;gap:.35rem 1.25rem;margin-bottom:1.35rem;">
                        <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;user-select:none;font-size:.84rem;color:#555;">
                            <input type="checkbox" id="modal-remember-email"
                                   style="width:15px;height:15px;accent-color:#2E86C1;cursor:pointer;">
                            Remember my email
                        </label>
                        <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;user-select:none;font-size:.84rem;color:#555;">
                            <input type="checkbox" id="modal-remember" name="remember"
                                   style="width:15px;height:15px;accent-color:#2E86C1;cursor:pointer;">
                            Keep me signed in
                        </label>
                    </div>

                    <button type="submit"
                            style="width:100%;padding:.72rem;background:#2E86C1;color:#fff;border:none;border-radius:8px;font-size:.95rem;font-weight:700;cursor:pointer;letter-spacing:.01em;transition:background .15s;"
                            onmouseover="this.style.background='#1A3C5E'" onmouseout="this.style.background='#2E86C1'">
                        Sign In →
                    </button>
                </form>

                <div style="text-align:center;margin-top:1.1rem;padding-top:1.1rem;border-top:1px solid #f0f0f0;font-size:.84rem;">
                    <span style="color:#888;">Don't have an account?</span>
                    <a href="{{ route('register') }}" style="color:#2E86C1;font-weight:600;text-decoration:none;margin-left:.3rem;">Create a Customer Account →</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleModalPassword() {
        var input  = document.getElementById('modal-password');
        var open   = document.getElementById('modal-eye-open');
        var closed = document.getElementById('modal-eye-closed');
        var show   = input.type === 'password';
        input.type           = show ? 'text'  : 'password';
        open.style.display   = show ? 'none'  : '';
        closed.style.display = show ? ''      : 'none';
    }

    const STORAGE_KEY = 'datatel_remember_email';

    function openLoginModal() {
        const modal = document.getElementById('login-modal');
        if (!modal) return;
        const emailEl = document.getElementById('modal-email');
        const rememberEl = document.getElementById('modal-remember-email');
        // Pre-fill saved email
        if (emailEl && !emailEl.value) {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                emailEl.value = saved;
                if (rememberEl) rememberEl.checked = true;
            }
        }
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        // Focus password if email already filled, otherwise focus email
        setTimeout(function() {
            const email = document.getElementById('modal-email');
            if (email && email.value) {
                document.getElementById('modal-password')?.focus();
            } else {
                email?.focus();
            }
        }, 60);
    }

    function closeLoginModal() {
        const modal = document.getElementById('login-modal');
        if (modal) { modal.style.display = 'none'; document.body.style.overflow = ''; }
    }

    function handleLoginSubmit() {
        const rememberEl = document.getElementById('modal-remember-email');
        const emailEl    = document.getElementById('modal-email');
        if (rememberEl && rememberEl.checked && emailEl && emailEl.value) {
            localStorage.setItem(STORAGE_KEY, emailEl.value);
        } else {
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    // Auto-open on validation errors (login failed — redirect brought user back here)
    @if($errors->any())
    document.addEventListener('DOMContentLoaded', openLoginModal);
    @endif

    // Escape key closes
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLoginModal();
    });
    </script>
@endauth
