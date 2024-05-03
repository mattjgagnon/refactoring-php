<?php

declare(strict_types=1);

namespace refactors;

use mattjgagnon\RefactoringPhp\refactors\Yatzy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class YatzyTest extends TestCase
{
    #[Test] public function chance_scores_sum_of_all_dice(): void
    {
        $expected = 15;
        $actual = Yatzy::chance(2, 3, 4, 5, 1);
        self::assertSame($expected, $actual);
        self::assertSame(16, Yatzy::chance(3, 3, 4, 5, 1));
    }

    #[Test] public function yatzy_scores_50(): void
    {
        $expected = 50;
        $actual = Yatzy::yatzy_score([
            4,
            4,
            4,
            4,
            4,
        ]);
        self::assertSame($expected, $actual);
        self::assertSame(50, Yatzy::yatzy_score([
            6,
            6,
            6,
            6,
            6,
        ]));
        self::assertSame(0, Yatzy::yatzy_score([
            6,
            6,
            6,
            6,
            3,
        ]));
    }

    #[Test] public function ones(): void
    {
        self::assertSame(1, (new Yatzy(1, 2, 3, 4, 5))->ones());
        self::assertSame(2, (new Yatzy(1, 1, 3, 4, 5))->ones());
        self::assertSame(2, (new Yatzy(1, 2, 1, 4, 5))->ones());
        self::assertSame(0, (new Yatzy(6, 2, 2, 4, 5))->ones());
        self::assertSame(4, (new Yatzy(1, 2, 1, 1, 1))->ones());
    }

    #[Test] public function twos(): void
    {
        self::assertSame(4, Yatzy::twos(1, 2, 3, 2, 6));
        self::assertSame(10, Yatzy::twos(2, 2, 2, 2, 2));
    }

    #[Test] public function threes(): void
    {
        self::assertSame(6, Yatzy::threes(1, 2, 3, 2, 3));
        self::assertSame(9, Yatzy::threes(3, 2, 3, 2, 3));
        self::assertSame(12, Yatzy::threes(2, 3, 3, 3, 3));
    }

    #[Test] public function fours(): void
    {
        self::assertSame(12, (new Yatzy(4, 4, 4, 5, 5))->fours());
        self::assertSame(8, (new Yatzy(4, 4, 5, 5, 5))->fours());
        self::assertSame(4, (new Yatzy(4, 5, 5, 5, 5))->fours());
    }

    #[Test] public function fives(): void
    {
        self::assertSame(10, (new Yatzy(4, 4, 4, 5, 5))->fives());
        self::assertSame(15, (new Yatzy(4, 4, 5, 5, 5))->fives());
        self::assertSame(20, (new Yatzy(4, 5, 5, 5, 5))->fives());
    }

    #[Test] public function sixes(): void
    {
        self::assertSame(0, (new Yatzy(4, 4, 4, 5, 5))->sixes());
        self::assertSame(6, (new Yatzy(4, 4, 6, 5, 5))->sixes());
        self::assertSame(18, (new Yatzy(6, 5, 6, 6, 5))->sixes());
    }

    #[Test] public function one_pair(): void
    {
        self::assertSame(6, Yatzy::score_pair(3, 4, 3, 5, 6));
        self::assertSame(10, Yatzy::score_pair(5, 3, 3, 3, 5));
        self::assertSame(12, Yatzy::score_pair(5, 3, 6, 6, 5));
        self::assertSame(0, Yatzy::score_pair(2, 2, 2, 2, 2));
    }

    #[Test] public function two_pair(): void
    {
        self::assertSame(16, Yatzy::two_pair(3, 3, 5, 4, 5));
        self::assertSame(18, Yatzy::two_pair(3, 3, 6, 6, 6));
        self::assertSame(0, Yatzy::two_pair(3, 3, 6, 5, 4));
    }

    #[Test] public function three_of_a_kind(): void
    {
        self::assertSame(9, Yatzy::three_of_a_kind(3, 3, 3, 4, 5));
        self::assertSame(15, Yatzy::three_of_a_kind(5, 3, 5, 4, 5));
        self::assertSame(9, Yatzy::three_of_a_kind(3, 3, 3, 2, 1));
        self::assertSame(0, Yatzy::three_of_a_kind(1, 2, 3, 4, 5));
    }

    #[Test] public function small_straight(): void
    {
        self::assertSame(15, Yatzy::small_straight(1, 2, 3, 4, 5));
        self::assertSame(15, Yatzy::small_straight(2, 3, 4, 5, 1));
        self::assertSame(0, Yatzy::small_straight(1, 2, 2, 4, 5));
    }

    #[Test] public function large_straight(): void
    {
        self::assertSame(20, Yatzy::large_straight(6, 2, 3, 4, 5));
        self::assertSame(20, Yatzy::large_straight(2, 3, 4, 5, 6));
        self::assertSame(0, Yatzy::large_straight(1, 2, 2, 4, 5));
    }

    #[Test] public function full_house(): void
    {
        self::assertSame(18, Yatzy::full_house(6, 2, 2, 2, 6));
        self::assertSame(0, Yatzy::full_house(2, 3, 4, 5, 6));
    }
}
