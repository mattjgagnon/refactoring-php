<?php

declare(strict_types=1);

namespace mattjgagnon\RefactoringPhp\refactors;

final class Yatzy
{
    /**
     * @var array<int, int>
     */
    private array $dice;

    public function __construct(private readonly int $d1, private readonly int $d2, private readonly int $d3, private readonly int $d4, private readonly int $d5)
    {
        $this->dice = array_fill(0, 6, 0);
        $this->dice[0] = $this->d1;
        $this->dice[1] = $this->d2;
        $this->dice[2] = $this->d3;
        $this->dice[3] = $this->d4;
        $this->dice[4] = $this->d5;
    }

    public function full_house(): int
    {
        $counts = $this->get_counts();

        [
            $two_part,
            $two_part_pips,
        ] = $this->get_full_house_part(2, $counts);

        [
            $three_part,
            $three_part_pips,
        ] = $this->get_full_house_part(3, $counts);

        if ($this->is_full_house($two_part, $three_part)) {
            return $this->sum_full_house($two_part_pips, $three_part_pips);
        }

        return 0;
    }

    private function get_counts(): array
    {
        $counts = array_fill(0, count($this->dice) + 1, 0);

        foreach ($this->dice as $die) {
            $counts[$die - 1] += 1;
        }

        return $counts;
    }

    private function get_full_house_part(int $part, array $counts): array
    {
        $is_part = FALSE;
        $part_count = 0;

        foreach (range(0, 5) as $i) {
            if ($counts[$i] === $part) {
                $is_part = TRUE;
                $part_count = $i + 1;
            }
        }

        return [
            $is_part,
            $part_count,
        ];
    }

    private function is_full_house(bool $two_part, bool $three_part): bool
    {
        return $two_part && $three_part;
    }

    private function sum_full_house(int $two_part_pips, int $three_part_pips): int
    {
        return $two_part_pips * 2 + $three_part_pips * 3;
    }

    public function large_straight(): int
    {
        $counts = $this->get_counts();

        foreach ($counts as $value) {
            if ($value > 1) {
                return 0;
            }
        }

        return 20;
    }

    public function small_straight(): int
    {
        $counts = $this->get_counts();

        if ($counts[0] === 1 && $counts[1] === 1 && $counts[2] === 1 && $counts[3] === 1 && $counts[4] === 1) {
            return 15;
        }

        return 0;
    }

    public function three_of_a_kind(): int
    {
        $counts = $this->get_counts();
        return $this->get_sum_for_kind(3, $counts);
    }

    private function get_sum_for_kind(int $kind, array $counts): int
    {
        $sum = 0;

        for ($i = 0; $i !== 6; $i++) {
            $sum = $this->sum_kinds($counts[$i], $i, $kind, $sum);
        }

        return $sum;
    }

    private function sum_kinds($counts, int $i, int $kind, int $sum): int
    {
        if ($counts >= $kind) {
            $sum = ($i + 1) * $kind;
        }

        return $sum;
    }

    public function four_of_a_kind(): int
    {
        $counts = $this->get_counts();
        return $this->get_sum_for_kind(4, $counts);
    }

    public function two_pair(): int
    {
        $counts = $this->get_counts();
        $n = 0;
        $score = 0;

        for ($i = 0; $i !== 6; $i++) {
            if ($counts[6 - $i - 1] >= 2) {
                $n += 1;
                $score += (6 - $i);
            }
        }

        if ($n === 2) {
            return $score * 2;
        }

        return 0;
    }

    public function score_pair(): int
    {
        $counts = $this->get_counts();

        for ($at = 0; $at !== 6; $at++) {
            if ($counts[6 - $at - 1] === 2) {
                return (6 - $at) * 2;
            }
        }

        return 0;
    }

    public function yatzy_score(): int
    {
        $counts = $this->get_counts();

        foreach (range(0, count($counts) - 1) as $i) {
            if ($counts[$i] === 5) {
                return 50;
            }
        }

        return 0;
    }

    public function chance(): int
    {
        $total = 0;
        $total += $this->d1;
        $total += $this->d2;
        $total += $this->d3;
        $total += $this->d4;
        $total += $this->d5;
        return $total;
    }

    public function threes(): int
    {
        return $this->get_sum_for_value(3);
    }

    private function get_sum_for_value(int $value): int
    {
        $sum = 0;

        for ($pips = 0; $pips < 5; $pips++) {
            if ($this->dice[$pips] === $value) {
                $sum += $value;
            }
        }

        return $sum;
    }

    public function twos(): int
    {
        return $this->get_sum_for_value(2);
    }

    public function ones(): int
    {
        return $this->get_sum_for_value(1);
    }

    public function fours(): int
    {
        return $this->get_sum_for_value(4);
    }

    public function fives(): int
    {
        return $this->get_sum_for_value(5);
    }

    public function sixes(): int
    {
        return $this->get_sum_for_value(6);
    }
}
