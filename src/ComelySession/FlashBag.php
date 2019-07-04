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

/**
 * Class FlashBag
 * @package Comely\Sessions\ComelySession
 */
class FlashBag implements \Serializable
{
    /** @var Bag */
    private $current;
    /** @var Bag */
    private $loaded;

    /**
     * FlashBag constructor.
     */
    public function __construct()
    {
        $this->current = new Bag();
        $this->loaded = new Bag();
    }

    /**
     * @return Bag
     */
    public function bags(): Bag
    {
        return $this->current;
    }

    /**
     * @return Bag
     */
    public function last(): Bag
    {
        return $this->loaded;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize($this->current);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $unserialize = unserialize($serialized, [
            "allowed_classes" => [
                'Comely\Sessions\ComelySession\Bag'
            ]
        ]);

        if (!$unserialize instanceof Bag) {
            throw new \UnexpectedValueException('Failed to unserialize flash bags');
        }

        $this->loaded = $unserialize;
        $this->current = new Bag();
    }
}