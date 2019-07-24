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

namespace Comely\Sessions;

use Comely\Sessions\Exception\ComelySessionException;
use Comely\Sessions\Storage\SessionStorageInterface;

/**
 * Class Sessions
 * @package Comely\Sessions
 */
class Sessions
{
    /** string Version (Major.Minor.Release-Suffix) */
    public const VERSION = "1.0.12";
    /** int Version (Major * 10000 + Minor * 100 + Release) */
    public const VERSION_ID = 10012;

    /** @var SessionStorageInterface */
    private $storage;
    /** @var array */
    private $sessions;

    /**
     * Sessions constructor.
     * @param SessionStorageInterface $storage
     */
    public function __construct(SessionStorageInterface $storage)
    {
        $this->storage = $storage;
        $this->sessions = [];

        // Auto-save session(s) at end of execution
        register_shutdown_function([$this, "save"]);
    }

    /**
     * @param string $id
     * @return ComelySession
     * @throws ComelySessionException
     */
    public function resume(string $id): ComelySession
    {
        $id = strtolower($id);
        if (!preg_match('/^[a-f0-9]{64}$/', $id)) {
            throw new ComelySessionException('Invalid session id');
        }

        if (isset($this->sessions[$id])) {
            return $this->sessions[$id]; // If session is already loaded, return same instance instead of reading allover
        }

        if (!$this->storage->has($id)) {
            throw new ComelySessionException(sprintf('Session "%s" does not exist', $id));
        }

        $sessionData = base64_decode($this->storage->read($id));
        if (!$sessionData) {
            throw new ComelySessionException(sprintf('Failed to decode session "%s"', $id));
        }

        $session = unserialize($sessionData, [
            "allowed_classes" => [
                'Comely\Sessions\ComelySession',
                'Comely\Sessions\ComelySession\Bag'
            ]
        ]);
        if (!$session || !$session instanceof ComelySession) {
            throw new ComelySessionException(sprintf('Failed to unserialize session "%s"', $id));
        }

        $this->sessions[$session->id()] = $session;
        return $session;
    }

    /**
     * @return ComelySession
     * @throws ComelySessionException
     */
    public function start(): ComelySession
    {
        $session = new ComelySession();
        $this->sessions[$session->id()] = $session;
        return $session;
    }

    /**
     * @param string $id
     * @throws ComelySessionException
     */
    public function delete(string $id): void
    {
        $id = strtolower($id);
        if (!preg_match('/^[a-f0-9]{64}$/', $id)) {
            throw new ComelySessionException('Invalid session id');
        }

        if (!$this->storage->has($id)) {
            throw new ComelySessionException(sprintf('Session "%s" does not exist', $id));
        }

        $this->storage->delete($id);
    }

    /**
     * @throws \Exception
     */
    public function save(): void
    {
        /** @var ComelySession $session */
        foreach ($this->sessions as $session) {
            try {
                $serialized = base64_encode(serialize($session));
                $this->storage->write($session->id(), $serialized);
            } catch (\Exception $e) {
                trigger_error(
                    sprintf('Failed to write session [%s]: [%s] %s', $session->id(), get_class($e), $e->getMessage()),
                    E_USER_WARNING
                );

                throw $e;
            }
        }
    }
}