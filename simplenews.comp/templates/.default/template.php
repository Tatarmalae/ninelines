<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */

use Bitrix\Main\Context;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$ajaxYear = Context::getCurrent()->getRequest()->getPost('year') ?: current($arResult['TABS']);
?>

<div class="news-list">
    <div class="title">
        <?= 'Список новостей (' . $arResult['COUNT'] . ' шт.)' ?>
    </div>
    <div class="tabs">
        <?php foreach ($arResult['TABS'] as $key => $year) : ?>
            <div class="tab<?= $year == $ajaxYear ? ' selected' : '' ?>" data-year="<?= $year ?>">
                <?= $year ?>
            </div>
        <?php endforeach ?>
    </div>
    <div class="news-items">
        <?php foreach ($arResult['NEWS'] as $key => $item) : ?>
            <div class="news-item">
                <?= $item['NAME']; ?>
                <br>
                <?= $item['ACTIVE_FROM']->format('d.m.Y'); ?>
                <br>
                <?= $item['PREVIEW_TEXT']; ?>
                <br>
                <img src="<?= $item['PREVIEW_PICTURE']; ?>" alt="<?= $item['NAME']; ?>">
                <br><br>
            </div>
        <?php endforeach ?>
    </div>
    <?php
    $APPLICATION->IncludeComponent(
        'bitrix:main.pagenavigation',
        'modern',
        [
            'NAV_OBJECT' => $arResult['NAV'],
            'SEF_MODE'   => 'N',
        ],
        false
    );
    ?>
</div>

<?php if (!Context::getCurrent()->getRequest()->isAjaxRequest()): ?>
    <script>
        componentName = "<?= $this->getComponent()->getName() ?>";
        signedParameters = "<?= $this->getComponent()->getSignedParameters() ?>";
    </script>
<?php endif ?>
