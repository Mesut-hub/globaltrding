<?php

namespace App\Enums;

enum OrderStatusStage: string
{
    case LOAD_ACCEPTED      = 'load_accepted';
    case LOAD_EXITED_DEPOT  = 'load_exited_depot';
    case LOADING_VESSEL     = 'loading_vessel';
    case VESSEL_DEPARTED    = 'vessel_departed';
    case VESSEL_IN_TRANSIT  = 'vessel_in_transit';
    case VESSEL_ARRIVED     = 'vessel_arrived';
    case OFFLOADING         = 'offloading';
    case OFFLOADED_TO_YARD  = 'offloaded_to_yard';
    case LOADING_VEHICLE    = 'loading_vehicle';
    case VEHICLE_IN_TRANSIT = 'vehicle_in_transit';
    case VEHICLE_ARRIVED    = 'vehicle_arrived';
    case DELIVERED          = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::LOAD_ACCEPTED      => 'Load accepted',
            self::LOAD_EXITED_DEPOT  => 'Load exited the depot',
            self::LOADING_VESSEL     => 'Loading is in process',
            self::VESSEL_DEPARTED    => 'Vessel left the port',
            self::VESSEL_IN_TRANSIT  => 'Vessel is on the way to the destination',
            self::VESSEL_ARRIVED     => 'Vessel arrived at the port',
            self::OFFLOADING         => 'Offloading is in process',
            self::OFFLOADED_TO_YARD  => 'Load offloaded to the yard',
            self::LOADING_VEHICLE    => 'Loading to the vehicle is in process',
            self::VEHICLE_IN_TRANSIT => 'Vehicle is on the way to the delivery point',
            self::VEHICLE_ARRIVED    => 'Vehicle arrived at the delivery point',
            self::DELIVERED          => 'Cargo is delivered successfully',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}