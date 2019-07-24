<?php
/**
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

use Comely\Sessions\Exception\ComelySessionException;

/**
 * Class Bag
 * @package Comely\Sessions\ComelySession
 */
class Bag implements \Serializable
{
    /** @var array */
    protected $props;
    /** @var array */
    protected $bags;

    /**
     * Bag constructor.
     */
    public function __construct()
    {
        $this->props = [];
        $this->bags = [];
    }

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
     * @param $value
     * @return Bag
     */
    public function set(string $prop, $value): self
    {
        if (is_scalar($value) || is_null($value)) {
            $this->props[strtolower($prop)] = $value;
            return $this;
        }

        throw new \InvalidArgumentException(sprintf('Cannot store value of type "%s" in ComelySession', gettype($value)));
    }

    /**
     * @param string $prop
     * @return mixed|null
     */
    public function get(string $prop)
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
     * @return string
     */
    public function serialize(): string
    {
        return serialize([$this->bags, $this->props]);
    }

    /**
     * @param string $serialized
     * @throws ComelySessionException
     */
    public function unserialize($serialized)
    {
        $bag = @unserialize($serialized, [
            "allowed_classes" => [
                'Comely\Sessions\ComelySession\Bag'
            ]
        ]);

        if (!is_array($bag)) {
            throw new ComelySessionException('Bad/incomplete serialized session bag');
        }

        list($this->bags, $this->props) = $bag;
    }
}