<?php

namespace App\Helpers;

class PaginationHelper
{
    /**
     * Obtiene los parámetros de pagificación estándar para los controladores.
     */
    public static function getPaginationParams($request): array
    {
        return [
            'search' => $request->get('q', ''),
            'perPage' => (int) $request->get('per_page', 10),
        ];
    }

    /**
     * Aplica filtros de búsqueda y pagificación a una query.
     */
    public static function applyFilters($query, $search, $searchFields = ['codigo', 'descripcion'])
    {
        if ($search) {
            $query->where(function ($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }
        
        return $query;
    }
}
