<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class FinanceNumber
{
    public static function next(string $prefix, string $modelClass, string $column = 'reference_no'): string
    {
        /** @var class-string<Model> $modelClass */
        $date = now()->format('Ymd');
        $seed = sprintf('%s-%s-', $prefix, $date);

        $last = $modelClass::query()
            ->where($column, 'like', $seed . '%')
            ->orderByDesc('id')
            ->value($column);

        $nextSequence = 1;
        if ($last) {
            $parts = explode('-', (string) $last);
            $lastSequence = (int) end($parts);
            $nextSequence = $lastSequence + 1;
        }

        return $seed . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
