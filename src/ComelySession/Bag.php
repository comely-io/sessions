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
    private $props;
    /** @var array */
    private $bags;

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
     * @param string $prop
     * @return bool
     */
    public function has(string $prop): bool
    {
        return array_key_exists(strtolower($prop), $this->props);
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
     * @return string
     */
    public function serialize(): string
    {
        return serialize(["bags" => $this->bags, "props" => $this->props]);
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

        if (!is_array($bag) || !isset($bag["bags"]) || !isset($bag["props"])) {
            throw new ComelySessionException('Serialized session bag is invalid');
        }

        if (!is_array($bag["bags"])) {
            throw new ComelySessionException('Serialized session bag.bags is invalid');
        }

        if (!is_array($bag["props"])) {
            throw new ComelySessionException('Serialized session bag.props is invalid');
        }

        $this->bags = $bag["bags"];
        $this->props = $bag["props"];
    }
}