<?php

use yii\db\Migration;

class m260204_113407_add_custom_gamma_to_presets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1. Modalità di generazione: 'step' (progressivo) o 'list' (manuale/select2)
        $this->addColumn('{{%presets}}', 'gamma_mode', $this->string(20)->notNull()->defaultValue('step')->after('gamma_step'));

        // 2. Campo per memorizzare la lista di gamma (es: "1.0,1.8,2.2")
        $this->addColumn('{{%presets}}', 'gamma_custom_list', $this->text()->null()->after('gamma_mode'));

        // 3. Un default per la step wedge (comodo per non doverlo cliccare sempre)
        $this->addColumn('{{%presets}}', 'show_wedge_default', $this->boolean()->defaultValue(true)->after('gamma_custom_list'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%presets}}', 'show_wedge_default');
        $this->dropColumn('{{%presets}}', 'gamma_custom_list');
        $this->dropColumn('{{%presets}}', 'gamma_mode');
    }
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260204_113407_add_custom_gamma_to_presets cannot be reverted.\n";

        return false;
    }
    */
}
