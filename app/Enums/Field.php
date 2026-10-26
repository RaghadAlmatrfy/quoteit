<?php

namespace App\Enums;

/**
 * The computing fields (specializations). Every quote belongs to one field,
 * and every field has its own community and its own color.
 */
enum Field: string
{
    // Tech in general: a community for everyone who is interested in technology
    case General = 'general';
    case Ai = 'ai';
    case Data = 'data';
    case Cloud = 'cloud';
    case Security = 'security';
    case Software = 'software';
    case ComputerScience = 'cs';
    case Hci = 'hci';

    /** Translated name, e.g. "Artificial Intelligence" / "الذكاء الاصطناعي". */
    public function label(): string
    {
        return __(match ($this) {
            self::General => 'General Tech',
            self::Ai => 'Artificial Intelligence',
            self::Data => 'Data Science',
            self::Cloud => 'Cloud Computing',
            self::Security => 'Cybersecurity',
            self::Software => 'Software Engineering',
            self::ComputerScience => 'Computer Science',
            self::Hci => 'Human-Computer Interaction',
        });
    }

    /** CSS color name defined in resources/css/app.css (@theme). */
    public function color(): string
    {
        return match ($this) {
            self::General => 'tuwaiq-teal',
            self::Ai => 'midnight-orchid',
            self::Data => 'dusky-lilac',
            self::Cloud => 'iris-mist',
            self::Security => 'plum-blossom',
            self::Software => 'silver-wisteria',
            self::ComputerScience => 'royal-ink',
            self::Hci => 'blush-rose',
        };
    }

    /** True when the field color is dark, so the text on it must be white. */
    public function isDark(): bool
    {
        return in_array($this, [self::Ai, self::Data, self::ComputerScience], true);
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
