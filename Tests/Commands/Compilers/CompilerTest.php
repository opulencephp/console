<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Opulence\Console\Tests\Commands\Compilers;

use Opulence\Console\Commands\Command;
use Opulence\Console\Commands\CommandCollection;
use Opulence\Console\Commands\Compilers\Compiler;
use Opulence\Console\Requests\Argument;
use Opulence\Console\Requests\ArgumentTypes;
use Opulence\Console\Requests\Option;
use Opulence\Console\Requests\OptionTypes;
use Opulence\Console\Requests\Request;
use Opulence\Console\Tests\Commands\Mocks\SimpleCommand;
use RuntimeException;

/**
 * Tests the command compiler
 */
class CompilerTest extends \PHPUnit\Framework\TestCase
{
    /** @var Compiler The compiler to use in tests */
    private $compiler;
    /** @var Command The command to use in tests */
    private $command;
    /** @var CommandCollection The list of registered commands */
    private $commandCollection;
    /** @var Request The request to use in tests */
    private $request;

    protected function setUp(): void
    {
        $this->compiler = new Compiler();
        $this->commandCollection = new CommandCollection($this->compiler);
        $this->command = new SimpleCommand('Foo', 'The foo command');
        $this->request = new Request();
    }

    public function testCompilingArrayArgument(): void
    {
        $argument = new Argument('foo', ArgumentTypes::IS_ARRAY, 'Foo command');
        $this->command->addArgument($argument);
        $this->request->addArgumentValue('bar');
        $this->request->addArgumentValue('baz');
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals(['bar', 'baz'], $compiledCommand->getArgumentValue('foo'));
    }

    public function testCompilingArrayArgumentWitOptionalArgumentAfter(): void
    {
        $arrayArgument = new Argument('foo', ArgumentTypes::IS_ARRAY, 'Foo command');
        $optionalArgument = new Argument('bar', ArgumentTypes::OPTIONAL, 'Bar command', 'baz');
        $this->command->addArgument($arrayArgument);
        $this->command->addArgument($optionalArgument);
        $this->request->addArgumentValue('bar');
        $this->request->addArgumentValue('baz');
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals(['bar', 'baz'], $compiledCommand->getArgumentValue('foo'));
        $this->assertEquals('baz', $compiledCommand->getArgumentValue('bar'));
    }

    public function testCompilingArrayArgumentWitRequiredArgumentAfter(): void
    {
        $this->expectException(RuntimeException::class);
        $arrayArgument = new Argument('foo', ArgumentTypes::IS_ARRAY, 'Foo command');
        $requiredArgument = new Argument('bar', ArgumentTypes::REQUIRED, 'Bar command');
        $this->command->addArgument($arrayArgument);
        $this->command->addArgument($requiredArgument);
        $this->request->addArgumentValue('bar');
        $this->request->addArgumentValue('baz');
        $this->compiler->compile($this->command, $this->request);
    }

    public function testCompilingNoValueOption(): void
    {
        $option = new Option('foo', 'f', OptionTypes::NO_VALUE, 'Foo command');
        $this->command->addOption($option);
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertNull($compiledCommand->getOptionValue('foo'));
    }

    public function testCompilingNoValueOptionWithAValue(): void
    {
        $this->expectException(RuntimeException::class);
        $option = new Option('foo', 'f', OptionTypes::NO_VALUE, 'Foo command');
        $this->command->addOption($option);
        $this->request->addOptionValue('foo', 'bar');
        $this->compiler->compile($this->command, $this->request);
    }

    public function testCompilingOptionWithNullShortNameStillCompiles(): void
    {
        $option = new Option('foo', null, OptionTypes::REQUIRED_VALUE, 'Foo command');
        $this->command->addOption($option);
        $this->request->addOptionValue('foo', 'bar');
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals('bar', $compiledCommand->getOptionValue('foo'));
    }

    public function testCompilingOptionalArgument(): void
    {
        $optionalArgument = new Argument('foo', ArgumentTypes::OPTIONAL, 'Foo command');
        $this->command->addArgument($optionalArgument);
        $this->request->addArgumentValue('bar');
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals('bar', $compiledCommand->getArgumentValue('foo'));
    }

    public function testCompilingOptionalArgumentWithDefaultValue(): void
    {
        $optionalArgument = new Argument('foo', ArgumentTypes::OPTIONAL, 'Foo command', 'baz');
        $this->command->addArgument($optionalArgument);
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals('baz', $compiledCommand->getArgumentValue('foo'));
    }

    public function testCompilingOptionalArgumentsWithoutAnyValues(): void
    {
        $optionalArgument1 = new Argument('foo', ArgumentTypes::OPTIONAL, 'Foo command', 'fooValue');
        $optionalArgument2 = new Argument('bar', ArgumentTypes::OPTIONAL, 'Bar command', 'barValue');
        $this->command->addArgument($optionalArgument1);
        $this->command->addArgument($optionalArgument2);
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals('fooValue', $compiledCommand->getArgumentValue('foo'));
        $this->assertEquals('barValue', $compiledCommand->getArgumentValue('bar'));
    }

