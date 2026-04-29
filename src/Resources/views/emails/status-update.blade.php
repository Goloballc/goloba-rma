@component('shop::emails.layout')
    <div style="margin-bottom:34px;">

        <span style="font-size:22px;font-weight:600;color:#121A26;">
            @lang('goloba-rma::app.mail.status-update.title')
        </span>

        <p style="font-size:16px;color:#5E5E5E;line-height:24px;margin-top:16px;">
            @lang('goloba-rma::app.mail.status-update.greeting', ['name' => $data['name']]) 👋
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
                <span style="font-weight:600;color:#374151;">@lang('goloba-rma::app.mail.status-update.status-label')</span>
                @php
                    $statusMap = trans('goloba-rma::app.mail.status-update.status-map');
                    $statusLabel = $statusMap[$data['rma_status']] ?? $data['rma_status'];
                @endphp
                <span style="color:#374151;"> {{ $statusLabel }}</span>
            </div>
        </div>

        <div style="text-align:center;margin-top:32px;">
            <a href="{{ $data['view_url'] }}"
               style="background-color:#e91e8c;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;font-size:15px;display:inline-block;">
                @lang('goloba-rma::app.mail.status-update.view-request')
            </a>
        </div>

        <p style="font-size:13px;color:#9ca3af;margin-top:24px;text-align:center;">
            @lang('goloba-rma::app.mail.status-update.footer')
        </p>

    </div>
@endcomponent
