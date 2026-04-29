{{-- Partial: tabla de productos del RMA --}}
{{-- Variables esperadas: $items (array de order_items), $rmaData (array con rma_qty, reason, resolution_type) --}}
<table style="width:100%;border-collapse:collapse;margin-top:12px;font-size:14px;">
    <thead>
        <tr style="background-color:#f3f4f6;">
            <th style="text-align:left;padding:8px 12px;color:#374151;font-weight:600;border-bottom:2px solid #e5e7eb;">
                @lang('goloba-rma::app.mail-table.product')
            </th>
            <th style="text-align:center;padding:8px 12px;color:#374151;font-weight:600;border-bottom:2px solid #e5e7eb;">
                @lang('goloba-rma::app.mail-table.qty')
            </th>
            <th style="text-align:left;padding:8px 12px;color:#374151;font-weight:600;border-bottom:2px solid #e5e7eb;">
                @lang('goloba-rma::app.mail-table.reason')
            </th>
            <th style="text-align:left;padding:8px 12px;color:#374151;font-weight:600;border-bottom:2px solid #e5e7eb;">
                @lang('goloba-rma::app.mail-table.resolution')
            </th>
        </tr>
    </thead>
    <tbody>
        @php
            $resMap = [
                'return'       => trans('goloba-rma::app.mail.new-request.resolution-return'),
                'exchange'     => trans('goloba-rma::app.mail.new-request.resolution-exchange'),
                'cancel-items' => trans('goloba-rma::app.mail.new-request.resolution-cancel'),
            ];
        @endphp
        @foreach ($items as $index => $item)
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:8px 12px;color:#374151;">{{ $item->name ?? ($item['name'] ?? '—') }}</td>
                <td style="padding:8px 12px;color:#374151;text-align:center;">{{ $rmaData['rma_qty'][$index] ?? '—' }}</td>
                <td style="padding:8px 12px;color:#374151;">{{ $rmaData['reason'][$index] ?? ($rmaData['reason'][0] ?? '—') }}</td>
                <td style="padding:8px 12px;color:#374151;">
                    @php $res = $rmaData['resolution_type'][$index] ?? ($rmaData['resolution_type'][0] ?? ''); @endphp
                    {{ $resMap[$res] ?? $res }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
