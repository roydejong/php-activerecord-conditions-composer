<?php

namespace ActiveRecordUtils\Tests\Composers;

use ActiveRecordUtils\Composers\Conditions;
use PHPUnit\Framework\TestCase;

class ConditionsTest extends TestCase
{
    public function testQuery__where()
    {
        $this->assertSame(
            ["(employee_id = ?)", 123],
            Conditions::make()
                ->where('employee_id = ?', 123)
                ->value(),
            "`where` query test should produce simple statement text with 1 ground and 1 parameter."
        );
    }

    public function testQuery__where_and_or()
    {
        $this->assertSame(
            ["(employee_id = ? AND login_id = ? OR auth_id = ?)", 123, 456, 789],
            Conditions::make()
                ->where('employee_id = ?', 123)
                ->and('login_id = ?', 456)
                ->or('auth_id = ?', 789)
                ->value(),
            "`where_and_or` query test should produce simple statement text with 1 group and 3 parameters."
        );
    }

    public function testQuery__where_or_andWhere()
    {
        $this->assertSame(
            ["(employee_id = ? OR login_id = ?) AND (is_enabled = 1)", 123, 123],
            Conditions::make()
                ->where('employee_id = ?', 123)
                ->or('login_id = ?', 123)
                ->andWhere('is_enabled = 1')
                ->value(),
            "Grouped or/andWhere query test should 2 'AND' groups, the first of which with an internal 'OR'."
        );
    }

    /**
     * @depends testQuery__where
     */
    public function testEntry()
    {
        $expectedEntry = [
            'conditions' => ['(employee_id = ?)', 123]
        ];

        $this->assertSame(
            $expectedEntry,
            Conditions::make()
                ->where('employee_id = ?', 123)
                ->entry(),
            '$conditions->entry() should produce the value() indexed by "conditions" with no further data.'
        );
    }

    public function testCreateAndFormatEmpty()
    {
        $conditions = Conditions::make();

        $this->assertInstanceOf("ActiveRecordUtils\Composers\Conditions", $conditions,
            "Conditions::make() should result in a new, blank, Conditions object");

        $expectedEntry = [
            'conditions' => null
        ];

        $this->assertSame($expectedEntry, $conditions->entry(),
            'If nothing was set, $conditions->entry() should result in a valid array with a NULL value');

        $this->assertNull($conditions->value(),
            'If nothing was set, $conditions->value() should be NULL');
    }

    public function testParamCountException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('got 3 params, expected 2');

        Conditions::make()
            ->where('some_value = ? AND other_value = ?', 123, 456, 789)
            ->value();
    }

    public function testEmptyStatementException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('empty SQL expression');

        Conditions::make()
            ->where('', 123, 456, 789)
            ->value();
    }
}
