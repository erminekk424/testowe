<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductType: string implements HasColor, HasLabel
{
    case Account = 'account';
    case Currency = 'currency';
    case CustomItem = 'custom_item';
    //    case RequestedBoosting = "requested_boosting";
    //    case TopUp = "top_up";
    //    case GiftCard = "gift_card";

    public function getLabel(): string
    {
        return match ($this) {
            self::Account => __('Account'),
            self::Currency => __('Currency'),
            self::CustomItem => __('Custom Item'),
            //            self::RequestedBoosting => __('Requested Boosting'),
            //            self::TopUp => __('Top Up'),
            //            self::GiftCard => __('GiftCard'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Account => 'danger',
            self::Currency => 'gray',
            self::CustomItem => 'info',
            //            self::RequestedBoosting => 'primary',
            //            self::TopUp => 'success',
            //            self::GiftCard => 'warning',
        };
    }
}
