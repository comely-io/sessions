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

namespace Comely\Sessions;

use Comely\Sessions\ComelySession\Bag;
use Comely\Sessions\ComelySession\FlashBag;
use Comely\Sessions\Exception\ComelySessionException;

/**
 * Class ComelySession
 * @package Comely\Sessions
 */
class ComelySession implements \Serializable
{
    /** @var string */
    private string $id;
    /** @var Bag */
    private Bag $baggage;
    /** @var Bag */
    private Bag $meta;
    /** @var FlashBag */
    private FlashBag $flash;
    /** @var int */
    private int $timeStamp;

    /**
     * ComelySession constructor.
     * @throws ComelySessionException
     */
    public function __construct()
    {
        $this->regenerateSessionId();
        $this->baggage = new Bag();
        $this->meta = new Bag();
        $this->flash = new FlashBag();
        $this->timeStamp = time();
    }

    /**
     * @param string|null $nonce
     * @return $this
     * @throws ComelySessionException
     */
    public function regenerateSessionId(?string $nonce = null): self
    {
        try {
            $sessionId = random_bytes(32);
        } catch (\Exception) {
            throw new ComelySessionException('Failed to generate session Id');
        }

        if ($nonce) {
            $sessionId = substr(hash_hmac("sha512", $sessionId, $nonce, true), 0, 32);
        }

        $this->id = bin2hex($sessionId);
        return $this;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return Bag
     */
    public function bags(): Bag
    {
        return $this->baggage;
    }

    /**
     * @return Bag
     */
    public function meta(): Bag
    {
        return $this->meta;
    }

    /**
     * @return FlashBag
     */
    public function flash(): FlashBag
    {
        return $this->flash;
    }

    /**
     * @return int
     */
    public function timeStamp(): int
    {
        return $this->timeStamp;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->baggage,
            $this->meta,
            $this->flash,
            time()
        ]);
    }

    /**
     * @param string $data
     * @throws ComelySessionException
     */
    public function unserialize($data)
    {
        $session = @unserialize($data, [
            "allowed_classes" => [
                'Comely\Sessions\ComelySession\Bag',
                'Comely\Sessions\ComelySession\FlashBag',
            ]
        ]);

        if (!is_array($session)) {
            throw new ComelySessionException('Bad/incomplete serialized session');
        }

        list(
            $this->id,
            $this->baggage,
            $this->meta,
            $this->flash,
            $this->timeStamp
            ) = $session;

        if (!is_string($this->id) || !preg_match('/^[a-f0-9]{64}$/', $this->id)) {
            throw new ComelySessionException('Invalid serialized session Id');
        }
    }
}
