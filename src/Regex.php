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
use Throwable;

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
        $this->assertPattern();
        $delimiter = $this->pattern[0];
        $this->noDelimiters = substr($this->pattern, 1);
        /** @var int $tailPos */
        $tailPos = strrpos($this->noDelimiters, $delimiter);
        $this->noDelimiters = substr($this->noDelimiters, 0, $tailPos);
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
            $match = @preg_match($this->pattern, $value, $matches);
        } catch (Throwable) {
            $match = false;
        }
        if (is_int($match)) {
            return $match === 1 ? $matches : [];
        }

        // @codeCoverageIgnoreStart
        throw new LogicException(
            sprintf(
                'Error `%s` %s',
                'preg_match',
                static::ERRORS[preg_last_error()],
            )
        );
        // @codeCoverageIgnoreEnd
    }

    public function assertMatch(string $value): void
    {
        if ($this->match($value)) {
            return;
        }

        throw new NoMatchException(
            sprintf(
                'String `%s` does not match regex `%s`',
                $value,
                $this->pattern,
            ),
            100
        );
    }

    public function matchAll(string $value): array
    {
        try {
            $match = @preg_match_all($this->pattern, $value, $matches);
        } catch (Throwable) {
            $match = false;
        }
        if (is_int($match)) {
            return $match === 1 ? $matches : [];
        }

        // @codeCoverageIgnoreStart
        throw new LogicException(
            sprintf(
                'Error `%s` %s',
                'preg_match',
                static::ERRORS[preg_last_error()],
            )
        );
        // @codeCoverageIgnoreEnd
    }

    public function assertMatchAll(string $value): void
    {
        if ($this->matchAll($value)) {
            return;
        }

        throw new NoMatchException(
            sprintf(
                'String `%s` does not match all `%s`',
                $value,
                $this->pattern,
            ),
            110
        );
    }

    private function assertPattern(): void
    {
        try {
            if (@preg_match($this->pattern, '') !== false) {
                return;
            }
        } catch (Throwable) {
        }

        throw new InvalidArgumentException(
            sprintf(
                'Invalid regex pattern `%s` provided: %s',
                $this->pattern,
                static::ERRORS[preg_last_error()],
            )
        );
    }
}
