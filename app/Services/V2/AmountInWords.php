<?php

namespace App\Services\V2;

class AmountInWords
{
    private const ONES = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    private const TENS = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    public function rupees(float $amount): string
    {
        $whole = (int) floor($amount);
        $paisa = (int) round(($amount - $whole) * 100);
        $text = $this->number($whole) . ' Rupees';

        if ($paisa > 0) {
            $text .= ' and ' . $this->number($paisa) . ' Paisa';
        }

        return trim($text) . ' Only';
    }

    private function number(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $parts = [];
        foreach ([10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand', 100 => 'Hundred'] as $value => $label) {
            if ($number >= $value) {
                $parts[] = $this->number(intdiv($number, $value)) . ' ' . $label;
                $number %= $value;
            }
        }

        if ($number >= 20) {
            $parts[] = self::TENS[intdiv($number, 10)];
            $number %= 10;
        }

        if ($number > 0) {
            $parts[] = self::ONES[$number];
        }

        return implode(' ', array_filter($parts));
    }
}
