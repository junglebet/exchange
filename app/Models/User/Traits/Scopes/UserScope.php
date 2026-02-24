<?php

namespace App\Models\User\Traits\Scopes;

trait UserScope
{
    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('id', 'like', '%'.$search.'%');
                $query->orWhere('email', 'like', '%'.$search.'%');
            });
        });
    }

    public function scopeAuthorizable($query)
    {
        return $query->where('deleted', false);
    }
}

