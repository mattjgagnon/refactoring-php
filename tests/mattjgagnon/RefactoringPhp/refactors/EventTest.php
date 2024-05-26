<?php

namespace mattjgagnon\RefactoringPhp\refactors;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventTest extends TestCase
{
    #[Test] public function it_can_delete_events()
    {

    }

    #[Test] public function it_can_update_events()
    {

    }

    #[Test] public function calling_insert_events_with_invalid_permissions_returns_false()
    {
        // assemble
        $event = new Event();

        // act
        $result = $event->events_insert();

        // assert
        $this->assertFalse($result);
    }

    #[Test] public function calling_insert_events_with_empty_status_returns_alert_link()
    {
        // assemble
        $_GET['arr_perm'] = [
            0 => [],
            1 => ['permissions'],
        ];
        $event = new Event();

        // act
        $result = $event->events_insert();

        // assert
        $this->assertIsString($result);
        $this->assertStringContainsString('alert', $result);
        $this->assertStringContainsString('a href', $result);
    }

    #[Test] public function it_generates_an_editable_events_form()
    {

    }
}

function getTablePermissions(string $string): array
{
    return $_GET['arr_perm'] ?? [];
}
