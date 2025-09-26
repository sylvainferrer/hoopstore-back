<?php

namespace App\Enum;

enum Size: string
{   
    case NO_SIZE  = 'no-size';

    case TU  = 'TU';
    case XXS = 'XXS';
    case XS  = 'XS';
    case S   = 'S';
    case M   = 'M';
    case L   = 'L';
    case XL  = 'XL';
    case XXL = 'XXL';
    case XXXL = 'XXXL';

    case SIZE_24 = '24';
    case SIZE_25 = '25';
    case SIZE_26 = '26';
    case SIZE_27 = '27';
    case SIZE_28 = '28';
    case SIZE_29 = '29';
    case SIZE_30 = '30';
    case SIZE_31 = '31';
    case SIZE_32 = '32';
    case SIZE_33 = '33';
    case SIZE_34 = '34';
    case SIZE_35 = '35';
    case SIZE_36 = '36';
    case SIZE_37 = '37';
    case SIZE_38 = '38';
    case SIZE_39 = '39';
    case SIZE_40 = '40';
    case SIZE_41 = '41';
    case SIZE_42 = '42';
    case SIZE_43 = '43';
    case SIZE_44 = '44';
    case SIZE_45 = '45';
    case SIZE_46 = '46';
    case SIZE_47 = '47';
    case SIZE_48 = '48';
    case SIZE_49 = '49';
    case SIZE_50 = '50';

    public static function clothingCases(): array
    {
        return [
            self::TU,
            self::XXS,
            self::XS,
            self::S,
            self::M,
            self::L,
            self::XL,
            self::XXL,
            self::XXXL,
        ];
    }

    public static function shoesCases(): array
    {
        return [
            self::SIZE_24,
            self::SIZE_25,
            self::SIZE_26,
            self::SIZE_27,
            self::SIZE_28,
            self::SIZE_29,
            self::SIZE_30,
            self::SIZE_31,
            self::SIZE_32,
            self::SIZE_33,
            self::SIZE_34,
            self::SIZE_35,
            self::SIZE_36,
            self::SIZE_37,
            self::SIZE_38,
            self::SIZE_39,
            self::SIZE_40,
            self::SIZE_41,
            self::SIZE_42,
            self::SIZE_43,
            self::SIZE_44,
            self::SIZE_45,
            self::SIZE_46,
            self::SIZE_47,
            self::SIZE_48,
            self::SIZE_49,
            self::SIZE_50,
        ];
    }
}
