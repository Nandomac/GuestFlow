<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW view_workcenter_structures_path AS
            SELECT
                neto.id,
                neto.structure_code,
                neto.structure_name,
                CONCAT_WS(' << ',
                    CONCAT(
                        neto.structure_code, ' - ', neto.structure_name
                    ),
                    COALESCE(pai.structure_name, NULL),
                    COALESCE(avo.structure_name, NULL),
                    COALESCE(`avo`.`structure_contract`,
                        `pai`.`structure_contract`,
                        `neto`.`structure_contract`,
                        NULL)
                ) AS structure_path,
                neto.created_at,
                neto.updated_at,
                neto.deleted_at
            FROM workcenter_structures AS neto
            LEFT JOIN workcenter_structures AS pai ON pai.id = neto.structure_parent_id
            LEFT JOIN workcenter_structures AS avo ON avo.id = pai.structure_parent_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_workcenter_structures_path');
    }
};
