@component('shop::emails.layout')
    <div style="margin-bottom:34px;">

        <span style="font-size:22px;font-weight:600;color:#121A26;">
            @lang('goloba-rma::app.mail.new-request.title-seller')
        </span>

        <p style="font-size:16px;color:#5E5E5E;line-height:24px;margin-top:16px;">
            @lang('goloba-rma::app.mail.new-request.greeting', ['name' => $data['seller_name']]) 👋
        </p>

        <p style="font-size:16px;color:#5E5E5E;line-height:24px;">
            @lang('goloba-rma::app.mail.new-request.body-seller')
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
            <div style="margin-bottom:8px;">
                <span style="font-weight:600;color:#374151;">@lang('goloba-rma::app.mail.new-request.type-label')</span>
                <span style="color:#374151;">
                    {{ ($data['rma_type'] ?? 'standard') === 'retracto'
                        ? trans('goloba-rma::app.mail.new-request.type-retracto')
                        : trans('goloba-rma::app.mail.new-request.type-standard') }}
                </span>
            </div>
        </div>

        @include('goloba-rma::emails.partials.product-table', ['items' => $data['order_items'], 'rmaData' => $data])

        <div style="text-align:center;margin-top:32px;">
            <a href="{{ url('/seller/rma/' . $data['rma_id']) }}"
               style="background-color:#e91e8c;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;font-size:15px;display:inline-block;">
                @lang('goloba-rma::app.mail.new-request.view-request')
            </a>
        </div>

    </div>
@endcomponent
