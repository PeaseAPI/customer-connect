<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Deactive = 'deactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => '在职',
            self::Deactive => '离职',
        };
    }
}
