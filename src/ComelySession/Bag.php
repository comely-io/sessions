<?php
/*
 * This file is a part of "comely-io/sessions" package.
 * https://github.com/comely-io/sessions
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comely-io/sessions/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Sessions\ComelySession;

/**
 * Class Bag
 * @package Comely\Sessions\ComelySession
 */
class Bag
{
    /** @var array */
    protected array $props = [];
    /** @var array */
    protected array $bags = [];

    /**
     * @param string $name
     * @return Bag
     */
    public function bag(string $name): Bag
    {
        $name = strtolower($name);
        return $this->bags[$name] ?? $this->bags[$name] = new Bag;
    }

    /**
     * @param string ...$props
     * @return bool
     */
    public function has(string ...$props): bool
    {
        foreach ($props as $prop) {
            if (!array_key_exists(strtolower($prop), $this->props)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasBag(string $name): bool
    {
        return array_key_exists(strtolower($name), $this->bags);
    }

    /**
     * @param string $prop
     * @param string|int|float|bool|null $value
     * @return $this
     */
    public function set(string $prop, string|int|float|bool|null $value): self
    {
        $this->props[strtolower($prop)] = $value;
        return $this;
    }

    /**
     * @param string $prop
     * @return string|int|float|bool|null
     */
    public function get(string $prop): string|int|float|bool|null
    {
        return $this->props[strtolower($prop)] ?? null;
    }

    /**
     * @param string $prop
     * @return Bag
     */
    public function delete(string $prop): self
    {
        $key = strtolower($prop);
        unset($this->props[$key], $this->bags[$key]);
        return $this;
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return [
            "bags" => $this->bags,
            "props" => $this->props,
        ];
    }

    /**
     * @param array $data
     */
    public function __unserialize(array $data): void
    {
        $this->bags = $data["bags"] ?? [];
        $this->props = $data["props"] ?? [];
    }
}
