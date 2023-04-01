<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\FileTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @noinspection PhpUnused */

class SimpleNewsComponent extends CBitrixComponent implements Controllerable
{
    /** @var Cache */
    protected Cache $cache;

    /** @var string */
    protected string $cachePath;

    /** @var string */
    protected string $cacheId = 'simplenews_сomp';

    protected PageNavigation $nav;

    /**
     * @return string[]
     */
    protected function listKeysSignedParameters(): array
    {
        return [
            'IBLOCK_ID',
            'NEWS_COUNT',
        ];
    }

    /**
     * @param $component
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->cache = Cache::createInstance();
        $this->cachePath = str_replace([':', '//'], '/', '/' . SITE_ID . '/' . $this->getName() . '/');
    }

    /**
     * @return void
     */
    public function executeComponent(): void
    {
        try {
            $tabs = $this->getTabs();
            $firsYear = current($this->getTabs());
            $newsList = $this->getNewsListByDate($firsYear);
            $this->arResult = [
                'TABS'  => $tabs,
                'NEWS'  => $newsList['NEWS'],
                'NAV'   => $newsList['NAV'],
                'COUNT' => $newsList['COUNT'],
            ];

            $this->includeComponentTemplate();
        } catch (Exception $e) {
            $this->cache->abortDataCache();
            ShowError($e->getMessage());
        }
    }

    /**
     * @return array[]
     */
    public function configureActions(): array
    {
        return [
            'getNewsList' => [
                'prefilters' => [
                    new HttpMethod([HttpMethod::METHOD_POST]),
                ],
            ],
        ];
    }

    /**
     * @noinspection PhpUnused
     *
     * @param                $year
     * @param PageNavigation $pageNavigation
     *
     * @throws ArgumentException
     * @throws ObjectException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return string
     */
    public function getNewsListAction($year, PageNavigation $pageNavigation): string
    {
        $offset = $pageNavigation->getCurrentPage();
        $tabs = $this->getTabs();
        $newsList = $this->getNewsListByDate($year, $offset);
        $this->arResult = [
            'TABS'  => $tabs,
            'NEWS'  => $newsList['NEWS'],
            'NAV'   => $newsList['NAV'],
            'COUNT' => $newsList['COUNT'],
        ];

        ob_start();
        $this->includeComponentTemplate();

        return ob_get_clean();
    }

    /**
     * Получает табы по годам.
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     * @return array
     */
    protected function getTabs(): array
    {
        $path = __FUNCTION__ . '/';

        if (empty($result = $this->initCache($this->cacheId, $path))) {
            $elementQuery = ElementTable::query()
                                        ->setSelect(
                                            [
                                                'ACTIVE_FROM',
                                            ]
                                        )
                                        ->setFilter(
                                            [
                                                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                                                'ACTIVE'    => 'Y',
                                            ]
                                        )
                                        ->setOrder(
                                            [
                                                'ACTIVE_FROM' => 'DESC',
                                            ]
                                        )
                                        ->exec();
            if ($elementQuery->getSelectedRowsCount() === 0) {
                $this->cache->abortDataCache();
                throw new Exception(Loc::getMessage('SIMPLENEWS_COMP_ITEMS_NOT_FOUND'));
            }

            while ($res = $elementQuery->fetch()) {
                $result[] = $res['ACTIVE_FROM']->format('Y');
            }
            $result = array_values(array_unique($result));

            $this->setCache($this->cacheId, $path, $result);
        }

        return $result;
    }

    /**
     * Получение списка новостей за выбранный год.
     *
     * @param string $year
     * @param int    $offset
     *
     * @throws ArgumentException
     * @throws ObjectException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     * @return array
     */
    protected function getNewsListByDate(string $year, int $offset = 0): array
    {
        $path = __FUNCTION__ . '/' . $year;
        $dateStart = new \Bitrix\Main\Type\DateTime($year, 'Y-m-d H:i:s');
        $dateEnd = (new \Bitrix\Main\Type\DateTime($year, 'Y-m-d H:i:s'))->add(
            '11month 30day 23hour 59minutes 59seconds'
        );

        $nav = new PageNavigation('more-news');
        $nav->allowAllRecords(false)
            ->setPageSize($this->arParams['NEWS_COUNT']);

        if ($offset) {
            $nav->setCurrentPage($offset);
        }
        $nav->initFromUri();

        if (empty($result = $this->initCache($this->cacheId . $year, $path))) {
            $elementQuery = ElementTable::query()
                                        ->setSelect(
                                            [
                                                'NAME',
                                                'ACTIVE_FROM',
                                                'PREVIEW_TEXT',
                                                'PREVIEW_PICTURE',
                                                'IMAGE_SUBDIR'    => 'IMAGE.SUBDIR',
                                                'IMAGE_FILE_NAME' => 'IMAGE.FILE_NAME',
                                            ]
                                        )
                                        ->setFilter(
                                            [
                                                'IBLOCK_ID'     => $this->arParams['IBLOCK_ID'],
                                                'ACTIVE'        => 'Y',
                                                '>=ACTIVE_FROM' => $dateStart,
                                                '<ACTIVE_FROM'  => $dateEnd,
                                            ]
                                        )
                                        ->setOrder(
                                            [
                                                'ACTIVE_FROM' => 'DESC',
                                            ]
                                        )
                                        ->countTotal(true)
                                        ->setLimit($nav->getLimit())
                                        ->setOffset($nav->getOffset())
                                        ->registerRuntimeField(
                                            'IMAGE',
                                            [
                                                'data_type' => FileTable::class,
                                                'reference' => ['this.PREVIEW_PICTURE' => 'ref.ID'],
                                            ]
                                        )
                                        ->exec();

            if ($elementQuery->getSelectedRowsCount() === 0) {
                $this->cache->abortDataCache();
                throw new Exception(Loc::getMessage('SIMPLENEWS_COMP_ITEMS_NOT_FOUND'));
            }

            while ($res = $elementQuery->fetch()) {
                $res['PREVIEW_PICTURE'] = $res['PREVIEW_PICTURE'] ? '/upload/' . $res['IMAGE_SUBDIR'] . '/' . $res['IMAGE_FILE_NAME'] : $res['PREVIEW_PICTURE'];
                unset($res['IMAGE_SUBDIR'], $res['IMAGE_FILE_NAME']);
                $result['NEWS'][] = $res;
            }

            $newsCount = $result['COUNT'] = $elementQuery->getCount();
            $nav->setRecordCount($newsCount);
            $result['NAV'] = $nav;

            $this->setCache($this->cacheId . $year, $path, $result);
        }

        return $result;
    }

    /**
     * @param string $id
     * @param string $path
     *
     * @return array
     */
    private function initCache(string $id, string $path = ''): array
    {
        if ($this->cache->initCache($this->arParams['CACHE_TIME'], $id, $this->cachePath . $path)) {
            return $this->cache->getVars();
        }

        return [];
    }

    /**
     * @param string $id
     * @param string $path
     * @param array  $data
     *
     * @return void
     */
    private function setCache(string $id, string $path, array $data): void
    {
        if ($this->arParams['CACHE_TIME'] > 0) {
            $this->cache->startDataCache($this->arParams['CACHE_TIME'], $id, $this->cachePath . $path);
            $this->cache->endDataCache($data);
        }
    }
}