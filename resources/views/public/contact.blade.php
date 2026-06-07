<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/favicon-512.png">
    <title>Contact Us – DataTel</title>
    <style>
        :root { --p: #1A3C5E; --a: #2E86C1; }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; color: #333; background: #E8ECF0; }

        /* ── Keyframes ─────────────────────────────────────────────── */
        @keyframes fade-up   { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes orb-float { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(24px,-18px) scale(1.04); } 66% { transform: translate(-14px,16px) scale(.97); } }
        @keyframes node-pulse { 0%,100% { opacity:.28; } 50% { opacity:.82; } }
        @keyframes line-fade  { 0%,100% { opacity:.12; } 50% { opacity:.48; } }
        @keyframes reveal-clip { from { clip-path: inset(0 100% 0 0); } to { clip-path: inset(0 0% 0 0); } }
        @keyframes slide-up { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: translateY(0); } }

        /* ── Page hero ─────────────────────────────────────────────── */
        .page-hero { background: var(--p); position: relative; overflow: hidden; padding: 3.75rem 2rem 3.5rem; text-align: center; }
        .orb { position: absolute; border-radius: 50%; filter: blur(82px); opacity: .15; pointer-events: none; }
        .orb-1 { width: 480px; height: 480px; background: var(--a);  top: -200px; left: -100px; animation: orb-float 20s ease-in-out infinite; }
        .orb-2 { width: 300px; height: 300px; background: #0c6ca5;   bottom: -120px; right: -60px; animation: orb-float 25s ease-in-out infinite reverse; }
        .orb-3 { width: 180px; height: 180px; background: #5ab8f5;   top: 25%; right: 20%; animation: orb-float 17s ease-in-out infinite 3s; }
        .hero-net { position: absolute; inset: 0; width: 100%; height: 100%; opacity: .08; pointer-events: none; }
        .page-hero-inner { position: relative; z-index: 2; max-width: 640px; margin: 0 auto; }
        .h-eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(46,134,193,.2); color: #90d0f5;
            font-size: .71rem; font-weight: 700; letter-spacing: .14em;
            text-transform: uppercase; padding: .3rem .95rem;
            border-radius: 999px; border: 1px solid rgba(144,208,245,.18);
            margin-bottom: 1.2rem; animation: fade-up .55s ease both;
        }
        .page-hero h1 { font-size: 2.6rem; font-weight: 900; color: #fff; letter-spacing: -.5px; line-height: 1.1; margin-bottom: .8rem; animation: fade-up .55s .1s ease both; }
        .page-hero h1 span { color: #90d0f5; display: inline-block; animation: reveal-clip .7s .5s ease both; }
        .page-hero-inner > p { color: rgba(255,255,255,.62); font-size: 1rem; line-height: 1.75; animation: fade-up .55s .18s ease both; }

        /* ── Page layout ────────────────────────────────────────────── */
        .page-wrap { max-width: 1080px; margin: 0 auto; padding: 3.5rem 2rem 5rem; display: grid; grid-template-columns: 320px 1fr; gap: 2.25rem; align-items: start; }

        /* ── Info column ────────────────────────────────────────────── */
        .info-card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            padding: 1.6rem 1.5rem; margin-bottom: 1.1rem;
            animation: slide-up .55s ease both;
        }
        .info-card:nth-child(2) { animation-delay: .1s; }
        .info-card:nth-child(3) { animation-delay: .18s; }
        .info-card-head {
            display: flex; align-items: center; gap: .7rem;
            margin-bottom: .85rem;
        }
        .info-icon {
            width: 36px; height: 36px;
            background: rgba(46,134,193,.1);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .info-icon svg { width: 17px; height: 17px; color: var(--a); }
        .info-card-head h3 { font-size: .96rem; font-weight: 700; color: var(--p); margin: 0; }
        .info-card p { color: #555; font-size: .875rem; line-height: 1.75; margin: 0 0 .5rem; }
        .info-card p:last-child { margin-bottom: 0; }
        .info-card a { color: var(--a); text-decoration: none; font-weight: 600; }
        .info-card a:hover { text-decoration: underline; }

        .response-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(46,134,193,.09); color: var(--p);
            font-size: .79rem; font-weight: 600;
            padding: .35rem .75rem; border-radius: 20px; margin-top: .6rem;
        }
        .response-badge svg { color: var(--a); flex-shrink: 0; }

        /* ── Form card ──────────────────────────────────────────────── */
        .form-card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            overflow: hidden;
            animation: slide-up .55s .08s ease both;
        }
        .form-card-header { background: var(--p); padding: 1.4rem 1.75rem; }
        .form-card-header h2 { color: #fff; font-size: 1.1rem; font-weight: 800; margin: 0 0 .2rem; }
        .form-card-header p { color: rgba(255,255,255,.62); font-size: .83rem; margin: 0; }
        .form-card-body { padding: 1.75rem; }

        /* Account tip */
        .account-tip {
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 9px; padding: .9rem 1.1rem;
            margin-bottom: 1.5rem;
            display: flex; align-items: flex-start; gap: .75rem;
        }
        .account-tip-icon { flex-shrink: 0; width: 18px; height: 18px; margin-top: 2px; color: var(--a); }
        .account-tip p { margin: 0; font-size: .845rem; color: #1e40af; line-height: 1.55; }
        .account-tip a { color: var(--a); font-weight: 600; text-decoration: none; }
        .account-tip a:hover { text-decoration: underline; }

        /* Form rows */
        .form-row { margin-bottom: 1.05rem; }
        .form-row label { display: block; font-size: .8rem; font-weight: 700; color: #374151; margin-bottom: .36rem; }
        .form-row .req { color: #e74c3c; margin-left: 2px; }
        .form-row .opt { font-weight: 400; color: #9ca3af; }
        .form-row input,
        .form-row textarea,
        .form-row select {
            width: 100%; padding: .63rem .85rem;
            border: 1.5px solid #e5e7eb; border-radius: 8px;
            font-size: .88rem; font-family: inherit;
            color: #111; background: #fafafa;
            outline: none;
            transition: border-color .18s, box-shadow .18s, background .18s;
        }
        .form-row input:focus,
        .form-row textarea:focus,
        .form-row select:focus {
            border-color: var(--a);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(46,134,193,.13);
        }
        .form-row textarea { resize: vertical; min-height: 130px; }
        .err-msg { color: #b91c1c; font-size: .79rem; margin-top: .25rem; display: block; }

        /* Two-col row */
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        /* Service checkboxes */
        .svc-check-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(188px, 1fr)); gap: .5rem; margin-top: .5rem; }
        .svc-checkbox {
            display: flex; align-items: center; gap: .55rem;
            padding: .55rem .75rem; border: 1.5px solid #e5e7eb;
            border-radius: 8px; cursor: pointer;
            transition: border-color .15s, background .15s;
        }
        .svc-checkbox:hover { border-color: var(--a); background: #f0f7fd; }
        .svc-checkbox input[type=checkbox] { width: 15px; height: 15px; accent-color: var(--a); flex-shrink: 0; cursor: pointer; }
        .svc-checkbox span { font-size: .82rem; color: #374151; line-height: 1.3; }
        .svc-checkbox.checked { border-color: var(--a); background: #eff6ff; }

        /* Success banner */
        .success-banner {
            background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46;
            padding: .85rem 1.1rem; border-radius: 8px;
            margin-bottom: 1.25rem; font-size: .88rem;
            display: flex; align-items: center; gap: .6rem;
        }

        /* Submit button */
        .btn-submit {
            width: 100%; padding: .8rem;
            border: none; border-radius: 9px;
            background: linear-gradient(105deg, var(--p) 0%, var(--a) 100%);
            color: #fff; font-size: .92rem; font-weight: 700;
            letter-spacing: .025em; font-family: inherit;
            cursor: pointer; margin-top: .5rem;
            position: relative; overflow: hidden;
            box-shadow: 0 4px 14px rgba(46,134,193,.3);
            transition: transform .18s, box-shadow .18s;
        }
        .btn-submit::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.15), transparent);
            background-size: 200% 100%; background-position: -200% center;
            transition: background-position .55s;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(46,134,193,.4); }
        .btn-submit:hover::after { background-position: 200% center; }
        .btn-submit:active { transform: none; }

        /* ── Footer ─────────────────────────────────────────────────── */
        footer { background: var(--p); color: rgba(255,255,255,.42); text-align: center; padding: 1.75rem 2rem; font-size: .82rem; }
        footer .foot-links { margin-top: .4rem; }
        footer a { color: rgba(255,255,255,.62); text-decoration: none; margin: 0 .5rem; }
        footer a:hover { color: #fff; }

        @media (max-width: 760px) {
            .page-wrap { grid-template-columns: 1fr; }
            .row-2 { grid-template-columns: 1fr; }
            .page-hero h1 { font-size: 2rem; }
        }
        @media (max-width: 480px) { .svc-check-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

@include('public._nav')

<section class="page-hero">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <svg class="hero-net" viewBox="0 0 1200 280" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
        <circle cx="80"  cy="40"  r="3.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 0s"/>
        <circle cx="280" cy="110" r="4.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite .7s"/>
        <circle cx="500" cy="55"  r="3"   fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 1.3s"/>
        <circle cx="740" cy="140" r="4"   fill="white" style="animation:node-pulse 3.2s ease-in-out infinite .4s"/>
        <circle cx="960" cy="65"  r="3.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 1.8s"/>
        <circle cx="1130" cy="145" r="3"  fill="white" style="animation:node-pulse 3.2s ease-in-out infinite .9s"/>
        <circle cx="400" cy="210" r="3.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 1.5s"/>
        <circle cx="850" cy="225" r="4"   fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 2.1s"/>
        <line x1="80"  y1="40"  x2="280" y2="110" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .2s"/>
        <line x1="280" y1="110" x2="500" y2="55"  stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .9s"/>
        <line x1="500" y1="55"  x2="740" y2="140" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .5s"/>
        <line x1="740" y1="140" x2="960" y2="65"  stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 1.2s"/>
        <line x1="960" y1="65"  x2="1130" y2="145" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .4s"/>
        <line x1="280" y1="110" x2="400" y2="210" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 1.6s"/>
        <line x1="740" y1="140" x2="850" y2="225" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 1s"/>
        <line x1="400" y1="210" x2="850" y2="225" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 2s"/>
    </svg>

    <div class="page-hero-inner">
        <div class="h-eyebrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.862 9.862 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            We're Here to Help
        </div>
        <h1><span>Contact</span> Us</h1>
        <p>Have a question or project in mind? Send us a message and we'll be in touch within one business day.</p>
    </div>
</section>

<div class="page-wrap">

    {{-- Left: Info column --}}
    <div class="info-col">
        <div class="info-card">
            <div class="info-card-head">
                <div class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.862 9.862 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3>Get in Touch</h3>
            </div>
            <p>Have a question about our services or want to discuss an upcoming project? Fill out the form and our team will get back to you promptly.</p>
            <div class="response-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
                Responds within 1–2 business days
            </div>
        </div>

        @if(config('datatel.company_phone') || config('datatel.company_email'))
        <div class="info-card">
            <div class="info-card-head">
                <div class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <h3>Direct Contact</h3>
            </div>
            @if(config('datatel.company_phone'))
            <p><strong>Phone:</strong> <a href="tel:{{ config('datatel.company_phone') }}">{{ config('datatel.company_phone') }}</a></p>
            @endif
            @if(config('datatel.company_email'))
            <p><strong>Email:</strong> <a href="mailto:{{ config('datatel.company_email') }}">{{ config('datatel.company_email') }}</a></p>
            @endif
        </div>
        @endif

        <div class="info-card">
            <div class="info-card-head">
                <div class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <h3>Customer Portal</h3>
            </div>
            <p>Existing customers and those ready to get started can create a <strong>free account</strong> to submit work orders, track progress in real time, and manage invoices online.</p>
            <p style="margin-top:.75rem;"><a href="{{ route('register') }}">Create a free customer account →</a></p>
        </div>
    </div>

    {{-- Right: Contact form --}}
    <div class="form-card">
        <div class="form-card-header">
            <h2>Send a Message</h2>
            <p>Fields marked <span style="color:rgba(255,120,120,.9);">*</span> are required.</p>
        </div>
        <div class="form-card-body">

            @if(session('contact_sent'))
            <div class="success-banner">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Message sent! We'll be in touch within one business day.
            </div>
            @endif

            <div class="account-tip">
                <svg class="account-tip-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>Already a customer or ready to get started? <a href="{{ route('register') }}">Create a customer account</a> to submit work orders, track job status, and manage invoices — all in one place.</p>
            </div>

            <form method="POST" action="{{ route('contact') }}">
                @csrf

                <div class="row-2">
                    <div class="form-row">
                        <label>Your Name <span class="req">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Jane Smith" autocomplete="name">
                        @error('name')<span class="err-msg">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-row">
                        <label>Email Address <span class="req">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="jane@company.com" autocomplete="email">
                        @error('email')<span class="err-msg">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="row-2">
                    <div class="form-row">
                        <label>Phone <span class="opt">(optional)</span></label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="(555) 555-5555" autocomplete="tel">
                    </div>
                    <div class="form-row">
                        <label>Company <span class="opt">(optional)</span></label>
                        <input type="text" name="company" value="{{ old('company') }}" placeholder="Your company name" autocomplete="organization">
                    </div>
                </div>

                @if($services->isNotEmpty())
                <div class="form-row" style="margin-bottom:1.25rem;">
                    <label>Services of Interest <span class="opt">(select all that apply)</span></label>
                    <div class="svc-check-grid">
                        @foreach($services as $svc)
                        <label class="svc-checkbox" id="svc-lbl-{{ $svc->id }}">
                            <input type="checkbox" name="services[]" value="{{ $svc->id }}"
                                   {{ in_array($svc->id, (array) old('services', [])) ? 'checked' : '' }}
                                   onchange="document.getElementById('svc-lbl-'+this.value).classList.toggle('checked', this.checked)">
                            <span>{{ $svc->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="form-row">
                    <label>Message <span class="req">*</span></label>
                    <textarea name="message" required placeholder="Tell us about your project or question…">{{ old('message') }}</textarea>
                    @error('message')<span class="err-msg">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>
    </div>

</div>

<footer>
    <div>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</div>
    <div class="foot-links">
        <a href="{{ route('services') }}">Services</a>
        <a href="{{ route('about') }}">About</a>
        <a href="{{ route('contact') }}">Contact</a>
    </div>
</footer>

<script>
document.querySelectorAll('.svc-checkbox input[type=checkbox]').forEach(function (cb) {
    if (cb.checked) cb.closest('.svc-checkbox').classList.add('checked');
});
</script>
</body>
</html>
