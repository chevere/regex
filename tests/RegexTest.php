<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Tests;

use Chevere\Regex\Exceptions\NoMatchException;
use Chevere\Regex\Regex;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RegexTest extends TestCase
{
    public function testConstructInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Regex('#');
    }

    public function testConstruct(): void
    {
        $pattern = '\w+';
        $patternAnchors = "^{$pattern}$";
        $patternDelimitersAnchors = "/{$patternAnchors}/";
        $regex = new Regex($patternDelimitersAnchors);
        $this->assertSame($patternDelimitersAnchors, $regex->__toString());
        $this->assertSame($patternAnchors, $regex->noDelimiters());
        $this->assertSame($pattern, $regex->noDelimitersNoAnchors());
    }

    public function delimiterConflictDataProvider(): array
    {
        return [
            ['abc\/', '/'],
            ['\/abc\/', '/'],
            ['abc\/i', '/'],
            ['\/abc\/i', '/'],
            ['\/ab🐞c\/i', '/'],
        ];
    }

    /**
     * @dataProvider delimiterConflictDataProvider
     */
    public function testDelimiterConflict(string $pattern, string $delimiter): void
    {
        $patternAnchors = "^{$pattern}$";
        $patternDelimitersAnchors = "{$delimiter}{$patternAnchors}{$delimiter}";
        $regex = new Regex($patternDelimitersAnchors);
        $this->assertSame($patternDelimitersAnchors, $regex->__toString());
        $this->assertSame($patternAnchors, $regex->noDelimiters());
        $this->assertSame($pattern, $regex->noDelimitersNoAnchors());
        $patternNoAnchors = "{$pattern}";
        $patternDelimitersAnchors = "{$delimiter}{$patternNoAnchors}{$delimiter}";
        $regex = new Regex($patternDelimitersAnchors);
        $this->assertSame($patternDelimitersAnchors, $regex->__toString());
        $this->assertSame($patternNoAnchors, $regex->noDelimiters());
        $this->assertSame($pattern, $regex->noDelimitersNoAnchors());
    }

    public function testMatch(): void
    {
        $test = 'Hello World!';
        $fail = 'Hola mundo!';
        $pattern = '/^' . $test . '$/';
        $regex = new Regex($pattern);
        $this->assertSame([$test], $regex->match($test));
        $this->assertSame([], $regex->match($fail));
        $regex->assertMatch($test);
        $this->expectException(NoMatchException::class);
        $this->expectExceptionCode(100);
        $regex->assertMatch($fail);
    }

    public function testMatchAll(): void
    {
        $pattern = '/^id-[\d]+$/';
        $test = 'id-123';
        $fail = '123-id';
        $regex = new Regex($pattern);
        $this->assertSame([[$test]], $regex->matchAll($test));
        $this->assertSame([], $regex->matchAll($fail));
        $regex->assertMatchAll($test);
        $this->expectException(NoMatchException::class);
        $this->expectExceptionCode(110);
        $regex->assertMatchAll($fail);
    }
}
