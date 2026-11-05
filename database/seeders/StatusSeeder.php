<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Awaiting',
                'value' => 0,
                'badge_class' => 'bg-warning text-dark',
                'badge_label' => 'Awaiting',
                'description' => 'Waybill is awaiting allocation',
            ],
            [
                'name' => 'Allocated',
                'value' => 1,
                'badge_class' => 'bg-info text-white',
                'badge_label' => 'Allocated',
                'description' => 'Waybill has been allocated to a driver',
            ],
            [
                'name' => 'In Transit',
                'value' => 2,
                'badge_class' => 'bg-primary',
                'badge_label' => 'In Transit',
                'description' => 'Waybill is in transit to destination',
            ],
            [
                'name' => 'Return Transit',
                'value' => 5,
                'badge_class' => 'bg-secondary text-white',
                'badge_label' => 'Return Transit',
                'description' => 'Waybill is in return transit',
            ],
            [
                'name' => 'Delivered',
                'value' => 3,
                'badge_class' => 'bg-success',
                'badge_label' => 'Delivered',
                'description' => 'Waybill has been delivered',
            ],
            [
                'name' => 'Completed',
                'value' => 4,
                'badge_class' => 'bg-secondary',
                'badge_label' => 'Completed',
                'description' => 'Waybill trip has been successfully completed',
            ],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(
                ['value' => $status['value']],
                $status
            );
        }
    }
}
