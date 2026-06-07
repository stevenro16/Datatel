<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    private function activeServices()
    {
        return ServiceType::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function index(): View
    {
        return view('public.home', ['services' => $this->activeServices()]);
    }

    public function services(): View
    {
        return view('public.services', ['services' => $this->activeServices()]);
    }

    public function contact(): View
    {
        return view('public.contact', ['services' => $this->activeServices()]);
    }

    public function contactSubmit(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:120',
            'email'      => 'required|email|max:200',
            'phone'      => 'nullable|string|max:30',
            'company'    => 'nullable|string|max:150',
            'services'   => 'nullable|array',
            'services.*' => 'integer|exists:service_types,id',
            'message'    => 'required|string|max:3000',
        ]);

        Inquiry::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'phone'    => $request->input('phone'),
            'company'  => $request->input('company'),
            'services' => $request->input('services') ?? [],
            'message'  => $request->input('message'),
            'status'   => Inquiry::STATUS_NEW,
        ]);

        return redirect()->route('contact')->with('contact_sent', true);
    }

    public function quote(): View
    {
        return view('public.quote', ['services' => $this->activeServices()]);
    }

    public function quoteSubmit(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:120',
            'email'       => 'required|email|max:200',
            'phone'       => 'nullable|string|max:30',
            'site_address'=> 'required|string|max:300',
            'description' => 'required|string|max:5000',
            'urgency'     => 'required|in:routine,urgent,emergency',
            'services'    => 'nullable|array',
            'services.*'  => 'integer|exists:service_types,id',
        ]);

        // TODO: persist as work order draft and notify admin when that module is built
        return redirect()->route('quote')->with('quote_sent', true);
    }
}
