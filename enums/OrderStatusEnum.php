<?php
namespace Enums;

enum OrderStatusEnum: string
{
    case Received = 'received';
    case Preparing = 'preparing';
    case PickUp = 'pick-up';
    case OnTheWay = 'on the way';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Received => 'Received',
            self::Preparing => 'Preparing',
            self::PickUp => 'Pick-Up',
            self::OnTheWay => 'On The Way',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Received => 'gray',
            self::Preparing => 'yellow',
            self::PickUp => 'orange',
            self::OnTheWay => 'blue',
            self::Completed => 'green',
        };
    }
}