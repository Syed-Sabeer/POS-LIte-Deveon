<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountLedgerEntry;

class BalanceSheetService
{
    public function build(string $asOfDate): array
    {
        $accounts = Account::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $rows = [];
        $assetsTotal = 0.0;
        $liabilitiesTotal = 0.0;
        $equityTotal = 0.0;

        foreach ($accounts as $account) {
            $type = $account->account_type ?: $account->type;
            $balance = $this->accountBalanceAsOf($account->id, $asOfDate);

            if (! in_array($type, ['asset', 'liability', 'equity'], true)) {
                continue;
            }

            $rows[] = [
                'id' => $account->id,
                'parent_id' => $account->parent_id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $type,
                'balance' => $balance,
            ];

            if ($type === 'asset') {
                $assetsTotal += $balance;
            } elseif ($type === 'liability') {
                $liabilitiesTotal += $balance;
            } elseif ($type === 'equity') {
                $equityTotal += $balance;
            }
        }

        $retainedEarnings = $this->retainedEarningsAsOf($asOfDate);
        $equityTotal += $retainedEarnings;

        return [
            'as_of_date' => $asOfDate,
            'assets' => $this->hierarchyByType($rows, 'asset'),
            'liabilities' => $this->hierarchyByType($rows, 'liability'),
            'equity' => $this->hierarchyByType($rows, 'equity'),
            'retained_earnings' => $retainedEarnings,
            'assets_total' => $assetsTotal,
            'liabilities_total' => $liabilitiesTotal,
            'equity_total' => $equityTotal,
            'difference' => $assetsTotal - ($liabilitiesTotal + $equityTotal),
        ];
    }

    private function accountBalanceAsOf(int $accountId, string $asOfDate): float
    {
        $last = AccountLedgerEntry::query()
            ->where('account_id', $accountId)
            ->whereDate('entry_date', '<=', $asOfDate)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->first();

        return (float) ($last?->running_balance ?? 0);
    }

    private function retainedEarningsAsOf(string $asOfDate): float
    {
        $incomeIds = Account::query()->whereIn('account_type', ['income'])->orWhere(function ($q) {
            $q->whereNull('account_type')->where('type', 'income');
        })->pluck('id');

        $expenseIds = Account::query()->whereIn('account_type', ['expense'])->orWhere(function ($q) {
            $q->whereNull('account_type')->where('type', 'expense');
        })->pluck('id');

        $income = (float) AccountLedgerEntry::query()
            ->whereIn('account_id', $incomeIds)
            ->whereDate('entry_date', '<=', $asOfDate)
            ->selectRaw('COALESCE(SUM(credit - debit),0) as bal')
            ->value('bal');

        $expense = (float) AccountLedgerEntry::query()
            ->whereIn('account_id', $expenseIds)
            ->whereDate('entry_date', '<=', $asOfDate)
            ->selectRaw('COALESCE(SUM(debit - credit),0) as bal')
            ->value('bal');

        return $income - $expense;
    }

    private function hierarchyByType(array $rows, string $type): array
    {
        $filtered = array_values(array_filter($rows, fn ($row) => $row['type'] === $type));
        $childrenMap = [];

        foreach ($filtered as $row) {
            $childrenMap[(int) ($row['parent_id'] ?? 0)][] = $row;
        }

        $build = function (int $parentId) use (&$build, $childrenMap): array {
            $nodes = $childrenMap[$parentId] ?? [];

            foreach ($nodes as &$node) {
                $node['children'] = $build((int) $node['id']);
                if (! empty($node['children'])) {
                    $node['balance'] = $node['balance'] + array_sum(array_column($node['children'], 'balance'));
                }
            }

            return $nodes;
        };

        return $build(0);
    }
}
