<?php

/* @var $this yii\web\View */
/* @var $isEditSuccess boolean */
/* @var $doi int */

$this->title = 'DOI parser';
?>
<div class="api-doi">

    <?php if ($isEditSuccess): ?>
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <a href="https://ru.wikipedia.org/wiki/Шаблон:Cite_doi/<?= $doi ?>" target="_blank">Страница подшаблона</a>
            успешно создана!
        </div>
    <?php endif; ?>

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
            <label for="input-doi">DOI</label>
            <input class="form-control" name="doi" id="input-doi" placeholder="DOI">
        </div>

        <button type="submit" class="btn">Отправить</button>
    </form>
</div>
