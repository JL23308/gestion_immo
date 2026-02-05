<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'email' => 'user1@example.com',
                'password' => '$2y$12$nG9TKRPG5u7aU0qdJd5aPurQkLLYPfioUt.t6wV23NTnupsKyawUi',
                'created' => '2026-01-13 08:39:43',
                'modified' => '2026-01-13 08:39:43',
            ],
        ];
        parent::init();
    }
}
