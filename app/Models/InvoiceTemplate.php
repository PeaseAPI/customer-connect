<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTemplate extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'name', 'slug',
        'header_html', 'body_html', 'footer_html', 'css',
        'settings', 'is_default', 'is_active', 'created_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取默认模板
     */
    public static function getDefault(int $companyId): ?self
    {
        return static::where('company_id', $companyId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * 渲染发票 HTML
     */
    public function renderInvoice(Invoice $invoice): string
    {
        $settings = array_merge([
            'show_logo' => true,
            'show_company_info' => true,
            'show_payment_details' => true,
            'color_primary' => '#4f46e5',
        ], $this->settings ?? []);

        $css = $this->css ?? $this->getDefaultCss();

        return view('invoices.template', [
            'invoice' => $invoice,
            'template' => $this,
            'settings' => $settings,
            'css' => $css,
        ])->render();
    }

    protected function getDefaultCss(): string
    {
        return 'body { font-family: "Helvetica", sans-serif; color: #333; }
        .invoice-header { border-bottom: 3px solid #4f46e5; padding-bottom: 20px; }
        .invoice-table { width: 100%; border-collapse: collapse; }
        .invoice-table th { background: #f3f4f6; padding: 8px; text-align: left; }
        .invoice-table td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; font-size: 1.1em; }';
    }
}
