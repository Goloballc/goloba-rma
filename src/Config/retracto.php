<?php

/**
 * Derecho de Retracto — Mapa de categorías de Goloba
 *
 * Clasifica cada category_id de Bagisto en una de tres reglas:
 *
 *   'applies'      → Retracto siempre aplica (regla general)
 *   'excluded'     → Retracto NO aplica (excepción legal)
 *   'conditional'  → Aplica solo si el sello de seguridad está intacto
 *                    (cosméticos, perfumes). El cliente debe declararlo.
 *
 * Categorías no listadas aquí se tratan como 'applies' por defecto
 * (principio pro-consumidor).
 *
 * Actualizar este archivo cuando se añadan nuevas categorías en Goloba.
 * Fuente: Ley 1480 de 2011 (Estatuto del Consumidor), Art. 47.
 */

return [

    // -------------------------------------------------------------------------
    // EXCLUIDAS — Nunca aplica retracto
    // -------------------------------------------------------------------------
    'excluded' => [
        22,  // Lencería y Ropa Interior (padre)
        54,  // Ropa Interior (hombre)
        166, // Brasieres
        167, // Panties
        168, // Lencería Sexy y Disfraces
        169, // Medias y Calcetines (mujer)
        170, // Moldeadores y Fajas
        171, // Lencería y Accesorios de Ropa Interior
        172, // Boxers
        173, // Medias y Calcetines (hombre)
        174, // Calcetines y Ropa Interior (niña)
        175, // Calcetines y Ropa Interior (niño)

        21,  // Ropa de Baño (padre)
        152, // Bikinis
        153, // Trajes de una Pieza
        156, // Vestidos de Baño
        159, // Traje de Baño y de Buceo (mujer)
        164, // Traje de Baño y de Buceo (hombre)

        84,  // Aretes
        91,  // Piercings

        96,  // Alimentos y Golosinas para Mascotas (perecederos)
    ],

    // -------------------------------------------------------------------------
    // CONDICIONADAS — Aplica solo si el sello de seguridad está intacto
    // -------------------------------------------------------------------------
    'conditional' => [
        4,   // Belleza y Salud (padre)
        5,   // Maquillaje y Pestañas
        6,   // Artículos de Belleza
        8,   // Cuidado Personal
        10,  // Cuidado de La Piel
        12,  // Perfumes y Lociones
        14,  // Depilación
        15,  // Tratamientos de Belleza
    ],

    // -------------------------------------------------------------------------
    // Todas las demás categorías se tratan como 'applies' (regla general).
    // No es necesario listarlas aquí.
    // -------------------------------------------------------------------------
];
