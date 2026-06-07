@extends('layouts.admin')

@section('title', 'Inquiry #' . $inquiry->id)

@section('content')
<div style="padding:2rem;">

    <div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem;align-items:start;">

        {{-- Left: Inquiry details --}}
        <div>
            <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;margin-bottom:1.25rem;">
                <div style="background:#1A3C5E;padding:.85rem 1.25rem;">
                    <h3 style="color:#fff;font-size:.9rem;font-weight:700;margin:0;">Contact Information</h3>
                </div>
                <div style="padding:1.25rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div>
                        <div style="font-size:.75rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Name</div>
                        <div style="font-weight:600;color:#111827;">{{ $inquiry->name }}</div>
                    </div>
                    <div>
                        <div style="font-size:.75rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Email</div>
                        <div><a href="mailto:{{ $inquiry->email }}" style="color:#2E86C1;text-decoration:none;">{{ $inquiry->email }}</a></div>
                    </div>
                    <div>
                        <div style="font-size:.75rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Phone</div>
                        <div style="color:#374151;">
                            @if($inquiry->phone)
                                <a href="tel:{{ $inquiry->phone }}" style="color:#374151;text-decoration:none;">{{ $inquiry->phone }}</a>
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div style="font-size:.75rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Company</div>
                        <div style="color:#374151;">{{ $inquiry->company ?: '—' }}</div>
                    </div>
                </div>
            </div>

            {{-- Services --}}
            @if(!empty($serviceNames))
            <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;margin-bottom:1.25rem;">
                <div style="background:#1A3C5E;padding:.85rem 1.25rem;">
                    <h3 style="color:#fff;font-size:.9rem;font-weight:700;margin:0;">Services of Interest</h3>
                </div>
                <div style="padding:1.25rem;display:flex;flex-wrap:wrap;gap:.5rem;">
                    @foreach($serviceNames as $sn)
                    <span style="background:#e0f2fe;color:#0369a1;font-size:.83rem;font-weight:600;padding:.3rem .85rem;border-radius:999px;">{{ $sn }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Message --}}
            <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;">
                <div style="background:#1A3C5E;padding:.85rem 1.25rem;">
                    <h3 style="color:#fff;font-size:.9rem;font-weight:700;margin:0;">Message</h3>
                </div>
                <div style="padding:1.25rem;">
                    <p style="color:#374151;line-height:1.75;white-space:pre-wrap;margin:0;">{{ $inquiry->message }}</p>
                </div>
            </div>
        </div>

        {{-- Right: Notes --}}
        <div>
            <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;">
                <div style="background:#1A3C5E;padding:.85rem 1.25rem;">
                    <h3 style="color:#fff;font-size:.9rem;font-weight:700;margin:0;">Notes</h3>
                </div>
                <div style="padding:1.25rem;">

                    {{-- Add note form --}}
                    <form method="POST" action="{{ route('admin.inquiries.notes.store', $inquiry) }}" style="margin-bottom:1.25rem;">
                        @csrf
                        <textarea name="note" rows="3" required
                                  placeholder="Add an internal note…"
                                  style="width:100%;padding:.6rem .85rem;border:1.5px solid #d1d5db;border-radius:7px;font-size:.875rem;font-family:inherit;resize:vertical;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;"
                                  onfocus="this.style.borderColor='#2E86C1';this.style.boxShadow='0 0 0 3px rgba(46,134,193,.12)'"
                                  onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">{{ old('note') }}</textarea>
                        @error('note')<div style="color:#b91c1c;font-size:.8rem;margin-top:.25rem;">{{ $message }}</div>@enderror
                        <button type="submit"
                                style="margin-top:.5rem;width:100%;padding:.55rem;background:#2E86C1;color:#fff;border:none;border-radius:7px;font-size:.85rem;font-weight:700;cursor:pointer;transition:background .15s;"
                                onmouseover="this.style.background='#1A3C5E'" onmouseout="this.style.background='#2E86C1'">
                            Add Note
                        </button>
                    </form>

                    {{-- Notes list --}}
                    @forelse($inquiry->notes as $note)
                    <div style="border-top:1px solid #f3f4f6;padding-top:.9rem;margin-top:.9rem;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.35rem;">
                            <span style="font-weight:600;font-size:.83rem;color:#1A3C5E;">{{ $note->admin->name }}</span>
                            <span style="font-size:.75rem;color:#9ca3af;">{{ $note->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        <p style="color:#374151;font-size:.875rem;line-height:1.65;margin:0;white-space:pre-wrap;">{{ $note->note }}</p>
                    </div>
                    @empty
                    <p style="color:#9ca3af;font-size:.85rem;text-align:center;padding:.5rem 0;">No notes yet. Adding a note will move this inquiry to "In Progress".</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── Create Work Order Modal ──────────────────────────────────── --}}
