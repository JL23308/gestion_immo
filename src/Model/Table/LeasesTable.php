<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Leases Model
 *
 * @property \App\Model\Table\ApartmentsTable&\Cake\ORM\Association\BelongsTo $Apartments
 * @property \App\Model\Table\TenantsTable&\Cake\ORM\Association\BelongsTo $Tenants
 * @property \App\Model\Table\PaymentsTable&\Cake\ORM\Association\HasMany $Payments
 *
 * @method \App\Model\Entity\Lease newEmptyEntity()
 * @method \App\Model\Entity\Lease newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Lease> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Lease get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Lease findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Lease patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Lease> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Lease|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Lease saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Lease>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Lease>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Lease>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Lease> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Lease>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Lease>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Lease>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Lease> deleteManyOrFail(iterable $entities, array $options = [])
 */
class LeasesTable extends Table
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

        $this->setTable('leases');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'created' => 'created_at',
            'modified' => 'updated_at',
        ]);

        $this->belongsTo('Apartments', [
            'foreignKey' => 'apartment_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
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
            ->integer('apartment_id')
            ->requirePresence('apartment_id', 'create')
            ->notEmptyString('apartment_id');

        $validator
            ->integer('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->date('start_date')
            ->requirePresence('start_date', 'create')
            ->notEmptyDate('start_date');

        $validator
            ->date('end_date')
            ->allowEmptyDate('end_date');

        $validator
            ->decimal('monthly_rent')
            ->allowEmptyString('monthly_rent')
            ->greaterThan('monthly_rent', 0, null, function($context) {
                return !empty($context['data']['monthly_rent']);
            });

        $validator
            ->decimal('deposit')
            ->requirePresence('deposit', 'create')
            ->notEmptyString('deposit')
            ->greaterThanOrEqual('deposit', 0);

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->requirePresence('status', 'create')
            ->notEmptyString('status')
            ->inList('status', ['active', 'expired', 'terminated', 'pending']);

        $validator
            ->scalar('notes')
            ->allowEmptyString('notes');

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
        $rules->add($rules->existsIn(['apartment_id'], 'Apartments'), ['errorField' => 'apartment_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * beforeSave callback to auto-fill monthly_rent from apartment rent if not provided
     */
    public function beforeSave($event, $entity, $options)
    {
        // If monthly_rent is not set, use the apartment's rent
        if (empty($entity->monthly_rent) && !empty($entity->apartment_id)) {
            $apartment = $this->Apartments->get($entity->apartment_id);
            $entity->monthly_rent = $apartment->rent;
        }
        
        return true;
    }
}
