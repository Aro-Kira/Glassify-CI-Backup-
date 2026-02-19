<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_billing_columns_to_payment extends CI_Migration {
    public function up() {
        $fields = [
            'billing_name'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'billing_email'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'billing_phone'       => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => TRUE],
            'billing_unit'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'billing_street'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'billing_subdivision' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'billing_barangay'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'billing_city'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'billing_province'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'billing_region'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'billing_postal_code' => ['type' => 'VARCHAR', 'constraint' => 20,  'null' => TRUE],
            'billing_country'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'billing_country_iso' => ['type' => 'VARCHAR', 'constraint' => 2,   'null' => TRUE],
            'billing_payload_json' => ['type' => 'TEXT', 'null' => TRUE],
        ];

        $this->dbforge->add_column('payment', $fields);
    }

    public function down() {
        $cols = [
            'billing_name','billing_email','billing_phone','billing_unit','billing_street',
            'billing_subdivision','billing_barangay','billing_city','billing_province','billing_region',
            'billing_postal_code','billing_country','billing_country_iso','billing_payload_json'
        ];
        foreach ($cols as $col) {
            if ($this->db->field_exists($col, 'payment')) {
                $this->dbforge->drop_column('payment', $col);
            }
        }
    }
}
