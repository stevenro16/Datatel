<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Pending – DataTel</title>
    @include('layouts.portal-styles')
</head>
<body style="background:#f8f9fa;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;">

<div style="width:100%;max-width:480px;padding:1rem;">
    <div style="text-align:center;margin-bottom:2rem;">
        <img src="{{ route('site.logo') }}" alt="DataTel" style="height:52px;">
    </div>

    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.08);padding:2.5rem;text-align:center;">
        <div style="width:64px;height:64px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto 1.25rem;">
            ⏳
        </div>
        <h1 style="font-size:1.3rem;color:#1A3C5E;margin:0 0 .75rem;">Account Pending Approval</h1>
        <p style="color:#666;font-size:.95rem;line-height:1.6;margin:0 0 1.5rem;">
            Thank you for registering! Your account is currently under review.
            You'll be able to access the customer portal once an administrator approves your account.
        </p>
        <p style="color:#888;font-size:.85rem;margin:0 0 2rem;">
            If you have questions, please contact us directly.
        </p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    style="padding:.55rem 1.5rem;border-radius:6px;border:1px solid #d1d5db;background:#f8f9fa;color:#555;font-size:.9rem;cursor:pointer;">
                Sign Out
            </button>
        </form>
    </div>
</div>

</body>
</html>
