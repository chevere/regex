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

namespace Chevere\Regex;

use Chevere\Regex\Exceptions\NoMatchException;
use Chevere\Regex\Interfaces\RegexInterface;
use InvalidArgumentException;
use LogicException;
use Safe\Exceptions\PcreException;
use Throwable;
use function Chevere\Message\message;
use function Safe\preg_match;
use function Safe\preg_match_all;

final class Regex implements RegexInterface
{
    private string $noDelimiters;

    private string $noDelimitersNoAnchors;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string $pattern
    ) {
        $this->assertRegex();
        $delimiter = $this->pattern[0];
        $this->noDelimiters = trim($this->pattern, $delimiter);
        $this->noDelimitersNoAnchors = strval(
            preg_replace('#^\^(.*)\$$#', '$1', $this->noDelimiters)
        );
    }

    public function __toString(): string
    {
        return $this->pattern;
    }

    public function noDelimiters(): string
    {
        return $this->noDelimiters;
    }

    public function noDelimitersNoAnchors(): string
    {
        return $this->noDelimitersNoAnchors;
    }

    public function match(string $value): array
    {
        try {
            $match = preg_match($this->pattern, $value, $matches);
        }
        // @codeCoverageIgnoreStart
        catch (PcreException $e) {
            throw new LogicException(
                (string) message(
                    'Error `%function%` %message%',
                    function: 'preg_match',
                    message: $e->getMessage()
                )
            );
        }
        // @codeCoverageIgnoreEnd

        return $match === 1 ? $matches : [];
    }

    public function assertMatch(string $value): void
    {
        if ($this->match($value)) {
            return;
        }

        throw new NoMatchException(
            (string) message(
                'String `%string%` does not match regex `%pattern%`',
                pattern: $this->pattern,
                string: $value,
            ),
            100
        );
    }

    public function matchAll(string $value): array
    {
        try {
            $match = preg_match_all($this->pattern, $value, $matches);
        }
        // @codeCoverageIgnoreStart
        catch (PcreException $e) {
            throw new LogicException(
                (string) message(
                    'Error `%function%` %message%',
                    function: 'preg_match_all',
                    message: $e->getMessage()
                )
            );
        }
        // @codeCoverageIgnoreEnd

        return $match === 1 ? $matches : [];
    }

    public function assertMatchAll(string $value): void
    {
        if ($this->matchAll($value)) {
            return;
        }

        throw new NoMatchException(
            (string) message(
                'String `%string%` does not match all `%pattern%`',
                pattern: $this->pattern,
                string: $value,
            ),
            110
        );
    }

    private function assertRegex(): void
    {
        try {
            preg_match($this->pattern, '');
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                previous: $e,
                message: (string) message(
                    'Invalid regex string `%regex%` provided: %error%',
                    regex: $this->pattern,
                    error: static::ERRORS[preg_last_error()],
                )
            );
        }
    }
}
