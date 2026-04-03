<?php

namespace App\Enums;

enum StatusEnum: int
    {
        case ACTIVE = 1;
        case INACTIVE = 0;

        public function label(): string
            {
                return match($this) {
                    self::ACTIVE => 'Active',
                    self::INACTIVE => 'Inactive',
                    };
            }

        public function color(): string
            {
                return match($this) {
                    self::ACTIVE => 'success',
                    self::INACTIVE => 'danger',
                    };
            }

        public static function options(): array
            {
                return collect(self::cases())
                    ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                    ->toArray();
            }

        public static function values(): array
            {
                return array_column(self::cases(), 'value');
            }
    }
