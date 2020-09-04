<h2>商品を出品する</h1>
<?= $this->Form->create($biditem, ['enctype' => 'multipart/form-data']); ?>

<fieldset>
    <legend>※商品名と終了日時を入力：</legend>
    <?php
        echo $this->Form->hidden('user_id', ['value' => $authuser['id']]);
        echo '<p><strong>USER：' . $authuser['username'] . '</strong></p>';
        echo $this->Form->control('name', ['required' => false, 'label' => '商品名']);
        echo $this->Form->control('goods_detail', ['required' => false, 'type' => 'textarea', 'label' => '商品詳細']);
        echo $this->Form->input('goods_image', ['required' => false,  'type' => 'file', 'label' => '商品画像']);
        echo $this->Form->hidden('finished', ['value' => 0]);
        echo $this->Form->control('endtime', ['label' => '終了日時']);
    ?>
</fieldset>
<?= $this->Form->button(__('Submit')) ?>
<?= $this->Form->end() ?>
