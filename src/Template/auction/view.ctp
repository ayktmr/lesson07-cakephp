<?php
use Cake\I18n\Time;

// jsカウントダウンタイマー用終了日
$end_date = $biditem->endtime;
$end_date = $end_date->i18nFormat('yyyy-MM-dd HH:mm:ss');
?>

<?php echo $this->Html->scriptStart(array('inline' => false )); ?>
    var end_date = "<?php echo $end_date; ?>";
<?php echo $this->Html->scriptEnd(); ?>
<?php echo $this->Html->script('auction'); ?>

<h2>「<?=$biditem->name ?>」の情報</h2>
<table class="vertical-table">
    <tr>
        <th class="small" scope="row">出品者</th>
        <td><?= $biditem->has('user') ? $biditem->user->username : '' ?></td>
    </tr>
    <tr>
        <th scope="row">商品名</th>
        <td><?= h($biditem->name) ?></td>
    </tr>
    <tr>
        <th scope="row">商品ID</th>
        <td><?= $this->Number->format($biditem->id) ?></td>
    </tr>
    <tr>
        <th scope="row">商品詳細</th>
        <td><?= h($biditem->goods_detail) ?></td>
    </tr>
    <tr>
        <th scope="row">商品画像</th>
        <td>
        <?= $this->Html->image('../goods_images/' . $biditem->goods_image, array('alt' => $biditem->name)); ?></td>
    </tr>
    <tr>
        <th scope="row">終了時間</th>
        <td>
            <?= h($biditem->endtime) ?>
            <span class="cdt_txt" id="cdt_txt"></span>
            <span class="cdt_date" id="cdt_date"></span>
        </td>
    </tr>
    <tr>
        <th scope="row">投稿時間</th>
        <td><?= h($biditem->created) ?></td>
    </tr>
    <tr>
        <th scope="row"><?= __('終了した？') ?></th>
        <td><?= $biditem->finished ? __('Yes') : __('No'); ?></td>
    </tr>
</table>

<div class="related">
    <h4><?= __('落札情報') ?></h4>

    <?php if(!empty($biditem->bidinfo)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col">落札者</th>
                <th scope="col">落札金額</th>
                <th scope="col">落札日時</th>
            </tr>
            <tr>
                <td><?= h($biditem->bidinfo->user->username) ?></td>
                <td><?= h($biditem->bidinfo->price) ?></td>
                <td><?= h($biditem->endtime) ?></td>
            </tr>
        </table>
    <?php else: ?>
        <p><?='※落札情報は、ありません。' ?></p>
    <?php endif; ?>
</div>

<div class="rerlated">
    <h4><?= __('入札情報') ?></h4>

    <?php if(!$biditem->finished): ?>
        <h6><a href="<?=$this->Url->build(['action' => 'bid', $biditem->id]) ?>">《入札する！》</a></h6>

    <?php if(!empty($biidrequests)): ?>
        <table cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th scope="col">入札者</th>
                    <th scope="col">金額</th>
                    <th scope="col">入札日時</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($bidrequests as $bidrequest): ?>
                <tr>
                    <td><?= h($bidrequest->user->username) ?></td>
                    <td><?= h($bidrequest->price) ?>円</td>
                    <td><?=$bidrequest->created ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <p><?='※入札は、まだありません。' ?></p>
    <?php endif; ?>
    <?php else: ?>
        <p><?='※入札は、終了しました。' ?></p>
    <?php endif; ?>
</div>
