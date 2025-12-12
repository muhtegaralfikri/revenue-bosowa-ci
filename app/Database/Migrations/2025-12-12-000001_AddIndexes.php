<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexes extends Migration
{
    public function up()
    {
        // Add indexes to revenue_realizations table
        $this->forge->addKey('company_id', false, false, 'idx_realizations_company');
        $this->forge->addKey('date', false, false, 'idx_realizations_date');
        
        // Composite index for common queries
        $this->db->query('CREATE INDEX idx_realizations_company_date ON ' . $this->db->prefixTable('revenue_realizations') . ' (company_id, `date`)');
        
        // Add indexes to revenue_targets table
        $this->db->query('CREATE INDEX idx_targets_company ON ' . $this->db->prefixTable('revenue_targets') . ' (company_id)');
        $this->db->query('CREATE INDEX idx_targets_year_month ON ' . $this->db->prefixTable('revenue_targets') . ' (year, month)');
        $this->db->query('CREATE INDEX idx_targets_company_year_month ON ' . $this->db->prefixTable('revenue_targets') . ' (company_id, year, month)');
        
        // Add index to companies table
        $this->db->query('CREATE INDEX idx_companies_code ON ' . $this->db->prefixTable('companies') . ' (code)');
        $this->db->query('CREATE INDEX idx_companies_active ON ' . $this->db->prefixTable('companies') . ' (is_active)');
        
        // Add index to users table
        $this->db->query('CREATE INDEX idx_users_email ON ' . $this->db->prefixTable('users') . ' (email)');
        $this->db->query('CREATE INDEX idx_users_active ON ' . $this->db->prefixTable('users') . ' (is_active)');
    }

    public function down()
    {
        // Remove indexes
        $this->db->query('DROP INDEX IF EXISTS idx_realizations_company ON ' . $this->db->prefixTable('revenue_realizations'));
        $this->db->query('DROP INDEX IF EXISTS idx_realizations_date ON ' . $this->db->prefixTable('revenue_realizations'));
        $this->db->query('DROP INDEX IF EXISTS idx_realizations_company_date ON ' . $this->db->prefixTable('revenue_realizations'));
        
        $this->db->query('DROP INDEX IF EXISTS idx_targets_company ON ' . $this->db->prefixTable('revenue_targets'));
        $this->db->query('DROP INDEX IF EXISTS idx_targets_year_month ON ' . $this->db->prefixTable('revenue_targets'));
        $this->db->query('DROP INDEX IF EXISTS idx_targets_company_year_month ON ' . $this->db->prefixTable('revenue_targets'));
        
        $this->db->query('DROP INDEX IF EXISTS idx_companies_code ON ' . $this->db->prefixTable('companies'));
        $this->db->query('DROP INDEX IF EXISTS idx_companies_active ON ' . $this->db->prefixTable('companies'));
        
        $this->db->query('DROP INDEX IF EXISTS idx_users_email ON ' . $this->db->prefixTable('users'));
        $this->db->query('DROP INDEX IF EXISTS idx_users_active ON ' . $this->db->prefixTable('users'));
    }
}
