<p>これは、Helloレイアウトを利用したサンプルです</p>
<table>
<?= $this->Html->tableHeaders(["title", 'name', 'mail'],
    ['style' => ['abckground:#006;color:white']]) ?>
<?= $this->Html->tableCells([
        ["this is sample", "taro", "taro@yamada"],
        ["Hello!", "hanako", "hanako@flower"],
        ["test,test.", "sachiko", "sachi@co.jp"],
        ["last!.", "jiro", "jiro@change"],
],
['style' => ['background:#ccf']],
['style' => ['background:#dff']]) ?>
</table>
<ul>
<?= $this->Html->nestedList(
    ['first line', 'second line',
    'third line' => ['one', 'two']]
) ?>
</ul>
<p><?=$this->Url->build(['controller' => 'hello', 'action' => 'show', '123']) ?></p>

<p><?=$this->Url->build(['controller' => 'hello', 'action' => 'show', '?' => ['id' => 'taro', 'password' => 'yamada123']]) ?></p>


<p><?=$this->Url->build(['controller' => 'hello', 'action' => 'show', '_ext' => 'png', 'sample']) ?></p>


<p><?=$this->Text->autoLinkUrls('http://google.com') ?></p>

<p><?=$this->Text->autoParagraph("one\ntwo\nthree") ?></p>

<p>金額は、<?=$this->Number->currency(123456789, 'JPY') ?></p>

<p>2桁で表すと、<?=$this->Number->precision(1234.56789, 2) ?></p>
<p>割合は、<?=$this->Number->toPercentage(0.12345, 2, ['multiply' => true]) ?>です。</p>
