<?php

namespace App\Http\Controllers\Web;

use App\Models\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    use HasCrudActions;

    public function index()
    {
        return view('settings.index');
    }

    public function company(Request $request)
    {
        $response = $this->apiGet($request, '/api/companies/1');
        return view('settings.company', [
            'company' => $response->json('data', []),
        ]);
    }

    public function updateCompany(Request $request)
    {
        $payload = $request->only([
            'company_name',
            'company_email',
            'company_phone',
            'logo_background_color',
        ]);

        // Files are stored locally on the public disk; only the stored path
        // travels to the API, mirroring the original Worksuite behaviour of
        // managing branding assets from the admin panel.
        foreach (['logo', 'login_background'] as $field) {
            if ($request->hasFile($field)) {
                $request->validate([
                    $field => 'image|mimes:png,jpg,jpeg,svg,webp|max:4096',
                ]);

                $path = $request->file($field)->store('company-branding', 'public');

                $previous = Company::find(1)?->{$field};
                if ($previous && Storage::disk('public')->exists($previous)) {
                    Storage::disk('public')->delete($previous);
                }

                $payload[$field] = $path;
            }
        }

        $response = $this->apiPut($request, '/api/companies/1', $payload);
        if ($response->successful()) {
            return redirect()->route('settings.company')->with('success', 'Company settings updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function notifications()
    {
        return view('settings.notifications');
    }
}

