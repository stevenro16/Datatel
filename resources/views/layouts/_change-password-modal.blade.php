{{-- Change Password Modal — included in admin, portal, and employee layouts --}}
<div id="chpw-modal"
     onclick="if(event.target===this)closeChpwModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:10000;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:12px;box-shadow:0 16px 48px rgba(0,0,0,.22);width:100%;max-width:400px;overflow:hidden;">

        {{-- Header --}}
        <div style="background:var(--primary);padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;">
            <span style="color:#fff;font-size:1rem;font-weight:700;">Change Password</span>
            <button type="button" onclick="closeChpwModal()"
                    style="background:rgba(255,255,255,.15);border:none;color:#fff;width:28px;height:28px;border-radius:6px;font-size:1.1rem;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center;">&times;</button>
        </div>

        {{-- Body --}}
        <form method="POST" action="{{ route('password.update') }}" id="chpw-form" style="padding:1.5rem;">
            @csrf
            @method('PUT')

            <div id="chpw-errors" style="display:none;background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:.65rem .9rem;border-radius:7px;margin-bottom:1rem;font-size:.85rem;line-height:1.5;"></div>
            <div id="chpw-success" style="display:none;background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:.65rem .9rem;border-radius:7px;margin-bottom:1rem;font-size:.85rem;"></div>

            @foreach([['current_password','Current Password'],['password','New Password'],['password_confirmation','Confirm New Password']] as [$field,$label])
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.83rem;font-weight:600;color:#374151;margin-bottom:.3rem;">{{ $label }}</label>
                <div style="position:relative;">
                    <input type="password" name="{{ $field }}" id="chpw-{{ $field }}"
                           required
                           style="width:100%;padding:.6rem 2.4rem .6rem .85rem;border:1.5px solid #d1d5db;border-radius:7px;font-size:.9rem;outline:none;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;"
                           onfocus="this.style.borderColor='#2E86C1';this.style.boxShadow='0 0 0 3px rgba(46,134,193,.15)'"
                           onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                    <button type="button" tabindex="-1"
                            onclick="chpwToggle('chpw-{{ $field }}', this)"
                            style="position:absolute;right:.65rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;color:#9ca3af;line-height:0;transition:color .15s;"
                            onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#9ca3af'">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>
            @endforeach

            <div style="display:flex;gap:.75rem;margin-top:1.5rem;">
                <button type="button" onclick="closeChpwModal()"
                        style="flex:1;padding:.65rem;border:1.5px solid #d1d5db;background:#fff;border-radius:7px;font-size:.88rem;font-weight:600;color:#374151;cursor:pointer;transition:background .15s;"
                        onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">Cancel</button>
                <button type="submit"
                        style="flex:1;padding:.65rem;background:var(--accent);color:#fff;border:none;border-radius:7px;font-size:.88rem;font-weight:700;cursor:pointer;transition:background .15s;"
                        onmouseover="this.style.background='var(--primary)'" onmouseout="this.style.background='var(--accent)'">Save Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function openChpwModal() {
    document.getElementById('chpw-form').reset();
    document.getElementById('chpw-errors').style.display  = 'none';
    document.getElementById('chpw-success').style.display = 'none';
    document.getElementById('chpw-modal').style.display   = 'flex';
    document.getElementById('chpw-current_password').focus();
    document.addEventListener('keydown', _chpwKey);
    // Close any open nav dropdown
    const dd = document.getElementById('portalDropdown');
    if (dd) dd.classList.remove('open');
}
function closeChpwModal() {
    document.getElementById('chpw-modal').style.display = 'none';
    document.removeEventListener('keydown', _chpwKey);
}
function _chpwKey(e) { if (e.key === 'Escape') closeChpwModal(); }

function chpwToggle(inputId, btn) {
    var input  = document.getElementById(inputId);
    var show   = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.querySelector('.eye-open').style.display   = show ? 'none' : '';
    btn.querySelector('.eye-closed').style.display = show ? ''     : 'none';
}

document.getElementById('chpw-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form    = this;
    var errBox  = document.getElementById('chpw-errors');
    var okBox   = document.getElementById('chpw-success');
    var saveBtn = form.querySelector('button[type="submit"]');
    errBox.style.display = 'none';
    okBox.style.display  = 'none';
    saveBtn.disabled     = true;
    saveBtn.textContent  = 'Saving…';

    // Use redirect:'manual' so we see the 302 directly instead of following it to HTML
    fetch(form.action, {
        method:   'POST',
        redirect: 'manual',
        headers:  { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body:     new FormData(form),
    })
    .then(function(res) {
        // opaqueredirect (type='opaqueredirect', status=0) means Laravel issued a redirect → success
        if (res.type === 'opaqueredirect' || res.status === 0) {
            okBox.textContent   = 'Password updated successfully.';
            okBox.style.display = 'block';
            form.reset();
            return;
        }
        // 422 = validation errors returned as JSON
        return res.json().then(function(data) {
            var msgs = [];
            if (data.errors) {
                Object.values(data.errors).forEach(function(arr) { msgs = msgs.concat(arr); });
            } else if (data.message) {
                msgs = [data.message];
            } else {
                msgs = ['Something went wrong. Please try again.'];
            }
            errBox.innerHTML    = msgs.map(function(m) { return '<div>' + m + '</div>'; }).join('');
            errBox.style.display = 'block';
        });
    })
    .catch(function() {
        errBox.textContent   = 'An error occurred. Please try again.';
        errBox.style.display = 'block';
    })
    .finally(function() {
        saveBtn.disabled    = false;
        saveBtn.textContent = 'Save Password';
    });
});
</script>
