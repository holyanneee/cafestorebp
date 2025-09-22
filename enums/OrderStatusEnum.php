<?php
namespace Enums;

enum OrderStatusEnum: string
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case PickUp = 'pick-up';
    case OnTheWay = 'on the way';
    case Received = 'received';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Preparing => 'Preparing',
            self::PickUp => 'Pick-Up',
            self::OnTheWay => 'On The Way',
            self::Received => 'Received',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => '#FF0000', // red
            self::Preparing => '#FFFF00', // yellow
            self::PickUp => '#FFA500', // orange
            self::OnTheWay => '#0000FF', // blue
            self::Received => '#808080', // gray
            self::Completed => '#008000', // green
        };
    }
}