<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number ?? '' }}</title>
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
                        <h2>Invoice</h2>
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
                            <strong>Biller:</strong><br>
                            {{ $invoice->company->company_name ?? '' }}<br>
                            {{ $invoice->company->company_email ?? '' }}
                        @endif
                    </td>
                    <td style="width: 50%;" class="text-right">
                        <strong>Client:</strong><br>
                        {{ $invoice->client->name ?? '' }}<br>
                        {{ $invoice->client->email ?? '' }}
                    </td>
                </tr>
            </table>

            <table class="invoice-table">
                <thead>
                    <tr>
                                                <th>Item</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Amount</th>
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
                    <td class="text-right">Subtotal:</td>
                    <td style="width: 150px;" class="text-right">{{ $invoice->sub_total ?? 0 }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                    <tr>
                        <td class="text-right">Tax:</td>
                        <td class="text-right">{{ $invoice->tax_amount }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td class="text-right">Total:</td>
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
                <p><strong>Payment Info:</strong></p>
                <p>Bank: {{ $invoice->company->bank_name ?? '' }}</p>
                <p>Account: {{ $invoice->company->bank_account ?? '' }}</p>
            @endif
        </div>
    @endif
</body>
</html>
