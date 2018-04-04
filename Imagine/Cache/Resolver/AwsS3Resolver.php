<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Aws\CommandInterface;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class AwsS3Resolver implements ResolverInterface
{
    use LoggerAwareTrait;

    /**
     * @var S3Client
     */
    protected $storage;

    /**
     * @var string
     */
    protected $bucket;

    /**
     * @var string
     */
    protected $acl;

    /**
     * @var array
     */
    protected $getOptions;

    /**
     * @var array
     */
    protected $putOptions;

    /**
     * @var array
     */
    protected $delOptions;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $cachePrefix;

    /**
     * @param S3Client    $storage     The Amazon S3 storage API. It's required to know authentication information
     * @param string      $bucket      The bucket name to operate on
     * @param string|null $acl         The ACL to use when storing new objects. Default: owner read/write, public read
     * @param array       $getOptions  A list of options to be passed when retrieving an object url from Amazon S3
     * @param array       $putOptions  A list of options to be passed when saving an object to Amazon S3
     * @param array       $delOptions  A list of options to be passed when removing an object from Amazon S3
     * @param string|null $cachePrefix A cache prefix string
     */
    public function __construct(
        S3Client $storage,
        string $bucket,
        string $acl = null,
        array $getOptions = [],
        array $putOptions = [],
        array $delOptions = [],
        string $cachePrefix = null
    ) {
        $this->storage = $storage;
        $this->bucket = $bucket;
        $this->acl = $acl ?: 'public-read';
        $this->getOptions = $getOptions;
        $this->putOptions = $putOptions;
        $this->delOptions = $delOptions;
        $this->cachePrefix = $cachePrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function isStored(string $path, string $filter): bool
    {
        return $this->objectExists($this->getObjectPath($path, $filter));
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $path, string $filter): string
    {
        return $this->getObjectUrl($this->getObjectPath($path, $filter));
    }

    /**
     * {@inheritdoc}
     */
    public function store(FileInterface $file, string $path, string $filter): void
    {
        $this->putObjectPath($this->getObjectPath($path, $filter), $file);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $filters): void
    {
        if (empty($paths) && !empty($filters)) {
            $this->delMatchingObjectPaths($filters);
        } else {
            foreach ($filters as $f) {
                foreach ($paths as $p) {
                    $this->delObjectPath($this->getObjectPath($p, $f));
                }
            }
        }
    }

    /**
     * Returns the object path within the bucket.
     *
     * @param string $path   The base path of the resource
     * @param string $filter The name of the imagine filter in effect
     *
     * @return string The path of the object on S3
     */
    protected function getObjectPath($path, $filter)
    {
        $path = $this->cachePrefix
            ? sprintf('%s/%s/%s', $this->cachePrefix, $filter, $path)
            : sprintf('%s/%s', $filter, $path);

        return str_replace('//', '/', $path);
    }

    /**
     * Returns the URL for an object saved on Amazon S3.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getObjectUrl($path)
    {
        $command = $this->storage->getCommand('GetObject', array_merge($this->getOptions, [
            'Bucket' => $this->bucket,
            'Key' => $path,
        ]));

        return (string) (
            $command instanceof CommandInterface ? \Aws\serialize($command)->getUri() : $command
        );
    }

    /**
     * @param array $filters
     *
     * @return string
     */
    protected function getObjectSearchFilters(array $filters)
    {
        return vsprintf('/%s(%s)/i', [
            $this->cachePrefix ? preg_quote(sprintf('%s/', $this->cachePrefix), '/') : '',
            implode('|', array_map(function (string $f): string {
                return preg_quote($f, '/');
            }, $filters)),
        ]);
    }

    /**
     * Checks whether an object exists.
     *
     * @param string $objectPath
     *
     * @return bool
     */
    protected function objectExists($objectPath)
    {
        return $this->storage->doesObjectExist($this->bucket, $objectPath);
    }

    /**
     * @param string        $path
     * @param FileInterface $file
     *
     * @throws S3Exception
     */
    private function putObjectPath(string $path, FileInterface $file): void
    {
        try {
            $this->storage->putObject(array_merge($this->putOptions, [
                'ACL' => $this->acl,
                'Bucket' => $this->bucket,
                'Key' => $path,
                'Body' => $file->getContents(),
                'ContentType' => (string) $file->getContentType(),
            ]));
        } catch (S3Exception $exception) {
            $this->logger->error('The object "%path%" could not be created on AWS S3 bucket "%bucket%".', [
                'path' => $path,
                'bucket' => $this->bucket,
                'exception' => $exception,
            ]);

            throw new NotStorableException(
                'The object "%s" could not be created on AWS S3 bucket "%s".', $path, $this->bucket, $exception
            );
        }
    }

    /**
     * @param string $object
     */
    private function delObjectPath(string $object): void
    {
        if (!$this->objectExists($object)) {
            return;
        }

        try {
            $this->storage->deleteObject(array_merge($this->delOptions, [
                'Bucket' => $this->bucket,
                'Key' => $object,
            ]));
        } catch (S3Exception $exception) {
            $this->logger->error('The object "%path%" could not be deleted from AWS S3 bucket "%bucket%".', [
                'path' => $object,
                'bucket' => $this->bucket,
                'exception' => $exception,
            ]);
        }
    }

    /**
     * @param array $filters
     */
    private function delMatchingObjectPaths(array $filters): void
    {
        try {
            $this->storage->deleteMatchingObjects($this->bucket, null, $this->getObjectSearchFilters($filters));
        } catch (S3Exception $exception) {
            $this->logger->error('The objects matching "%regex%" could not be deleted from AWS S3 bucket "%bucket%".', [
                'regex' => $this->getObjectSearchFilters($filters),
                'bucket' => $this->bucket,
                'exception' => $exception,
            ]);
        }
    }
}
