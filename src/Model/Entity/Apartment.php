<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Apartment Entity
 *
 * @property int $id
 * @property string $address
 * @property float $rent
 * @property float $size
 * @property int $nb_rooms
 * @property int $nb_bathrooms
 * @property bool $booked
 * @property string $energy_class
 * @property string|null $climat_class
 * @property string|null $description
 * @property int|null $landlord_id
 * @property \Cake\I18n\DateTime|null $created_at
 * @property \Cake\I18n\DateTime|null $updated_at
 * 
 * @property \App\Model\Entity\Landlord|null $landlord
 * @property \App\Model\Entity\Lease[] $leases
 * @property \App\Model\Entity\MaintenanceRequest[] $maintenance_requests
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
        'address' => true,
        'rent' => true,
        'size' => true,
        'nb_rooms' => true,
        'nb_bathrooms' => true,
        'booked' => true,
        'energy_class' => true,
        'climat_class' => true,
        'description' => true,
        'landlord_id' => true,
        'created_at' => true,
        'updated_at' => true,
        'landlord' => true,
        'leases' => true,
        'maintenance_requests' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Virtual fields that should be set to JSON output
     *
     * @var array<string>
     */
    protected array $_virtual = [];

    /**
     * Convert entity to array for JSON serialization
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        
        // Remove landlord_id if null
        if (isset($array['landlord_id']) && $array['landlord_id'] === null) {
            unset($array['landlord_id']);
        }
        
        // Remove climat_class if null
        if (isset($array['climat_class']) && $array['climat_class'] === null) {
            unset($array['climat_class']);
        }
        
        return $array;
    }

    /**
     * Getter for rent - ensure it's returned as float
     *
     * @return float|null
     */
    protected function _getRent()
    {
        $value = $this->_fields['rent'] ?? null;
        return $value !== null ? (float)$value : null;
    }

    /**
     * Getter for size - ensure it's returned as float
     *
     * @return float|null
     */
    protected function _getSize()
    {
        $value = $this->_fields['size'] ?? null;
        return $value !== null ? (float)$value : null;
    }
}
