<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>发票 #{{ $invoice->invoice_number ?? '' }}</title>
    <style>
        {{ $css }}
    </style>
</head>
<body>
    @if($template->header_html)
        {!! $template->header_html !!}
    @else
        <div class="invoice-header">
            <table style="width: 100%;">
                <tr>
                    <td>
                        @if($settings['show_logo'] ?? true)
                            <img src="{{ $invoice->company->logo_url ?? '' }}" alt="Logo" style="max-height: 60px;">
                        @endif
                    </td>
                    <td class="text-right">
                        <h2>发票</h2>
                        <p>#{{ $invoice->invoice_number ?? '' }}</p>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    @if($template->body_html)
        {!! $template->body_html !!}
    @else
        <div class="invoice-body">
            <table style="width: 100%; margin: 20px 0;">
                <tr>
                    <td style="width: 50%;">
                        @if($settings['show_company_info'] ?? true)
                            <strong>开票方:</strong><br>
                            {{ $invoice->company->company_name ?? '' }}<br>
                            {{ $invoice->company->company_email ?? '' }}
                        @endif
                    </td>
                    <td style="width: 50%;" class="text-right">
                        <strong>客户:</strong><br>
                        {{ $invoice->client->name ?? '' }}<br>
                        {{ $invoice->client->email ?? '' }}
                    </td>
                </tr>
            </table>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>项目</th>
                        <th class="text-right">数量</th>
                        <th class="text-right">单价</th>
                        <th class="text-right">金额</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items ?? [] as $item)
                        <tr>
                            <td>{{ $item['item_name'] ?? '' }}</td>
                            <td class="text-right">{{ $item['quantity'] ?? 1 }}</td>
                            <td class="text-right">{{ $item['unit_price'] ?? 0 }}</td>
                            <td class="text-right">{{ $item['amount'] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table style="width: 100%; margin-top: 20px;">
                <tr>
                    <td class="text-right">小计:</td>
                    <td style="width: 150px;" class="text-right">{{ $invoice->sub_total ?? 0 }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                    <tr>
                        <td class="text-right">税额:</td>
                        <td class="text-right">{{ $invoice->tax_amount }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td class="text-right">总计:</td>
                    <td class="text-right">{{ $invoice->total ?? 0 }}</td>
                </tr>
            </table>
        </div>
    @endif

    @if($template->footer_html)
        {!! $template->footer_html !!}
    @else
        <div class="invoice-footer" style="margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            @if($settings['show_payment_details'] ?? true)
                <p><strong>付款信息:</strong></p>
                <p>银行: {{ $invoice->company->bank_name ?? '' }}</p>
                <p>账号: {{ $invoice->company->bank_account ?? '' }}</p>
            @endif
        </div>
    @endif
</body>
</html>
