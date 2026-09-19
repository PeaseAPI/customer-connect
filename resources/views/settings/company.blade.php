@extends('layouts.app')
@section('title', 'Company Settings')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-24">
            <h5 class="mb-0">Company Settings</h5>
            <a href="{{ route('settings.index') }}" class="text-primary-600 text-sm fw-semibold">&larr; Back to Settings</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success bg-success-100 text-success-600 border-0 radius-8 px-24 py-11 mb-24">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="row gy-3">
            @method('PUT')
            @csrf

            <div class="col-12">
                <label class="form-label fw-semibold text-sm">Company Name</label>
                <input type="text" name="company_name" value="{{ old('company_name', $company['company_name'] ?? '') }}"
                       class="form-control radius-8 bg-neutral-50 @error('company_name') is-invalid @enderror"
                       placeholder="Customer Connect Technologies Ltd.">
                @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-sm">Company Email</label>
                <input type="email" name="company_email" value="{{ old('company_email', $company['company_email'] ?? '') }}"
                       class="form-control radius-8 bg-neutral-50 @error('company_email') is-invalid @enderror"
                       placeholder="admin@example.com">
                @error('company_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-sm">Company Phone</label>
                <input type="tel" name="company_phone" value="{{ old('company_phone', $company['company_phone'] ?? '') }}"
                       class="form-control radius-8 bg-neutral-50 @error('company_phone') is-invalid @enderror"
                       placeholder="+1 (555) 123-4567">
                @error('company_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 border-top border-stroke pt-24 mt-2">
                <h6 class="fw-semibold mb-16">Branding</h6>
                <div class="row gy-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-sm">Logo</label>
                        <div class="d-flex align-items-center gap-3 mb-8">
                            <img src="{{ \App\Support\Branding::logoUrl() }}" alt="Current logo" height="44"
                                 class="border border-stroke radius-8 p-1 bg-white">
                            <span class="text-sm text-secondary-light">Current logo, shown on the sign-in page and sidebar</span>
                        </div>
                        <input type="file" name="logo" accept="image/*"
                               class="form-control radius-8 bg-neutral-50 @error('logo') is-invalid @enderror">
                        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-sm">Logo Header Background Color</label>
                        <div class="d-flex align-items-center gap-3 mb-8">
                            <input type="color" name="logo_background_color" value="{{ old('logo_background_color', $company['logo_background_color'] ?? '#FFFFFF') }}"
                                   class="form-control form-control-color radius-8" title="Pick a color">
                            <span class="text-sm text-secondary-light">Background of the sign-in page header bar</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-sm">Login Background Image</label>
                        @if(\App\Support\Branding::loginBackgroundUrl())
                            <div class="mb-8">
                                <img src="{{ \App\Support\Branding::loginBackgroundUrl() }}" alt="Login background" height="44"
                                     class="border border-stroke radius-8">
                            </div>
                        @endif
                        <input type="file" name="login_background" accept="image/*"
                               class="form-control radius-8 bg-neutral-50 @error('login_background') is-invalid @enderror">
                        @error('login_background') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex justify-content-end border-top border-stroke pt-24">
                <button type="submit" class="btn btn-primary radius-8 px-24 py-11 text-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
