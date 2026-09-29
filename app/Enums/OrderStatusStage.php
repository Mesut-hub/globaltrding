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
        return __('portal.stages.' . $this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}