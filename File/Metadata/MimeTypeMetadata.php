<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File\Metadata;

use Liip\ImagineBundle\Exception\InvalidArgumentException;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
class MimeTypeMetadata
{
    /**
     * @var string
     */
    private const PREFIX_UNREGISTERED = 'x';

    /**
     * @var string
     */
    private const PREFIX_VENDOR = 'vnd';

    /**
     * @var string
     */
    private const PREFIX_PERSONAL = 'prs';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $subType;

    /**
     * @var string|null
     */
    private $prefix;

    /**
     * @var string|null
     */
    private $deliminator;

    /**
     * @var string|null
     */
    private $suffix;

    /**
     * @param string|null $type
     * @param string|null $subType
     * @param string|null $prefix
     * @param string|null $suffix
     * @param string|null $deliminator
     */
    public function __construct(string $type = null, string $subType = null, string $prefix = null, string $suffix = null, string $deliminator = null)
    {
        $this->type = self::sanitize($type);
        $this->subType = self::sanitize($subType);
        $this->prefix = self::sanitizePrefix($prefix);
        $this->suffix = self::sanitize($suffix);
        $this->deliminator = self::sanitizeDeliminator($deliminator);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getMimeType() ?: '';
    }

    /**
     * @param string|null $string
     *
     * @return self
     */
    public static function create(string $string = null): self
    {
        if (null !== $sections = self::getMimeTypeParts($string)) {
            return new self(...$sections);
        }

        return new self();
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->hasType() && $this->hasSubType() ? vsprintf('%s/%s%s%s%s', [
            $this->getType(),
            $this->hasPrefix() ? $this->getPrefix() : '',
            $this->hasPrefix() && $this->hasDeliminator() ? $this->getDeliminator() : '',
            $this->getSubType(),
            $this->hasSuffix() ? sprintf('+%s', $this->getSuffix()) : '',
        ]) : null;
    }

    /**
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function hasType(): bool
    {
        return null !== $this->type;
    }

    /**
     * @param string|null $type
     *
     * @return bool
     */
    public function isType(string $type = null): bool
    {
        return $type === $this->type;
    }

    /**
     * @return null|string
     */
    public function getSubType(): ?string
    {
        return $this->subType;
    }

    /**
     * @return bool
     */
    public function hasSubType(): bool
    {
        return null !== $this->subType;
    }

    /**
     * @param string|null $subType
     *
     * @return bool
     */
    public function isSubType(string $subType = null): bool
    {
        return $subType === $this->subType;
    }

    /**
     * @return null|string
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @return bool
     */
    public function hasPrefix(): bool
    {
        return null !== $this->prefix;
    }

    /**
     * @param string|null $prefix
     *
     * @return bool
     */
    public function isPrefix(string $prefix = null): bool
    {
        return $prefix === $this->prefix;
    }

    /**
     * @return null|string
     */
    public function getDeliminator(): ?string
    {
        return $this->deliminator;
    }

    /**
     * @return bool
     */
    public function hasDeliminator(): bool
    {
        return null !== $this->deliminator;
    }

    /**
     * @param string|null $deliminator
     *
     * @return bool
     */
    public function isDeliminator(string $deliminator = null): bool
    {
        return $deliminator === $this->deliminator;
    }

    /**
     * @return null|string
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @return bool
     */
    public function hasSuffix(): bool
    {
        return null !== $this->suffix;
    }

    /**
     * @param string|null $suffix
     *
     * @return bool
     */
    public function isSuffix(string $suffix = null): bool
    {
        return $suffix === $this->suffix;
    }

    /**
     * @return bool
     */
    public function isPrefixStandard(): bool
    {
        return false === $this->isPrefixUnregistered()
            && false === $this->isPrefixVendor()
            && false === $this->isPrefixPersonal();
    }

    /**
     * @return bool
     */
    public function isPrefixUnregistered(): bool
    {
        return $this->isPrefix(self::PREFIX_UNREGISTERED);
    }

    /**
     * @return bool
     */
    public function isPrefixVendor(): bool
    {
        return $this->isPrefix(self::PREFIX_VENDOR);
    }

    /**
     * @return bool
     */
    public function isPrefixPersonal(): bool
    {
        return $this->isPrefix(self::PREFIX_PERSONAL);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->hasType() && $this->hasSubType() && self::isValidMimeType($this);
    }

    /**
     * @param string|null $type
     * @param string|null $subType
     * @param string|null $prefix
     * @param string|null $suffix
     *
     * @return bool
     */
    public function isMatch(string $type = null, string $subType = null, string $prefix = null, string $suffix = null): bool
    {
        return $this->isType($type ?: $this->type)
            && $this->isSubType($subType ?: $this->subType)
            && $this->isPrefix($prefix ?: $this->prefix)
            && $this->isSuffix($suffix ?: $this->suffix);
    }

    /**
     * @param string $string
     *
     * @return array|null
     */
    public static function getMimeTypeParts(string $string = null): ?array
    {
        $matched = 1 === preg_match(
            '{^(?<type>[^/]+)/((?<prefix>vnd|prs|x)(?<deliminator>\.|\-))?(?<sub_type>[^+]+?)(\+(?<suffix>.+))?$}',
            $string, $sections
        );

        $n = function (string $index) use ($sections): ?string {
            return empty($sections[$index]) ? null : $sections[$index];
        };

        return $matched ? [
            self::sanitize($sections['type']),
            self::sanitize($sections['sub_type']),
            self::sanitizePrefix($n('prefix')),
            self::sanitize($n('suffix')),
            self::sanitizeDeliminator($n('deliminator')),
        ] : null;
    }

    /**
     * @param string|null $string
     *
     * @return bool
     */
    public static function isValidMimeType(string $string = null): bool
    {
        return !empty($string) && null !== self::getMimeTypeParts($string);
    }

    /**
     * @param string|null $prefix
     *
     * @return null|string
     */
    private static function sanitizePrefix(string $prefix = null): ?string
    {
        if (null === $prefix || in_array($prefix, [self::PREFIX_UNREGISTERED, self::PREFIX_VENDOR, self::PREFIX_PERSONAL])) {
            return self::sanitize($prefix);
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid mime type prefix "%s" provided (accepted values are "%s", "%s", and "%s").', $prefix,
            self::PREFIX_UNREGISTERED, self::PREFIX_VENDOR, self::PREFIX_PERSONAL
        ));
    }

    /**
     * @param string|null $deliminator
     *
     * @return null|string
     */
    private static function sanitizeDeliminator(string $deliminator = null): ?string
    {
        if (null === $deliminator || in_array($deliminator, ['.', '-'])) {
            return self::sanitize($deliminator);
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid mime type deliminator "%s" provided (accepted values are "." and "-").', $deliminator
        ));
    }

    /**
     * @param string|null $string
     *
     * @return null|string
     */
    private static function sanitize(string $string = null): ?string
    {
        if (null !== $string && 1 === preg_match('{(?<characters>[^a-z0-9\.-]+)}i', $string, $matches)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid mime type character(s) "%s" provided in "%s" (accepted values are "[a-z0-9\.-]").', $matches['characters'], $string
            ));
        }

        return $string;
    }
}
