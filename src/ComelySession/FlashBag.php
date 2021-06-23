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
 * Class FlashBag
 * @package Comely\Sessions\ComelySession
 */
class FlashBag
{
    /** @var Bag */
    private Bag $current;
    /** @var Bag */
    private Bag $loaded;

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
     * @return Bag[]
     */
    public function __serialize(): array
    {
        return ["current" => $this->current];
    }

    /**
     * @param array $data
     */
    public function __unserialize(array $data): void
    {
        $this->loaded = $data["current"];
        $this->current = new Bag();
    }
}
