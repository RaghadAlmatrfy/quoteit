<?php

namespace App\Support;

/**
 * The options for how a quote looks: font, size, bold, italic, alignment, background.
 */
class QuoteStyle
{
    /** @var array<string, array{label: string, family: string}> */
    public const Fonts = [
        'default' => ['label' => 'Default (Amiri)', 'family' => "'Amiri', serif"],
        'tajawal' => ['label' => 'Tajawal', 'family' => "'Tajawal', sans-serif"],
        'cairo' => ['label' => 'Cairo', 'family' => "'Cairo', sans-serif"],
        'amiri' => ['label' => 'Amiri', 'family' => "'Amiri', serif"],
        'naskh' => ['label' => 'Noto Naskh Arabic', 'family' => "'Noto Naskh Arabic', serif"],
        'kufi' => ['label' => 'Reem Kufi', 'family' => "'Reem Kufi', sans-serif"],
        'georgia' => ['label' => 'Georgia', 'family' => 'Georgia, serif'],
        'mono' => ['label' => 'Monospace', 'family' => "'Courier New', ui-monospace, monospace"],
    ];

    /** @var array<string, array{label: string, class: string}> */
    public const Sizes = [
        'sm' => ['label' => 'Small', 'class' => 'text-base'],
        'md' => ['label' => 'Medium', 'class' => 'text-lg'],
        'lg' => ['label' => 'Large', 'class' => 'text-xl'],
        'xl' => ['label' => 'Extra large', 'class' => 'text-2xl'],
        'xxl' => ['label' => 'Huge', 'class' => 'text-3xl'],
    ];

    /** @var array<int, string> */
    public const Aligns = ['start', 'right', 'center', 'left'];

    /** Ready-made background colors (the site palettes). @var array<int, string> */
    public const Backgrounds = [
        '#432a80', '#2c2133', '#796989', '#7f626d', '#b69d98',
        '#6f3dbf', '#ae96f2', '#ebe0ff', '#f2a54a', '#4fc9b0', '#ffffff',
    ];

    /** Varied card colors (both site palettes) for quotes without a chosen background. @var array<int, string> */
    public const CardColors = [
        '#432a80', '#ffffff', '#796989', '#ebe0ff', '#6f3dbf', '#f3ece9',
        '#2c2133', '#d9ccff', '#7f626d', '#e8f6f2', '#5324a6', '#fdf1e2',
    ];

    /** Each quote always gets the same color, but the feed looks varied. */
    public static function cardColorFor(int $id): string
    {
        return self::CardColors[$id % count(self::CardColors)];
    }

    public static function family(?string $font): ?string
    {
        return $font && $font !== 'default' ? (self::Fonts[$font]['family'] ?? null) : null;
    }

    public static function sizeClass(?string $size): string
    {
        return self::Sizes[$size ?? 'md']['class'] ?? 'text-lg';
    }

    /** White text on dark backgrounds, dark text on light ones. */
    public static function textColorFor(string $hex): string
    {
        [$red, $green, $blue] = sscanf($hex, '#%02x%02x%02x');
        $luminance = (0.299 * $red + 0.587 * $green + 0.114 * $blue) / 255;

        return $luminance > 0.6 ? '#432a80' : '#ffffff';
    }
}
