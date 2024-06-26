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
        self::assertSame(17, (new Yatzy(1, 2, 3, 5, 6))->chance());
    }

    #[Test] public function yatzy_scores_50(): void
    {
        $expected = 50;
        $actual = (new Yatzy(4, 4, 4, 4, 4,))->yatzy();
        self::assertSame($expected, $actual);
        self::assertSame(50, (new Yatzy(6, 6, 6, 6, 6,))->yatzy());
        self::assertSame(0, (new Yatzy(6, 6, 6, 6, 3,))->yatzy());
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
        self::assertSame(0, (new Yatzy(1, 5, 3, 6, 6))->twos());
    }

    #[Test] public function threes(): void
    {
        self::assertSame(6, (new Yatzy(1, 2, 3, 2, 3))->threes());
        self::assertSame(9, (new Yatzy(3, 2, 3, 2, 3))->threes());
        self::assertSame(12, (new Yatzy(2, 3, 3, 3, 3))->threes());
        self::assertSame(0, (new Yatzy(1, 2, 5, 2, 6))->threes());
    }

    #[Test] public function fours(): void
    {
        self::assertSame(12, (new Yatzy(4, 4, 4, 5, 5))->fours());
        self::assertSame(8, (new Yatzy(4, 4, 5, 5, 5))->fours());
        self::assertSame(4, (new Yatzy(4, 5, 5, 5, 5))->fours());
        self::assertSame(0, (new Yatzy(1, 5, 5, 5, 5))->fours());
    }

    #[Test] public function fives(): void
    {
        self::assertSame(10, (new Yatzy(4, 4, 4, 5, 5))->fives());
        self::assertSame(15, (new Yatzy(4, 4, 5, 5, 5))->fives());
        self::assertSame(20, (new Yatzy(4, 5, 5, 5, 5))->fives());
        self::assertSame(0, (new Yatzy(4, 4, 4, 1, 2))->fives());
    }

    #[Test] public function sixes(): void
    {
        self::assertSame(0, (new Yatzy(4, 4, 4, 5, 5))->sixes());
        self::assertSame(6, (new Yatzy(4, 4, 6, 5, 5))->sixes());
        self::assertSame(18, (new Yatzy(6, 5, 6, 6, 5))->sixes());
    }

    #[Test] public function three_of_a_kind(): void
    {
        self::assertSame(9, (new Yatzy(3, 3, 3, 4, 5))->three_of_a_kind());
        self::assertSame(15, (new Yatzy(5, 3, 5, 4, 5))->three_of_a_kind());
        self::assertSame(9, (new Yatzy(3, 3, 3, 2, 1))->three_of_a_kind());
        self::assertSame(0, (new Yatzy(1, 2, 3, 4, 5))->three_of_a_kind());
    }

    #[Test] public function four_of_a_kind(): void
    {
        self::assertSame(12, (new Yatzy(3, 3, 3, 3, 5))->four_of_a_kind());
        self::assertSame(20, (new Yatzy(5, 3, 5, 5, 5))->four_of_a_kind());
        self::assertSame(24, (new Yatzy(6, 6, 6, 2, 6))->four_of_a_kind());
        self::assertSame(0, (new Yatzy(4, 4, 3, 4, 5))->four_of_a_kind());
    }

    #[Test] public function small_straight(): void
    {
        self::assertSame(15, (new Yatzy(1, 2, 3, 4, 5))->small_straight());
        self::assertSame(15, (new Yatzy(2, 3, 4, 5, 1))->small_straight());
        self::assertSame(0, (new Yatzy(1, 2, 2, 4, 5))->small_straight());
        self::assertSame(15, (new Yatzy(3, 3, 4, 5, 6))->small_straight());
        self::assertSame(15, (new Yatzy(3, 4, 4, 5, 6))->small_straight());
        self::assertSame(15, (new Yatzy(3, 4, 5, 5, 6))->small_straight());
        self::assertSame(15, (new Yatzy(3, 4, 5, 6, 6))->small_straight());
    }

    #[Test] public function large_straight(): void
    {
        self::assertSame(20, (new Yatzy(6, 2, 3, 4, 5))->large_straight());
        self::assertSame(20, (new Yatzy(2, 3, 4, 5, 6))->large_straight());
        self::assertSame(0, (new Yatzy(1, 2, 2, 4, 5))->large_straight());
        self::assertSame(20, (new Yatzy(1, 2, 3, 4, 5))->large_straight());
    }

    #[Test] public function full_house(): void
    {
        self::assertSame(18, (new Yatzy(6, 2, 2, 2, 6))->full_house());
        self::assertSame(0, (new Yatzy(2, 3, 4, 5, 6))->full_house());
    }
}
