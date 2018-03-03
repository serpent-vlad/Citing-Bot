<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <?php if ($output): ?>
        <div class="form-group">
            <label for="outputTemplate">Готовый шаблон:</label>
            <textarea id="outputTemplate" class="form-control" rows="8"
                      onfocus="select(this);"><?= preg_replace('/\r\n/i', '', $output) ?></textarea>
        </div>

        <div class="form-group">
            <pre><?= $output ?></pre>
        </div>
    <?php endif; ?>

    <form action="<?= \yii\helpers\Url::to(['api/index']) ?>">

        <div class="form-group">
            <label for="input-pmid">PMID</label>
            <input class="form-control" name="pmid" id="input-pmid" placeholder="PMID">
        </div>

        <div class="form-group">
            <label for="input-doi">DOI</label>
            <input class="form-control" name="doi" id="input-doi" placeholder="DOI">
        </div>

        <button type="submit" class="btn">Отправить</button>
    </form>
</div>
