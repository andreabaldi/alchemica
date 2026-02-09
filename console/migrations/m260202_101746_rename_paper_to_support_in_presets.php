<?php

use yii\db\Migration;

class m260202_101746_rename_paper_to_support_in_presets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Rinomina la colonna esistente
        $this->renameColumn('{{%presets}}', 'paper_name', 'support_name');

        // Aggiunge la colonna per il futuro puntatore alla tabella carte
        $this->addColumn('{{%presets}}', 'paper_id', $this->integer()->after('support_name'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%presets}}', 'paper_id');
        $this->renameColumn('{{%presets}}', 'support_name', 'paper_name');
    }
}
