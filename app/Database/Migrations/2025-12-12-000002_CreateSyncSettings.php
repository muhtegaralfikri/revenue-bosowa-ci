<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSyncSettings extends Migration
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
            'key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'value' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addUniqueKey('key');
        $this->forge->createTable('sync_settings');
        
        // Insert default settings
        $this->db->table($this->db->prefixTable('sync_settings'))->insertBatch([
            ['key' => 'last_sync_time', 'value' => null, 'created_at' => date('Y-m-d H:i:s')],
            ['key' => 'last_sync_status', 'value' => 'never', 'created_at' => date('Y-m-d H:i:s')],
            ['key' => 'last_sync_count', 'value' => '0', 'created_at' => date('Y-m-d H:i:s')],
            ['key' => 'sync_interval_minutes', 'value' => '5', 'created_at' => date('Y-m-d H:i:s')],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('sync_settings');
    }
}
