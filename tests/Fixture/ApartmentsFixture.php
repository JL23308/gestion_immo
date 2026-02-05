<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ApartmentsFixture
 */
class ApartmentsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'rent' => 500,
                'address' => '123 Test Street',
                'booked' => 0,
                'energy_class' => 'A',
                'nb_rooms' => 2,
                'nb_bathrooms' => 1,
                'size' => 50,
                'climat_class' => 'B',
                'createdAt' => '2025-01-01 10:00:00',
                'updatedAt' => '2025-01-01 10:00:00',
                'landlord_id' => 1,
            ],
        ];
        parent::init();
    }
}
