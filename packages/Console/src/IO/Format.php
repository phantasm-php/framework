<?php

namespace Phantasm\Console\IO;

enum Format: int
{
    case RESET = 0;
    case BOLD = 1;
    case DIM = 2;
    case ITALIC = 3;
    case UNDERLINE = 4;
    case BLINK = 5;
    case FAST_BLINK = 6;
    case REVERSE = 7;
    case HIDDEN = 8;
    case STRIKETHROUGH = 9;

    case BLACK = 30;
    case RED = 31;
    case GREEN = 32;
    case YELLOW = 33;
    case BLUE = 34;
    case MAGENTA = 35;
    case CYAN = 36;
    case WHITE = 37;

    case BG_BLACK = 40;
    case BG_RED = 41;
    case BG_GREEN = 42;
    case BG_YELLOW = 43;
    case BG_BLUE = 44;
    case BG_MAGENTA = 45;
    case BG_CYAN = 46;
    case BG_WHITE = 47;

    case BRIGHT_BLACK = 90;
    case BRIGHT_RED = 91;
    case BRIGHT_GREEN = 92;
    case BRIGHT_YELLOW = 93;
    case BRIGHT_BLUE = 94;
    case BRIGHT_MAGENTA = 95;
    case BRIGHT_CYAN = 96;
    case BRIGHT_WHITE = 97;

    case BG_BRIGHT_BLACK = 100;
    case BG_BRIGHT_RED = 101;
    case BG_BRIGHT_GREEN = 102;
    case BG_BRIGHT_YELLOW = 103;
    case BG_BRIGHT_BLUE = 104;
    case BG_BRIGHT_MAGENTA = 105;
    case BG_BRIGHT_CYAN = 106;
    case BG_BRIGHT_WHITE = 107;

    public static function color(int $r, null|int $g = null, null|int $b = null): string
    {
        if (!is_null($g) && !is_null($b)) {
            return "38;2;{$r};{$g};{$b}";
        }

        return "38;5;{$r}";
    }

    public static function bg(int $r, null|int $g = null, null|int $b = null): string
    {
        if (!is_null($g) && !is_null($b)) {
            return "48;2;{$r};{$g};{$b}";
        }

        return "48;5;{$r}";
    }

    public static function apply(string $text, Format|int|string ...$formats): string
    {
        if (empty($formats)) {
            return $text;
        }

        $formats = array_map(static fn(Format|int|string $format) => $format instanceof Format
            ? $format->value
            : (string) $format, $formats);

        return sprintf("\033[%sm%s\033[0m", implode(';', $formats), $text);
    }
}