    public function testCompilingOptionalValueOptionWithDefaultValue(): void
    {
        $option = new Option('foo', 'f', OptionTypes::OPTIONAL_VALUE, 'Foo command', 'bar');
        $this->command->addOption($option);
        $this->request->addOptionValue('foo', null);
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals('bar', $compiledCommand->getOptionValue('foo'));
    }

    public function testCompilingRequiredAndOptionalArgument(): void
    {
        $requiredArgument = new Argument('foo', ArgumentTypes::REQUIRED, 'Foo command');
        $optionalArgument = new Argument('bar', ArgumentTypes::OPTIONAL, 'Bar command', 'baz');
        $this->command->addArgument($requiredArgument);
        $this->command->addArgument($optionalArgument);
        $this->request->addArgumentValue('bar');
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals('bar', $compiledCommand->getArgumentValue('foo'));
        $this->assertEquals('baz', $compiledCommand->getArgumentValue('bar'));
    }

    public function testCompilingRequiredArgument(): void
    {
        $requiredArgument = new Argument('foo', ArgumentTypes::REQUIRED, 'Foo command');
        $this->command->addArgument($requiredArgument);
        $this->request->addArgumentValue('bar');
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals('bar', $compiledCommand->getArgumentValue('foo'));
    }

    public function testCompilingRequiredArgumentWithoutValue(): void
    {
        $this->expectException(RuntimeException::class);
        $required = new Argument('foo', ArgumentTypes::REQUIRED, 'Foo command');
        $this->command->addArgument($required);
        $this->compiler->compile($this->command, $this->request);
    }

    public function testCompilingRequiredArgumentsWithoutSpecifyingAllValues(): void
    {
        $this->expectException(RuntimeException::class);
        $requiredArgument1 = new Argument('foo', ArgumentTypes::REQUIRED, 'Foo command');
        $requiredArgument2 = new Argument('bar', ArgumentTypes::REQUIRED, 'Bar command');
        $this->command->addArgument($requiredArgument1);
        $this->command->addArgument($requiredArgument2);
        $this->request->addArgumentValue('bar');
        $this->compiler->compile($this->command, $this->request);
    }

    public function testCompilingRequiredValueOption(): void
    {
        $option = new Option('foo', 'f', OptionTypes::REQUIRED_VALUE, 'Foo command');
        $this->command->addOption($option);
        $this->request->addOptionValue('foo', 'bar');
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals('bar', $compiledCommand->getOptionValue('foo'));
    }

    public function testCompilingRequiredValueOptionWithoutValue(): void
    {
        $this->expectException(RuntimeException::class);
        $option = new Option('foo', 'f', OptionTypes::REQUIRED_VALUE, 'Foo command');
        $this->command->addOption($option);
        $this->request->addOptionValue('foo', null);
        $this->compiler->compile($this->command, $this->request);
    }

    public function testDefaultValueIsUsedForOptionsThatAreNotSet(): void
    {
        $requiredValueOption = new Option('foo', 'f', OptionTypes::REQUIRED_VALUE, 'Foo command', 'foo value');
        $optionalValueOption = new Option('bar', 'b', OptionTypes::OPTIONAL_VALUE, 'Bar command', 'bar value');
        $noValueOption = new Option('baz', 'z', OptionTypes::NO_VALUE, 'Baz command', 'baz value');
        $this->command->addOption($requiredValueOption);
        $this->command->addOption($optionalValueOption);
        $this->command->addOption($noValueOption);
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals('foo value', $compiledCommand->getOptionValue('foo'));
        $this->assertEquals('bar value', $compiledCommand->getOptionValue('bar'));
        $this->assertNull($compiledCommand->getOptionValue('baz'));
    }

    public function testPassingTooManyArguments(): void
    {
        $this->expectException(RuntimeException::class);
        $argument = new Argument('foo', ArgumentTypes::REQUIRED, 'Foo command');
        $this->command->addArgument($argument);
        $this->request->addArgumentValue('bar');
        $this->request->addArgumentValue('baz');
        $this->compiler->compile($this->command, $this->request);
    }

    public function testThatShortAndLongOptionsPointToSameOption(): void
    {
        $option = new Option('foo', 'f', OptionTypes::OPTIONAL_VALUE, 'Foo command', 'bar');
        $this->command->addOption($option);
        $this->request->addOptionValue('f', null);
        $compiledCommand = $this->compiler->compile($this->command, $this->request);
        $this->assertEquals('bar', $compiledCommand->getOptionValue('foo'));
    }
}
