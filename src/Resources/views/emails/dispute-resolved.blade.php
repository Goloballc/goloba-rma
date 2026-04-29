@component('shop::emails.layout')
    <div style="margin-bottom:34px;">

        <span style="font-size:22px;font-weight:600;color:#121A26;">
            @lang('goloba-rma::app.mail.dispute-resolved.title')
        </span>

        <p style="font-size:16px;color:#5E5E5E;line-height:24px;margin-top:16px;">
            @lang('goloba-rma::app.mail.dispute-resolved.greeting', ['name' => $data['name']]) 👋
        </p>

        <p style="font-size:16px;color:#5E5E5E;line-height:24px;">
            {{ $data['body'] }}
        </p>

        {{-- Datos de la solicitud --}}
        <div style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin:20px 0;">
            <div style="margin-bottom:8px;">
                <span style="font-weight:600;color:#374151;">@lang('goloba-rma::app.mail.new-request.rma-id-label')</span>
                <span style="color:#374151;"> #{{ $data['rma_id'] }}</span>
            </div>
            <div style="margin-bottom:8px;">
                <span style="font-weight:600;color:#374151;">@lang('goloba-rma::app.mail.new-request.order-id-label')</span>
                <span style="color:#374151;"> #{{ $data['order_id'] }}</span>
            </div>
            <div>
                <span style="font-weight:600;color:#374151;">@lang('goloba-rma::app.mail.dispute-resolved.status-label')</span>
                @php
                    $statusMap = trans('goloba-rma::app.mail.status-update.status-map');
                    $statusLabel = $statusMap[$data['rma_status']] ?? $data['rma_status'];
                @endphp
                <span style="color:#374151;"> {{ $statusLabel }}</span>
            </div>
        </div>

        {{-- Notas del admin (opcionales) --}}
        @if (!empty($data['admin_notes']))
            <p style="font-weight:600;color:#374151;margin-bottom:4px;">
                @lang('goloba-rma::app.mail.dispute-resolved.notes-label')
            </p>
            <div style="background-color:#f9fafb;border-left:4px solid #6b7280;padding:12px 16px;color:#374151;font-size:14px;line-height:1.6;">
                {{ $data['admin_notes'] }}
            </div>
        @endif

        <div style="text-align:center;margin-top:32px;">
            <a href="{{ $data['view_url'] }}"
               style="background-color:#e91e8c;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;font-size:15px;display:inline-block;">
                @lang('goloba-rma::app.mail.dispute-resolved.view-request')
            </a>
        </div>

        <p style="font-size:13px;color:#9ca3af;margin-top:24px;text-align:center;">
            @lang('goloba-rma::app.mail.dispute-resolved.footer')
        </p>

    </div>
@endcomponent
