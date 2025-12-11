<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRevenueTargetsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'company_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'year' => [
                'type' => 'INT',
                'constraint' => 4,
            ],
            'month' => [
                'type' => 'INT',
                'constraint' => 2,
            ],
            'target_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['company_id', 'year', 'month']);
        $this->forge->createTable('revenue_targets');
    }

    public function down()
    {
        $this->forge->dropTable('revenue_targets');
    }
}
