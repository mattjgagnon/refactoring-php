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
        $actual = (new Yatzy(2, 3, 4, 5, 1))->chance();
        self::assertSame($expected, $actual);
        self::assertSame(16, (new Yatzy(3, 3, 4, 5, 1))->chance());
    }

    #[Test] public function yatzy_scores_50(): void
    {
        $expected = 50;
        $actual = (new Yatzy(4, 4, 4, 4, 4,))->yatzy_score();
        self::assertSame($expected, $actual);
        self::assertSame(50, (new Yatzy(6, 6, 6, 6, 6,))->yatzy_score());
        self::assertSame(0, (new Yatzy(6, 6, 6, 6, 3,))->yatzy_score());
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
        self::assertSame(4, (new Yatzy(1, 2, 3, 2, 6))->twos());
        self::assertSame(10, (new Yatzy(2, 2, 2, 2, 2))->twos());
    }

    #[Test] public function threes(): void
    {
        self::assertSame(6, (new Yatzy(1, 2, 3, 2, 3))->threes());
        self::assertSame(9, (new Yatzy(3, 2, 3, 2, 3))->threes());
        self::assertSame(12, (new Yatzy(2, 3, 3, 3, 3))->threes());
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
        self::assertSame(6, (new Yatzy(3, 4, 3, 5, 6))->score_pair());
        self::assertSame(10, (new Yatzy(5, 3, 3, 3, 5))->score_pair());
        self::assertSame(12, (new Yatzy(5, 3, 6, 6, 5))->score_pair());
        self::assertSame(0, (new Yatzy(2, 2, 2, 2, 2))->score_pair());
    }

    #[Test] public function two_pair(): void
    {
        self::assertSame(16, (new Yatzy(3, 3, 5, 4, 5))->two_pair());
        self::assertSame(18, (new Yatzy(3, 3, 6, 6, 6))->two_pair());
        self::assertSame(0, (new Yatzy(3, 3, 6, 5, 4))->two_pair());
    }

    #[Test] public function three_of_a_kind(): void
    {
        self::assertSame(9, (new Yatzy(3, 3, 3, 4, 5))->three_of_a_kind());
        self::assertSame(15, (new Yatzy(5, 3, 5, 4, 5))->three_of_a_kind());
        self::assertSame(9, (new Yatzy(3, 3, 3, 2, 1))->three_of_a_kind());
        self::assertSame(0, (new Yatzy(1, 2, 3, 4, 5))->three_of_a_kind());
    }

    #[Test] public function small_straight(): void
    {
        self::assertSame(15, (new Yatzy(1, 2, 3, 4, 5))->small_straight());
        self::assertSame(15, (new Yatzy(2, 3, 4, 5, 1))->small_straight());
        self::assertSame(0, (new Yatzy(1, 2, 2, 4, 5))->small_straight());
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
