<?php
use Migrations\AbstractMigration;

class AddBiditems extends AbstractMigration
{

    public function up()
    {

        $this->table('biditems')
            ->changeColumn('finished', 'boolean', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->changeColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->update();

        $this->table('biditems')
            ->addColumn('goods_detail', 'string', [
                'after' => 'name',
                'default' => null,
                'length' => 10000,
                'null' => false,
            ])
            ->addColumn('goods_image', 'string', [
                'after' => 'goods_detail',
                'default' => null,
                'length' => 100,
                'null' => false,
            ])
            ->update();
    }

    public function down()
    {

        $this->table('biditems')
            ->changeColumn('finished', 'boolean', [
                'default' => null,
                'length' => null,
                'null' => false,
            ])
            ->changeColumn('created', 'datetime', [
                'default' => null,
                'length' => null,
                'null' => false,
            ])
            ->removeColumn('goods_detail')
            ->removeColumn('goods_image')
            ->update();
    }
}

