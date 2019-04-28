<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Opulence\Console\Tests\Responses\Formatters;

use Opulence\Console\Commands\CommandCollection;
use Opulence\Console\Commands\Compilers\Compiler;
use Opulence\Console\Requests\Argument;
use Opulence\Console\Requests\ArgumentTypes;
use Opulence\Console\Requests\Option;
use Opulence\Console\Requests\OptionTypes;
use Opulence\Console\Responses\Formatters\CommandFormatter;
use Opulence\Console\Tests\Commands\Mocks\SimpleCommand;

/**
 * Tests the command formatter
 */
class CommandFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var CommandFormatter The formatter to use in tests */
    private $formatter;
    /** @var CommandCollection The list of registered commands */
    private $commandCollection;

    /**
     * Sets up the tests
     */
    protected function setUp(): void
    {
        $this->formatter = new CommandFormatter();
        $this->commandCollection = new CommandCollection(new Compiler());
    }

    /**
     * Tests formatting a command with mix of arguments
     */
    public function testFormattingCommandWithMixOfArguments(): void
    {
        $command = new SimpleCommand('foo', 'Foo command');
        $command->addArgument(new Argument(
            'bar',
            ArgumentTypes::REQUIRED,
            'Bar argument'
        ));
        $command->addArgument(new Argument(
            'baz',
            ArgumentTypes::OPTIONAL,
            'Baz argument'
        ));
        $command->addArgument(new Argument(
            'blah',
            ArgumentTypes::IS_ARRAY,
            'Blah argument'
        ));
        $this->assertEquals('foo [--help|-h] bar [baz] blah1...blahN', $this->formatter->format($command));
    }

    /**
     * Tests formatting a command with multiple arguments
     */
    public function testFormattingCommandWithMultipleArguments(): void
    {
        $command = new SimpleCommand('foo', 'Foo command');
        $command->addArgument(new Argument(
            'bar',
            ArgumentTypes::REQUIRED,
            'Bar argument'
        ));
        $command->addArgument(new Argument(
            'baz',
            ArgumentTypes::REQUIRED,
            'Baz argument'
        ));
        $this->assertEquals('foo [--help|-h] bar baz', $this->formatter->format($command));
    }

    /**
     * Tests formatting a command with no arguments or options
     */
    public function testFormattingCommandWithNoArgumentsOrOptions(): void
    {
        $command = new SimpleCommand('foo', 'Foo command');
        $this->assertEquals('foo [--help|-h]', $this->formatter->format($command));
    }

    /**
     * Tests formatting a command with one argument
     */
    public function testFormattingCommandWithOneArgument(): void
    {
        $command = new SimpleCommand('foo', 'Foo command');
        $command->addArgument(new Argument(
            'bar',
            ArgumentTypes::REQUIRED,
            'Bar argument'
        ));
        $this->assertEquals('foo [--help|-h] bar', $this->formatter->format($command));
    }

    /**
     * Tests formatting a command with one option with a default value
     */
    public function testFormattingCommandWithOneOptionWithDefaultValue(): void
    {
        $command = new SimpleCommand('foo', 'Foo command');
        $command->addOption(new Option(
            'bar',
            'b',
            OptionTypes::OPTIONAL_VALUE,
            'Bar option',
            'yes'
        ));
        $this->assertEquals('foo [--help|-h] [--bar=yes|-b]', $this->formatter->format($command));
    }

    /**
     * Tests formatting a command with one option with default value but no short name
     */
    public function testFormattingCommandWithOneOptionWithDefaultValueButNoShortName(): void
    {
        $command = new SimpleCommand('foo', 'Foo command');
        $command->addOption(new Option(
            'bar',
            null,
            OptionTypes::OPTIONAL_VALUE,
            'Bar option',
            'yes'
        ));
        $this->assertEquals('foo [--help|-h] [--bar=yes]', $this->formatter->format($command));
    }

    /**
     * Tests formatting a command with one option with no short name
     */
    public function testFormattingCommandWithOneOptionWithoutShortName(): void
    {
        $command = new SimpleCommand('foo', 'Foo command');
        $command->addOption(new Option(
            'bar',
            null,
            OptionTypes::NO_VALUE,
            'Bar option'
        ));
        $this->assertEquals('foo [--help|-h] [--bar]', $this->formatter->format($command));
    }

    /**
     * Tests formatting a command with one optional argument
     */
    public function testFormattingCommandWithOneOptionalArgument(): void
    {
        $command = new SimpleCommand('foo', 'Foo command');
        $command->addArgument(new Argument(
            'bar',
            ArgumentTypes::OPTIONAL,
            'Bar argument'
        ));
        $this->assertEquals('foo [--help|-h] [bar]', $this->formatter->format($command));
    }

    /**
     * Tests formatting a command with an optional array argument
     */
    public function testFormattingCommandWithOptionalArrayArgument(): void
    {
        $command = new SimpleCommand('foo', 'Foo command');
        $command->addArgument(new Argument(
            'blah',
            ArgumentTypes::IS_ARRAY | ArgumentTypes::OPTIONAL,
            'Blah argument'
        ));
        $this->assertEquals('foo [--help|-h] [blah1]...[blahN]', $this->formatter->format($command));
    }
}