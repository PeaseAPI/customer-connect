<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Deactive = 'deactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Deactive => 'Inactive',
        };
    }
}
