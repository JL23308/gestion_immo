<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
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

        $this->addBehavior('Timestamp', [
            'created' => 'createdAt',
            'modified' => 'updatedAt'
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
            ->numeric('rent')
            ->requirePresence('rent', 'create')
            ->notEmptyString('rent');

        $validator
            ->scalar('address')
            ->requirePresence('address', 'create')
            ->notEmptyString('address');

        $validator
            ->integer('booked')
            ->requirePresence('booked', 'create')
            ->notEmptyString('booked');

        $validator
            ->scalar('energyClass')
            ->requirePresence('energyClass', 'create')
            ->notEmptyString('energyClass');

        $validator
            ->integer('nbRooms')
            ->requirePresence('nbRooms', 'create')
            ->notEmptyString('nbRooms');

        $validator
            ->integer('nbBathrooms')
            ->requirePresence('nbBathrooms', 'create')
            ->notEmptyString('nbBathrooms');

        $validator
            ->numeric('size')
            ->requirePresence('size', 'create')
            ->notEmptyString('size');

        $validator
            ->scalar('climatClass')
            ->allowEmptyString('climatClass');

        $validator
            ->scalar('createdAt')
            ->allowEmptyString('createdAt');

        $validator
            ->scalar('updatedAt')
            ->allowEmptyString('updatedAt');

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
        $rules->add($rules->isUnique(['id']), ['errorField' => 'id']);

        return $rules;
    }
    public function beforeSave(\Cake\Event\EventInterface $event, \Cake\Datasource\EntityInterface $entity, \ArrayObject $options)
    {
        if ($entity->isNew() && empty($entity->createdAt)) {
            $entity->createdAt = date('Y-m-d H:i:s');
        }
        
        $entity->updatedAt = date('Y-m-d H:i:s');
    }
}
