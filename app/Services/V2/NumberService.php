<?php

namespace App\Services\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NumberService
{
    public function accountCode(string $prefix, string $modelClass, string $column = 'code', int $width = 5): string
    {
        /** @var class-string<Model> $modelClass */
        $last = $modelClass::withTrashed()
            ->where($column, 'like', $prefix . '%')
            ->orderByDesc($column)
            ->value($column);

        $next = 1;
        if ($last) {
            $next = ((int) Str::after((string) $last, $prefix)) + 1;
        }

        return $prefix . str_pad((string) $next, $width, '0', STR_PAD_LEFT);
    }

    public function dated(string $prefix, string $modelClass, string $column): string
    {
        /** @var class-string<Model> $modelClass */
        $seed = $prefix . '-' . now()->format('Ymd') . '-';
        $last = $modelClass::withTrashed()
            ->where($column, 'like', $seed . '%')
            ->orderByDesc('id')
            ->value($column);

        $next = 1;
        if ($last) {
            $parts = explode('-', (string) $last);
            $next = ((int) end($parts)) + 1;
        }

        return $seed . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function itemCode(string $modelClass, string $column = 'code'): string
    {
        /** @var class-string<Model> $modelClass */
        $last = $modelClass::withTrashed()->orderByDesc($column)->value($column);
        $next = $last ? ((int) $last) + 1 : 1;

        return str_pad((string) $next, 8, '0', STR_PAD_LEFT);
    }
}