<div id="wo-modal"
     onclick="if(event.target===this)closeWoModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9000;align-items:center;justify-content:center;padding:1rem;overflow-y:auto;">
    <div style="background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.25);width:100%;max-width:640px;overflow:hidden;margin:auto;">

        {{-- Modal header --}}
        <div style="background:#1A3C5E;padding:1.1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="color:#fff;font-size:1rem;font-weight:700;">Create Work Order from Inquiry</div>
                <div style="color:rgba(255,255,255,.65);font-size:.8rem;margin-top:.15rem;">Review the details below, edit as needed, then confirm.</div>
            </div>
            <button type="button" onclick="closeWoModal()"
                    style="background:rgba(255,255,255,.15);border:none;color:#fff;width:28px;height:28px;border-radius:6px;font-size:1.1rem;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center;">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.inquiries.create-work-order', $inquiry) }}">
            @csrf
            <div style="padding:1.5rem;overflow-y:auto;max-height:calc(100vh - 220px);">

                {{-- Section 1: Customer Account --}}
                <div style="margin-bottom:1.5rem;">
                    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem;">
                        <div style="width:24px;height:24px;background:#1A3C5E;border-radius:50%;color:#fff;font-size:.75rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">1</div>
                        <h3 style="font-size:.95rem;font-weight:700;color:#1A3C5E;margin:0;">Customer Account</h3>
                    </div>

                    {{-- Existing user banner (shown by JS) --}}
                    <div id="existing-user-banner" style="display:none;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:.8rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#1e40af;display:flex;align-items:flex-start;gap:.6rem;">
                        <svg style="flex-shrink:0;margin-top:1px;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="existing-user-text"></span>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Full Name <span style="color:#e74c3c;">*</span></label>
                            <input type="text" name="name" id="wo-name" required value="{{ $inquiry->name }}"
                                   style="width:100%;padding:.55rem .8rem;border:1.5px solid #d1d5db;border-radius:7px;font-size:.875rem;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;"
                                   onfocus="this.style.borderColor='#2E86C1';this.style.boxShadow='0 0 0 3px rgba(46,134,193,.12)'"
                                   onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Email Address <span style="color:#e74c3c;">*</span></label>
                            <input type="email" name="email" id="wo-email" required value="{{ $inquiry->email }}"
                                   style="width:100%;padding:.55rem .8rem;border:1.5px solid #d1d5db;border-radius:7px;font-size:.875rem;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;"
                                   onfocus="this.style.borderColor='#2E86C1';this.style.boxShadow='0 0 0 3px rgba(46,134,193,.12)'"
                                   onblur="checkExistingEmail(this.value)">
                        </div>
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Phone</label>
                            <input type="tel" name="phone" id="wo-phone" value="{{ $inquiry->phone }}"
                                   style="width:100%;padding:.55rem .8rem;border:1.5px solid #d1d5db;border-radius:7px;font-size:.875rem;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;"
                                   onfocus="this.style.borderColor='#2E86C1';this.style.boxShadow='0 0 0 3px rgba(46,134,193,.12)'"
                                   onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Company</label>
                            <input type="text" name="company" value="{{ $inquiry->company }}"
                                   style="width:100%;padding:.55rem .8rem;border:1.5px solid #d1d5db;border-radius:7px;font-size:.875rem;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;"
                                   onfocus="this.style.borderColor='#2E86C1';this.style.boxShadow='0 0 0 3px rgba(46,134,193,.12)'"
                                   onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                    </div>
                    <p id="new-user-note" style="margin:.6rem 0 0;font-size:.78rem;color:#6b7280;">
                        If no account exists for this email, a new customer account will be created automatically.
                    </p>
                </div>

                <hr style="border:none;border-top:1px solid #e5e7eb;margin-bottom:1.5rem;">

                {{-- Section 2: Work Order Details --}}
                <div>
                    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem;">
                        <div style="width:24px;height:24px;background:#1A3C5E;border-radius:50%;color:#fff;font-size:.75rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">2</div>
                        <h3 style="font-size:.95rem;font-weight:700;color:#1A3C5E;margin:0;">Work Order Details</h3>
                    </div>

                    <div style="margin-bottom:.85rem;">
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Description <span style="color:#e74c3c;">*</span></label>
                        <textarea name="description" required rows="4"
                                  style="width:100%;padding:.55rem .8rem;border:1.5px solid #d1d5db;border-radius:7px;font-size:.875rem;font-family:inherit;resize:vertical;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;"
                                  onfocus="this.style.borderColor='#2E86C1';this.style.boxShadow='0 0 0 3px rgba(46,134,193,.12)'"
                                  onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">{{ $inquiry->message }}</textarea>
                        <p style="margin:.4rem 0 0;font-size:.77rem;color:#9ca3af;">Pre-filled from the inquiry message. Edit as needed.</p>
                    </div>

                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Urgency <span style="color:#e74c3c;">*</span></label>
                        <select name="urgency"
                                style="width:100%;padding:.55rem .8rem;border:1.5px solid #d1d5db;border-radius:7px;font-size:.875rem;background:#fff;box-sizing:border-box;">
                            <option value="routine" selected>Routine</option>
                            <option value="urgent">Urgent</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>

                    @if(!empty($serviceNames))
                    <div style="margin-top:.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;">
                        <p style="margin:0;font-size:.82rem;color:#166534;">
                            <strong>Services will be pre-attached:</strong>
                            {{ implode(', ', $serviceNames) }}
                        </p>
                    </div>
                    @endif
                </div>

            </div>

            {{-- Modal footer --}}
            <div style="padding:1rem 1.5rem;border-top:1px solid #e5e7eb;display:flex;gap:.75rem;justify-content:flex-end;background:#f9fafb;">
                <button type="button" onclick="closeWoModal()"
                        style="padding:.6rem 1.25rem;border:1.5px solid #d1d5db;background:#fff;border-radius:7px;font-size:.875rem;font-weight:600;color:#374151;cursor:pointer;transition:background .15s;"
                        onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">Cancel</button>
                <button type="submit"
                        style="padding:.6rem 1.5rem;background:#1A3C5E;color:#fff;border:none;border-radius:7px;font-size:.875rem;font-weight:700;cursor:pointer;transition:background .15s;display:flex;align-items:center;gap:.5rem;"
                        onmouseover="this.style.background='#2E86C1'" onmouseout="this.style.background='#1A3C5E'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Confirm &amp; Create Work Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openWoModal() {
    document.getElementById('wo-modal').style.display = 'flex';
    document.addEventListener('keydown', _woKey);
    checkExistingEmail(document.getElementById('wo-email').value);
}
function closeWoModal() {
    document.getElementById('wo-modal').style.display = 'none';
    document.removeEventListener('keydown', _woKey);
}
function _woKey(e) { if (e.key === 'Escape') closeWoModal(); }

