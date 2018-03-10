<?php

namespace models\components\externalDb;

use Yii;
use yii\base\Exception;

/**
 * Class Pubmed
 * @package models\components\externalDb
 */
class Pubmed extends Cite
{
    /**
     * Pubmed constructor.
     * @param bool|int $id
     * @param array    $config
     */
    public function __construct($id = false, $config = [])
    {
        $this->id = $id;

        parent::__construct($config);
    }

    /**
     * @throws Exception
     */
    public function getInfoById()
    {
        if (!$this->id) {
            throw new Exception('Fatal error in function <Pubmed/getInfoById>: empty self::$input');
        }

        $this->loadFromPubmedById();
        $this->journalFormat();
    }

    /**
     * Get PMID and load self::$attributes
     *
     * @throws Exception
     */
    protected function loadFromPubmedById()
    {
        $email = Yii::$app->params['adminEmail'];
        $apiKey = Yii::$app->params['ncbiKey'];
        $xml = @simplexml_load_file("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?tool=Citing_Bot&email={$email}&api_key={$apiKey}&db=pubmed&id={$this->id}");

        if ($xml === false) {
            throw new Exception('Fatal error in function <Pubmed/loadFromPubmedById>: unable to do PubMed search');
        }

        $this->url = "https://www.ncbi.nlm.nih.gov/pubmed/{$this->id}";

        /** @var \SimpleXMLElement $item */
        if (count($xml->DocSum->Item) > 0) foreach ($xml->DocSum->Item as $item) {
            switch ($item["Name"]) {
                case "Title":
                    $this->title = str_replace(['[', ']'], '', (string)$item);
                    break;
                case "PubDate":
                    preg_match("~(\d+)\s*(\w*)(?:\s*(\d+))*~", (string)$item, $match);
                    $this->year = (int)$match[1];
                    if (isset($match[2]) && $match[2] !== null) $this->month = $this->monthFormat((string)$match[2]);
                    if (isset($match[3]) && $match[3] !== null) $this->day = (int)$match[3];
                    break;
                case "FullJournalName":
                    $this->edition = ucwords((string)$item);
                    break;
                case "Volume":
                    $this->volume = (string)$item;
                    break;
                case "Issue":
                    $this->issue = (string)$item;
                    break;
                case "Pages":
                    $this->pages = $this->pagesFormat((string)$item);
                    break;
                case "PmId":
                    $this->pmid = (string)$item;
                    break;
                case "AuthorList":
                    $i = 0;
                    foreach ($item->Item as $subItem) {
                        $i++;
                        if ($this->isHumanAuthor((string)$subItem)) {
                            $juniorName = $this->juniorAuthorName($subItem);
                            $subItem = $juniorName[0];
                            $junior = $juniorName[1];
                            if (preg_match("~(.*) (\w+)$~", $subItem, $names)) {
                                $first = trim(preg_replace('~(?<=[A-Z])([A-Z])~', ". $1", $names[2]));
                                if (substr($first, -1) != '.') {
                                    $first = $first . '.';
                                }
                                $this->author[$i] = $names[1] . $junior . ' ' . $first;
                            }
                        } else {
                            // author not human
                            $this->author[$i] = (string)$subItem;
                        }
                    }
                    break;
                case "LangList":
                    $i = 0;
                    foreach ($item->Item as $subItem) {
                        $lang = $this->languageFormat((string)$subItem);
                        if ($lang) $this->lang[$i++] = $lang;
                    }
                    break;
                case 'ISSN':
                    break;
                case "ArticleIds":
                    foreach ($item->Item as $subItem) {
                        switch ($subItem["Name"]) {
                            case "pubmed":
                                preg_match("~\d+~", (string)$subItem, $match);
                                $this->pmid = (int)$match[0];
                                break;
                            case "doi":
                            case "pii":
                            default:
                                if (preg_match("~10\.\d{4}/[^\s\"']*~", (string)$subItem, $match)) {
                                    $this->doi = (string)$match[0];
                                }
                                break;
                        }
                    }
                    break;
            }
        }
    }

    /**
     * @param string $month
     * @return int|null
     */
    protected function monthFormat($month = '')
    {
        $months = [
            'Jan' => 1,
            'Feb' => 2,
            'Mar' => 3,
            'Apr' => 4,
            'May' => 5,
            'Jun' => 6,
            'Jul' => 7,
            'Aug' => 8,
            'Sep' => 9,
            'Oct' => 10,
            'Nov' => 11,
            'Dec' => 12,
        ];

        if (isset($months[$month]))
            return $months[$month];

        return null;
    }

    /**
     * @param string $pages
     * @return string
     */
    protected function pagesFormat($pages = '')
    {
        preg_match_all('/(\d+)/is', $pages, $pagesArray);

        $pageSecond = $pageFirst = $pagesArray[1][0];
        if (isset($pagesArray[1][1])) $pageSecond = $pagesArray[1][1];

        $pageFirstLen = strlen($pageFirst);
        $pageSecondLen = strlen($pageSecond);

        if ($pages[0] == 'D') {
            $truePages = 'D';
        } elseif ($pages[0] == 'e') {
            $truePages = 'e';
        } else {
            $truePages = '';
        }

        $truePages .= $pageFirst;
        if ($pageSecond !== null) {
            $pageSecondStart = '';

            if ($pageFirstLen > $pageSecondLen) {
                $pageSecondStart = substr($pageFirst, 0, $pageFirstLen - $pageSecondLen);
            }

            $truePages .= '—' . $pageSecondStart . $pageSecond;
        }

        return $truePages;
    }

    protected function languageFormat($lang = '')
    {
        $languages = [
            'English'  => 'en',
            'German'   => 'ger',
            'Japanese' => 'ja',
            'Chinese'  => 'zh',
            'Italian'  => 'it',
            'French'   => 'fr',
        ];

        if (isset($languages[$lang]))
            return $languages[$lang];

        return false;
    }
}