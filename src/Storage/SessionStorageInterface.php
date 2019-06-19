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

namespace Comely\Sessions\Storage;

/**
 * Interface SessionStorageInterface
 * @package Comely\Sessions\Storage
 */
interface SessionStorageInterface
{
    /**
     * @param string $id
     * @return string
     */
    public function read(string $id): string;

    /**
     * @param string $id
     * @param string $serializedSession
     * @return bool
     */
    public function write(string $id, string $serializedSession): bool;

    /**
     * @param string $id
     */
    public function delete(string $id): void;

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * @param string $id
     * @return int
     */
    public function lastModified(string $id): int;

    /**
     * @return array
     */
    public function list(): array;

    /**
     * @return void
     */
    public function flush(): void;
}