function checkExistingEmail(email) {
    if (!email || !email.includes('@')) return;
    fetch('{{ route('admin.inquiries.check-email') }}?email=' + encodeURIComponent(email), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const banner   = document.getElementById('existing-user-banner');
        const noteText = document.getElementById('new-user-note');
        if (data.exists) {
            document.getElementById('existing-user-text').textContent =
                'An account already exists for ' + data.user.email + ' (' + data.user.name + '). ' +
                'The work order will be linked to this existing account — no new account will be created.';
            banner.style.display   = 'flex';
            noteText.style.display = 'none';
        } else {
            banner.style.display   = 'none';
            noteText.style.display = '';
        }
    })
    .catch(() => {});
}
</script>
@endsection

@push('topbar-title')
<div style="display:flex;align-items:center;gap:.75rem;">
    <a href="{{ route('admin.inquiries.index') }}" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;white-space:nowrap;">← Inquiries</a>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        {{ $inquiry->name }}
        <span class="badge {{ $inquiry->statusClass() }}" style="font-size:.7rem;">{{ $inquiry->statusLabel() }}</span>
    </h1>
</div>
@endpush

@push('topbar-actions')
<form method="POST" action="{{ route('admin.inquiries.status', $inquiry) }}" style="display:inline;margin:0;">
    @csrf
    @php $statuses = ['new' => 'New', 'in_progress' => 'In Progress', 'closed' => 'Closed']; @endphp
    <select name="status" onchange="this.form.submit()"
            style="padding:.4rem .75rem;border:1.5px solid #d1d5db;border-radius:7px;font-size:.83rem;font-weight:600;color:#374151;background:#fff;cursor:pointer;">
        @foreach($statuses as $val => $lbl)
        <option value="{{ $val }}" {{ $inquiry->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
    </select>
</form>
@if($inquiry->status !== \App\Models\Inquiry::STATUS_CLOSED)
<button onclick="openWoModal()"
        style="padding:.4rem 1rem;background:var(--primary);color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:background .15s;"
        onmouseover="this.style.background='var(--accent)'" onmouseout="this.style.background='var(--primary)'">
    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Create Work Order
</button>
@endif
@endpush
