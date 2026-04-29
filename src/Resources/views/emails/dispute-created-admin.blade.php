@component('shop::emails.layout')
    <div style="margin-bottom:34px;">

        <span style="font-size:22px;font-weight:600;color:#121A26;">
            @lang('goloba-rma::app.mail.dispute-created.title')
        </span>

        <p style="font-size:16px;color:#5E5E5E;line-height:24px;margin-top:16px;">
            @lang('goloba-rma::app.mail.dispute-created.greeting')
        </p>

        <p style="font-size:16px;color:#5E5E5E;line-height:24px;">
            @lang('goloba-rma::app.mail.dispute-created.body', [
                'rma_id'   => $data['rma_id'],
                'order_id' => $data['order_id'],
            ])
        </p>

        {{-- Datos de la disputa --}}
        <div style="background-color:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:16px;margin:20px 0;">
            <div style="margin-bottom:8px;">
                <span style="font-weight:600;color:#374151;">@lang('goloba-rma::app.mail.new-request.rma-id-label')</span>
                <span style="color:#374151;"> #{{ $data['rma_id'] }}</span>
            </div>
            <div style="margin-bottom:8px;">
                <span style="font-weight:600;color:#374151;">@lang('goloba-rma::app.mail.new-request.order-id-label')</span>
                <span style="color:#374151;"> #{{ $data['order_id'] }}</span>
            </div>
            <div style="margin-bottom:8px;">
                <span style="font-weight:600;color:#374151;">@lang('goloba-rma::app.mail.dispute-created.seller-label')</span>
                <span style="color:#374151;"> {{ $data['seller_name'] }}</span>
            </div>
        </div>

        {{-- Observaciones del seller --}}
        @if (!empty($data['observations']))
            <p style="font-weight:600;color:#374151;margin-bottom:4px;">
                @lang('goloba-rma::app.mail.dispute-created.observations-label')
            </p>
            <div style="background-color:#f9fafb;border-left:4px solid #e91e8c;padding:12px 16px;color:#374151;font-size:14px;line-height:1.6;">
                {{ $data['observations'] }}
            </div>
        @endif

        <div style="text-align:center;margin-top:32px;">
            <a href="{{ url(config('app.admin_url') . '/rma/disputes/' . $data['rma_id']) }}"
               style="background-color:#e91e8c;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;font-size:15px;display:inline-block;">
                @lang('goloba-rma::app.mail.dispute-created.view-dispute')
            </a>
        </div>

    </div>
@endcomponent
