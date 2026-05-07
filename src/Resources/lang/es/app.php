<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Correos transaccionales — GolobaRMA
    |--------------------------------------------------------------------------
    */

    'mail' => [

        // ── Nueva solicitud RMA ──────────────────────────────────────────────
        'new-request' => [
            'subject-customer'   => 'Solicitud de devolución/cambio #:rma_id recibida',
            'subject-seller'     => 'Nueva solicitud de devolución/cambio — Pedido #:order_id',
            'title-customer'     => '¡Solicitud recibida!',
            'title-seller'       => 'Nueva solicitud de cliente',
            'greeting'           => 'Hola :name,',
            'body-customer'      => 'Hemos recibido tu solicitud correctamente. La revisaremos y nos pondremos en contacto pronto.',
            'body-seller'        => 'Un cliente ha creado una solicitud de devolución/cambio para uno de tus pedidos. Revísala en tu panel de vendedor.',
            'type-label'         => 'Tipo de solicitud:',
            'type-retracto'      => 'Derecho de Retracto',
            'type-standard'      => 'Solicitud estándar',
            'resolution-label'   => 'Resolución solicitada:',
            'resolution-return'  => 'Devolución',
            'resolution-exchange'=> 'Reemplazo',
            'resolution-cancel'  => 'Cancelación',
            'rma-id-label'       => 'N.° de solicitud:',
            'order-id-label'     => 'N.° de pedido:',
            'products-label'     => 'Producto(s) incluidos:',
            'view-request'       => 'Ver solicitud',
            'footer'             => 'Si tienes dudas, puedes responder desde el chat de la solicitud.',
        ],

        // ── Cambio de estado ────────────────────────────────────────────────
        'status-update' => [
            'subject-customer'          => 'Tu solicitud #:rma_id ha sido actualizada',
            'subject-seller'            => 'Solicitud #:rma_id actualizada — Pedido #:order_id',
            'title'                     => '¡Estado actualizado!',
            'greeting'                  => 'Estimado/a :name,',
            'body-customer-by-seller'   => 'El estado de tu solicitud de devolución/cambio con ID #:rma_id ha sido actualizado por el vendedor.',
            'body-customer-by-admin'    => 'El estado de tu solicitud de devolución/cambio con ID #:rma_id ha sido actualizado por el equipo de Goloba.',
            'body-seller'               => 'El estado de la solicitud con ID #:rma_id ha sido actualizado.',
            'status-label'              => 'Estado actual:',
            'status-map' => [
                'Pending'            => 'Pendiente',
                'Accept'             => 'Aceptada',
                'Awaiting'           => 'En espera',
                'Dispatched Package' => 'Paquete enviado',
                'Received Package'   => 'Paquete recibido',
                'Disputed'           => 'En disputa',
                'Declined'           => 'Rechazada',
                'Paid'               => 'Reembolsada',
                'Replaced'           => 'Reemplazada',
                'Item Canceled'      => 'Artículo cancelado',
                'Canceled'           => 'Cancelada',
                'Solved'             => 'Resuelta',
            ],
            'view-request'      => 'Ver solicitud',
            'footer'            => 'Si tienes preguntas, puedes contactarnos desde el chat de la solicitud.',
        ],

        // ── Disputa creada (al admin) ────────────────────────────────────────
        'dispute-created' => [
            'subject-admin'      => 'Nueva disputa — Solicitud #:rma_id',
            'title'              => 'Nueva disputa de vendedor',
            'greeting'           => 'Hola,',
            'body'               => 'Un vendedor ha abierto una disputa para la solicitud RMA #:rma_id (Pedido #:order_id). Revisa las observaciones y toma una decisión en el panel de administración.',
            'seller-label'       => 'Vendedor:',
            'observations-label' => 'Observaciones del vendedor:',
            'view-dispute'       => 'Ver disputa',
        ],

        // ── Disputa resuelta (a seller y cliente) ────────────────────────────
        'dispute-resolved' => [
            'title'                  => 'Resolución de disputa',
            'greeting'               => 'Hola :name,',
            'notes-label'            => 'Notas del equipo Goloba:',
            'status-label'           => 'Estado final:',
            'view-request'           => 'Ver solicitud',
            'footer'                 => 'Si tienes dudas, puedes escribirnos a través del chat de soporte.',

            // Admin da la razón al cliente → RMA vuelve a Accept
            'subject-seller-rejected'  => 'Disputa resuelta — Solicitud #:rma_id',
            'subject-customer-rejected'=> 'Tu solicitud #:rma_id ha sido reactivada',
            'body-seller-rejected'     => 'El equipo de Goloba ha revisado la disputa y ha decidido proceder con la solicitud del cliente. El RMA continúa en estado "Aceptada".',
            'body-customer-rejected'   => 'Buenas noticias: tu solicitud ha sido revisada por el equipo de Goloba y ha sido reactivada. El vendedor deberá continuar con el proceso.',

            // Admin da la razón al seller → RMA pasa a Declined
            'subject-seller-approved'  => 'Disputa resuelta — Solicitud #:rma_id',
            'subject-customer-approved'=> 'Actualización de tu solicitud #:rma_id',
            'body-seller-approved'     => 'El equipo de Goloba ha revisado la disputa y ha dado resolución a tu favor. La solicitud ha quedado en estado "Rechazada".',
            'body-customer-approved'   => 'Hemos revisado la disputa en tu solicitud. Lamentablemente, la resolución no fue favorable en esta ocasión y la solicitud ha quedado rechazada.',
        ],

    ], // fin mail

    /*
    |--------------------------------------------------------------------------
    | Encabezados de tabla en correos
    |--------------------------------------------------------------------------
    */
    'mail-table' => [
        'product'    => 'Producto',
        'qty'        => 'Cantidad',
        'reason'     => 'Motivo',
        'resolution' => 'Resolución',
    ],

]; // fin return
