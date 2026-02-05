<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateApartments extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('apartments');
        $table->addColumn('address', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('rent', 'decimal', [
            'default' => null,
            'null' => false,
            'precision' => 10,
            'scale' => 2,
        ]);
        $table->addColumn('size', 'decimal', [
            'default' => null,
            'null' => false,
            'precision' => 10,
            'scale' => 2,
        ]);
        $table->addColumn('nb_rooms', 'integer', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('nb_bathrooms', 'integer', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('booked', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->addColumn('energy_class', 'string', [
            'default' => null,
            'limit' => 1,
            'null' => false,
        ]);
        $table->addColumn('climat_class', 'string', [
            'default' => null,
            'limit' => 1,
            'null' => true,
        ]);
        $table->addColumn('description', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('landlord_id', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('created_at', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('updated_at', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addIndex(['landlord_id']);
        $table->create();
    }
}
