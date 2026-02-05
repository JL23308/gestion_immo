<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Apartments Model
 *
 * @method \App\Model\Entity\Apartment newEmptyEntity()
 * @method \App\Model\Entity\Apartment newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Apartment> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Apartment get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Apartment findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Apartment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Apartment> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Apartment|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Apartment saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Apartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Apartment>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Apartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Apartment> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Apartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Apartment>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Apartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Apartment> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ApartmentsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('apartments');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->getSchema()->setColumnType('booked', 'boolean');

        $this->addBehavior('Timestamp', [
            'created' => 'created_at',
            'modified' => 'updated_at',
        ]);

        $this->belongsTo('Landlords', [
            'className' => 'Users',
            'foreignKey' => 'landlord_id',
        ]);
        $this->hasMany('Leases', [
            'foreignKey' => 'apartment_id',
        ]);
        $this->hasMany('MaintenanceRequests', [
            'foreignKey' => 'apartment_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('address')
            ->maxLength('address', 255)
            ->requirePresence('address', 'create')
            ->notEmptyString('address');

        $validator
            ->decimal('rent')
            ->requirePresence('rent', 'create')
            ->notEmptyString('rent')
            ->greaterThan('rent', 0);

        $validator
            ->decimal('size')
            ->requirePresence('size', 'create')
            ->notEmptyString('size')
            ->greaterThan('size', 0);

        $validator
            ->integer('nb_rooms')
            ->requirePresence('nb_rooms', 'create')
            ->notEmptyString('nb_rooms')
            ->greaterThan('nb_rooms', 0);

        $validator
            ->integer('nb_bathrooms')
            ->requirePresence('nb_bathrooms', 'create')
            ->notEmptyString('nb_bathrooms')
            ->greaterThan('nb_bathrooms', 0);

        $validator
            ->boolean('booked')
            ->requirePresence('booked', 'create');

        $validator
            ->scalar('energy_class')
            ->maxLength('energy_class', 1)
            ->requirePresence('energy_class', 'create')
            ->notEmptyString('energy_class')
            ->inList('energy_class', ['A', 'B', 'C', 'D', 'E', 'F', 'G']);

        $validator
            ->scalar('climat_class')
            ->maxLength('climat_class', 1)
            ->allowEmptyString('climat_class')
            ->inList('climat_class', ['A', 'B', 'C', 'D', 'E', 'F', 'G']);

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->integer('landlord_id')
            ->allowEmptyString('landlord_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['landlord_id'], 'Landlords'), ['errorField' => 'landlord_id']);

        return $rules;
    }
}
