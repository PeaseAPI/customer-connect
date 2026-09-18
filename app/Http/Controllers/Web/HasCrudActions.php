<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

trait HasCrudActions
{
    protected function getApiToken(Request $request): string
    {
        return $request->user()->createToken('web-session')->plainTextToken;
    }

    protected function apiHeaders(Request $request): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getApiToken($request),
            'X-Company-Id' => $request->user()->company_id ?? 1,
            'Accept' => 'application/json',
        ];
    }

    protected function apiGet(Request $request, string $url)
    {
        return Http::withHeaders($this->apiHeaders($request))->get(url($url));
    }

    protected function apiPost(Request $request, string $url, array $data = [])
    {
        return Http::withHeaders($this->apiHeaders($request))->post(url($url), $data);
    }

    protected function apiPut(Request $request, string $url, array $data = [])
    {
        return Http::withHeaders($this->apiHeaders($request))->put(url($url), $data);
    }

    protected function apiPatch(Request $request, string $url, array $data = [])
    {
        return Http::withHeaders($this->apiHeaders($request))->patch(url($url), $data);
    }

    protected function apiDelete(Request $request, string $url)
    {
        return Http::withHeaders($this->apiHeaders($request))->delete(url($url));
    }
}