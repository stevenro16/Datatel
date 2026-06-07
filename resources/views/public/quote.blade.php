<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/favicon-512.png">
    <title>Request a Quote – DataTel</title>
    @include('layouts.portal-styles')
    <style>
        nav { background: #fff; border-bottom: 1px solid #d0d5dd; box-shadow: 0 2px 8px rgba(0,0,0,.06); padding: 0 2rem; height: 80px; overflow: visible; display: flex; align-items: center; gap: 2rem; }
        nav img { height: 96px; }
        nav a { color: #1A3C5E; text-decoration: none; font-size: .95rem; font-weight: 500; }
        nav a:hover { color: #2E86C1; }
        nav .ml-auto { margin-left: auto; display: flex; gap: 1rem; }
        .btn { display: inline-block; padding: .6rem 1.4rem; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: .9rem; }
        .btn-accent { background: #2E86C1; color: #fff; }
        .btn-outline { border: 2px solid #1A3C5E; color: #1A3C5E; }
        .hero-sm { background: #1A3C5E; color: #fff; padding: 3rem 2rem; text-align: center; }
        .hero-sm h1 { font-size: 2rem; margin-bottom: .5rem; }
        .hero-sm p { color: rgba(255,255,255,.8); }
        .content { max-width: 720px; margin: 0 auto; padding: 3rem 2rem; }
        .card { background: #fff; border-radius: 8px; padding: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .form-row { margin-bottom: 1.1rem; }
        .form-row label { display: block; font-size: .85rem; font-weight: 600; color: #444; margin-bottom: .3rem; }
        .form-row input, .form-row textarea, .form-row select {
            width: 100%; padding: .6rem .85rem; border: 1px solid #ccc; border-radius: 5px; font-size: .95rem; font-family: inherit; }
        .form-row input:focus, .form-row textarea:focus, .form-row select:focus {
            outline: none; border-color: #2E86C1; box-shadow: 0 0 0 3px rgba(46,134,193,.12); }
        .form-row textarea { resize: vertical; min-height: 100px; }
        .form-section { font-size: .8rem; font-weight: 700; text-transform: uppercase;
                        letter-spacing: .06em; color: #2E86C1; margin: 1.75rem 0 .75rem; border-bottom: 1px solid #dde; padding-bottom: .4rem; }
        .checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px,1fr)); gap: .5rem; }
        .checkbox-item { display: flex; align-items: center; gap: .5rem; font-size: .9rem; }
        .checkbox-item input[type=checkbox] { width: 16px; height: 16px; accent-color: #2E86C1; flex-shrink: 0; }
        .btn-submit { width: 100%; padding: .85rem; background: #1A3C5E; color: #fff; border: none;
                      border-radius: 5px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: .75rem; }
        .btn-submit:hover { background: #2E86C1; }
        .success-banner { background: #d1fae5; color: #065f46; padding: 1rem 1.25rem; border-radius: 6px; margin-bottom: 1.25rem; }
        footer { background: #1A3C5E; color: rgba(255,255,255,.6); text-align: center; padding: 1.25rem; font-size: .85rem; }
    </style>
</head>
<body style="background:#E8ECF0">

<nav>
    <a href="{{ route('home') }}"><img src="{{ route('site.logo') }}" alt="DataTel"></a>
    <a href="{{ route('services') }}">Services</a>
    <a href="{{ route('about') }}">About</a>
    <a href="{{ route('contact') }}">Contact</a>
    <div class="ml-auto">
        @include('public._nav-auth')
    </div>
</nav>

<div class="hero-sm">
    <h1>Request a Quote</h1>
    <p>Tell us about your project and we'll prepare a custom estimate.</p>
</div>

<div class="content">
    <div class="card">
        @if(session('quote_sent'))
            <div class="success-banner">
                Thanks! Your quote request has been submitted. We'll be in touch within one business day.
            </div>
        @endif

        <form method="POST" action="{{ route('quote') }}">
            @csrf

            <div class="form-section">Contact Information</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-row">
                    <label>First &amp; Last Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                    @error('name')<span style="color:#C0392B;font-size:.82rem">{{ $message }}</span>@enderror
                </div>
                <div class="form-row">
                    <label>Company Name</label>
                    <input type="text" name="company" value="{{ old('company') }}">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-row">
                    <label>Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                    @error('email')<span style="color:#C0392B;font-size:.82rem">{{ $message }}</span>@enderror
                </div>
                <div class="form-row">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="(555) 555-5555">
                </div>
            </div>

            <div class="form-section">Project Location</div>
            <div class="form-row">
                <label>Site Address *</label>
                <input type="text" name="site_address" value="{{ old('site_address') }}" required placeholder="123 Main St, City, State ZIP">
            </div>
            <div class="form-row">
                <label>Building Type</label>
                <select name="building_type">
                    <option value="">— Select —</option>
                    <option value="commercial"  {{ old('building_type')=='commercial'  ? 'selected' : '' }}>Commercial</option>
                    <option value="residential" {{ old('building_type')=='residential' ? 'selected' : '' }}>Residential</option>
                    <option value="industrial"  {{ old('building_type')=='industrial'  ? 'selected' : '' }}>Industrial</option>
                    <option value="data_center" {{ old('building_type')=='data_center' ? 'selected' : '' }}>Data Center</option>
                    <option value="other"       {{ old('building_type')=='other'       ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="form-section">Services Needed</div>
            <div class="checkbox-grid">
                @foreach($services as $service)
                <label class="checkbox-item">
                    <input type="checkbox" name="services[]" value="{{ $service->id }}"
                        {{ in_array($service->id, old('services', [])) ? 'checked' : '' }}>
                    {{ $service->name }}
                </label>
                @endforeach
            </div>

            <div class="form-section">Project Details</div>
            <div class="form-row">
                <label>Describe Your Project *</label>
                <textarea name="description" required placeholder="What do you need installed, repaired, or upgraded?">{{ old('description') }}</textarea>
                @error('description')<span style="color:#C0392B;font-size:.82rem">{{ $message }}</span>@enderror
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-row">
                    <label>Urgency</label>
                    <select name="urgency">
                        <option value="routine"   {{ old('urgency','routine')=='routine'   ? 'selected' : '' }}>Routine</option>
                        <option value="urgent"    {{ old('urgency')=='urgent'    ? 'selected' : '' }}>Urgent</option>
                        <option value="emergency" {{ old('urgency')=='emergency' ? 'selected' : '' }}>Emergency</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Preferred Start Date</label>
                    <input type="date" name="preferred_date" value="{{ old('preferred_date') }}">
                </div>
            </div>
            <div class="form-row">
                <label>Estimated Number of Drops / Runs</label>
                <input type="number" name="num_drops" value="{{ old('num_drops') }}" min="1" placeholder="e.g. 24">
            </div>
            <div class="form-row">
                <label>Additional Notes</label>
                <textarea name="notes" placeholder="Anything else we should know?">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="btn-submit">Submit Quote Request</button>
        </form>
    </div>
</div>

<footer>&copy; {{ date('Y') }} DataTel. All rights reserved.</footer>
</body>
</html>
