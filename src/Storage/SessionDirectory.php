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

use Comely\Filesystem\Directory;
use Comely\Filesystem\Exception\FilesystemException;
use Comely\Filesystem\Exception\PathNotExistException;
use Comely\Filesystem\Exception\PathPermissionException;
use Comely\Sessions\Exception\StorageException;

/**
 * Class SessionLocalStorage
 * @package Comely\Sessions\Storage
 */
class SessionDirectory implements SessionStorageInterface
{
    /** @var Directory */
    private $dir;

    /**
     * SessionsDirectory constructor.
     * @param Directory $dir
     * @throws StorageException
     */
    public function __construct(Directory $dir)
    {
        if (!$dir->permissions()->read()) {
            throw new StorageException('Sessions directory is not readable');
        } elseif (!$dir->permissions()->write()) {
            throw new StorageException('Sessions directory is not writable');
        }

        $this->dir = $dir;
    }

    /**
     * @param string $id
     * @return int
     * @throws StorageException
     */
    public function lastModified(string $id): int
    {
        try {
            return $this->dir->file($id . ".sess", false)->timestamps()->modified();
        } catch (PathNotExistException $e) {
            throw new StorageException(sprintf('Session file "%s" does not exist', $id));
        } catch (FilesystemException $e) {
            throw new StorageException(sprintf('Failed to check session file "%s"', $id));
        }
    }

    /**
     * @param string $id
     * @return string
     * @throws StorageException
     */
    public function read(string $id): string
    {
        try {
            return $this->dir->file($id . ".sess")->read();
        } catch (PathNotExistException $e) {
            throw new StorageException(sprintf('Session "%s" not found in directory', $id));
        } catch (PathPermissionException $e) {
            throw new StorageException(sprintf('Session file "%s" is not readable', $id));
        } catch (FilesystemException $e) {
            throw new StorageException(sprintf('Failed to read session file "%s"', $id));
        }
    }

    /**
     * @param string $id
     * @param string $serializedSession
     * @return bool
     * @throws StorageException
     */
    public function write(string $id, string $serializedSession): bool
    {
        try {
            return $this->dir->write($id . ".sess", $serializedSession, false, true) ? true : false;
        } catch (PathPermissionException $e) {
            throw new StorageException(sprintf('Session "%s" directory is not writable', $id));
        } catch (FilesystemException $e) {
            throw new StorageException(sprintf('Failed to write session "%s" file', $id));
        }
    }

    /**
     * @param string $id
     * @throws StorageException
     */
    public function delete(string $id): void
    {
        try {
            $this->dir->delete($id . ".sess");
        } catch (FilesystemException $e) {
            throw new StorageException(sprintf('Failed to delete session file "%s"', $id));
        }
    }

    /**
     * @return array
     * @throws StorageException
     */
    public function list(): array
    {
        try {
            return $this->dir->glob("*.sess");
        } catch (PathPermissionException $e) {
            throw new StorageException('Cannot retrieve sessions list; Directory is not readable');
        } catch (FilesystemException $e) {
            throw new StorageException('Failed to retrieve sessions list from directory');
        }
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->dir->has($id . ".sess") ? true : false;
    }

    /**
     * @throws StorageException
     */
    public function flush(): void
    {
        try {
            $this->dir->flush(false);
        } catch (FilesystemException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            throw new StorageException('Failed to delete one or more session files');
        }
    }
}