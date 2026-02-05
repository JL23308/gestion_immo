<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateLeases extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('leases');
        $table->addColumn('apartment_id', 'integer', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('user_id', 'integer', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('start_date', 'date', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('end_date', 'date', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('monthly_rent', 'decimal', [
            'default' => null,
            'null' => false,
            'precision' => 10,
            'scale' => 2,
        ]);
        $table->addColumn('deposit', 'decimal', [
            'default' => null,
            'null' => false,
            'precision' => 10,
            'scale' => 2,
        ]);
        $table->addColumn('status', 'string', [
            'default' => 'active',
            'limit' => 20,
            'null' => false,
        ]);
        $table->addColumn('notes', 'text', [
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
        $table->addIndex(['apartment_id']);
        $table->addIndex(['user_id']);
        $table->addIndex(['status']);
        $table->create();
    }
}
