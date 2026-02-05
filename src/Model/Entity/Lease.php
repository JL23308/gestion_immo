<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Lease Entity
 *
 * @property int $id
 * @property int $apartment_id
 * @property int $user_id
 * @property \Cake\I18n\Date $start_date
 * @property \Cake\I18n\Date|null $end_date
 * @property float $monthly_rent
 * @property float $deposit
 * @property string $status
 * @property string|null $notes
 * @property \Cake\I18n\DateTime|null $created_at
 * @property \Cake\I18n\DateTime|null $updated_at
 *
 * @property \App\Model\Entity\Apartment $apartment
 * @property \App\Model\Entity\User $user
 */
class Lease extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'apartment_id' => true,
        'user_id' => true,
        'start_date' => true,
        'end_date' => true,
        'monthly_rent' => true,
        'deposit' => true,
        'status' => true,
        'notes' => true,
        'created_at' => true,
        'updated_at' => true,
        'apartment' => true,
        'user' => true,
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
     * Virtual field getter for monthly_rent to ensure it's returned as float
     */
    protected function _getMonthlyRent($value): ?float
    {
        return $value !== null ? (float)$value : null;
    }

    /**
     * Virtual field getter for deposit to ensure it's returned as float
     */
    protected function _getDeposit($value): ?float
    {
        return $value !== null ? (float)$value : null;
    }

    /**
     * Override toArray to remove null values from nested associations
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        
        // Remove null climat_class and landlord_id from apartment if present
        if (isset($array['apartment'])) {
            if (isset($array['apartment']['climat_class']) && $array['apartment']['climat_class'] === null) {
                unset($array['apartment']['climat_class']);
            }
            if (isset($array['apartment']['landlord_id']) && $array['apartment']['landlord_id'] === null) {
                unset($array['apartment']['landlord_id']);
            }
        }
        
        return $array;
    }
}
