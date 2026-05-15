<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Diff;

final class LineDiff
{
    /**
     * Computes a line-by-line diff between two strings using Longest Common Subsequence.
     *
     * @return list<array{type: 'equal'|'added'|'removed', line: string}>
     */
    public function diff(string $from, string $to): array
    {
        $fromLines = $from === '' ? [] : explode("\n", $from);
        $toLines = $to === '' ? [] : explode("\n", $to);

        $m = count($fromLines);
        $n = count($toLines);

        /** @var array<int, array<int, int>> $lcs */
        $lcs = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($fromLines[$i - 1] === $toLines[$j - 1]) {
                    $lcs[$i][$j] = $lcs[$i - 1][$j - 1] + 1;
                } else {
                    $lcs[$i][$j] = max($lcs[$i - 1][$j], $lcs[$i][$j - 1]);
                }
            }
        }

        /** @var list<array{type: 'equal'|'added'|'removed', line: string}> $diff */
        $diff = [];

        $i = $m;
        $j = $n;

        while ($i > 0 && $j > 0) {
            if ($fromLines[$i - 1] === $toLines[$j - 1]) {
                array_unshift($diff, ['type' => 'equal', 'line' => $fromLines[$i - 1]]);
                $i--;
                $j--;
            } elseif ($lcs[$i - 1][$j] > $lcs[$i][$j - 1]) {
                array_unshift($diff, ['type' => 'removed', 'line' => $fromLines[$i - 1]]);
                $i--;
            } else {
                array_unshift($diff, ['type' => 'added', 'line' => $toLines[$j - 1]]);
                $j--;
            }
        }

        while ($i > 0) {
            array_unshift($diff, ['type' => 'removed', 'line' => $fromLines[$i - 1]]);
            $i--;
        }

        while ($j > 0) {
            array_unshift($diff, ['type' => 'added', 'line' => $toLines[$j - 1]]);
            $j--;
        }

        return $diff;
    }
}
