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

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class ContentTypeMetadata implements MetadataInterface
{
    /**
     * @var string
     */
    public const PREFIX_UNREGISTERED = 'x';

    /**
     * @var string
     */
    public const PREFIX_VENDOR = 'vnd';

    /**
     * @var string
     */
    public const PREFIX_PERSONAL = 'prs';

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
    private $prefixDeliminator;

    /**
     * @var string|null
     */
    private $suffix;

    /**
     * @param string|null $type
     * @param string|null $subType
     * @param string|null $prefix
     * @param string|null $suffix
     * @param string|null $prefixDeliminator
     */
    public function __construct(string $type = null, string $subType = null, string $prefix = null, string $suffix = null, string $prefixDeliminator = null)
    {
        $this->type = $type;
        $this->subType = $subType;
        $this->prefix = in_array($prefix, [
            self::PREFIX_UNREGISTERED,
            self::PREFIX_VENDOR,
            self::PREFIX_PERSONAL,
        ]) ? $prefix : null;
        $this->suffix = $suffix;
        $this->prefixDeliminator = $prefixDeliminator ?: null;
    }

    /**
     * @param string|null $string
     *
     * @return self
     */
    public static function create(string $string = null): self
    {
        if (null !== $sections = self::explodeContentType($string)) {
            return new self(...$sections);
        }

        return new self();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return false === $this->hasType() && false === $this->hasSubType() ? '' : vsprintf('%s/%s%s%s%s', [
            $this->type(),
            $this->hasPrefix() ? $this->prefix() : '',
            $this->hasPrefixDeliminator() ? $this->prefixDeliminator() : '',
            $this->subType(),
            $this->hasSuffix() ? sprintf('+%s', $this->suffix()) : '',
        ]);
    }

    /**
     * @return null|string
     */
    public function type(): ?string
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
    public function subType(): ?string
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
    public function prefix(): ?string
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
    public function prefixDeliminator(): ?string
    {
        return $this->prefixDeliminator;
    }

    /**
     * @return bool
     */
    public function hasPrefixDeliminator(): bool
    {
        return null !== $this->prefixDeliminator;
    }

    /**
     * @param string|null $prefixDeliminator
     *
     * @return bool
     */
    public function isPrefixDeliminator(string $prefixDeliminator = null): bool
    {
        return $prefixDeliminator === $this->prefixDeliminator;
    }

    /**
     * @return null|string
     */
    public function suffix(): ?string
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
        return $this->hasType()
            && $this->hasSubType()
            && self::isValidContentType($this);
    }

    /**
     * @param string|null $type
     * @param string|null $subType
     * @param string|null $prefix
     * @param string|null $suffix
     *
     * @return bool
     */
    public function isEquivalent(string $type = null, string $subType = null, string $prefix = null, string $suffix = null): bool
    {
        return $this->isSame(
            $type,
            $subType ?: $this->subType,
            $prefix ?: $this->prefix,
            $suffix ?: $this->suffix
        );
    }

    /**
     * @param string|null $type
     * @param string|null $subType
     * @param string|null $prefix
     * @param string|null $suffix
     *
     * @return bool
     */
    public function isSame(string $type = null, string $subType = null, string $prefix = null, string $suffix = null): bool
    {
        return $this->isType($type)
            && $this->isSubType($subType)
            && $this->isPrefix($prefix)
            && $this->isSuffix($suffix);
    }

    /**
     * @param string $contentType
     *
     * @return array|null
     */
    public static function explodeContentType(string $contentType = null): ?array
    {
        $valid = 1 === preg_match(
            '{^(?<type>[^/]+)/((?<prefix>vnd|prs|x)(?<deliminator>\.|\-))?(?<sub_type>[^+]+?)(\+(?<suffix>.+))?$}',
            $contentType, $sections
        );

        $sanitize = function (string $index) use ($sections): ?string {
            return empty($sections[$index]) ? null : $sections[$index];
        };

        return false === $valid ? null : [
            $sections['type'],
            $sections['sub_type'],
            $sanitize('prefix'),
            $sanitize('suffix'),
            $sanitize('deliminator'),
        ];
    }

    /**
     * @param string|null $contentType
     *
     * @return bool
     */
    public static function isValidContentType(string $contentType = null): bool
    {
        return true !== empty($contentType)
            && null !== self::explodeContentType($contentType);
    }
}
