<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
     * Apartment Entity
 *
 * @property int $id
 * @property float $rent
 * @property string $address
 * @property int $booked
 * @property string $energyClass
 * @property int $nbRooms
 * @property int $nbBathrooms
 * @property float $size
 * @property string|null $climatClass
 * @property string|null $createdAt
 * @property string|null $updatedAt
 */
class Apartment extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'rent' => true,
        'address' => true,
        'booked' => true,
        'energyClass' => true,
        'nbRooms' => true,
        'nbBathrooms' => true,
        'size' => true,
        'climatClass' => true,
        'createdAt' => true,
        'updatedAt' => true,
    ];
}
