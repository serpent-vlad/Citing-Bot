<?php

namespace models\components\externalDb;

use Yii;
use yii\base\Exception;
use yii\caching\TagDependency;

/**
 * Class Doi
 * @package models\components\externalDb
 */
class Doi extends Cite
{
    const USER_AGENT = 'CitingBot/2.0 (https://ru.wikipedia.org/wiki/User:Citing_Bot; mailto:kekakop@outlook.com) BasedOnPHP/5.5';

    const CACHE_TAG_ALL_DOI = 'DOI_ALL_TAGS';
    const CACHE_TAG_ONE_DOI = 'doi_key-';

    /**
     * Doi constructor.
     * @param bool  $id
     * @param array $config
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
            throw new Exception('Fatal error in function <Doi/getInfoById>: empty self::$input');
        }

        $this->loadFromDoiById();
    }

    /**
     * Get DOI and load self::$attributes
     *
     * @throws Exception
     */
    protected function loadFromDoiById()
    {

        $this->doi = $this->id;

        if (!$this->verifyDoi()) {
            throw new Exception('Fatal error in function <Doi/loadFromDoiById>: no verify DOI');
        }
    }

    /**
     * @return bool
     */
    protected function verifyDoi()
    {
        $doi = trim($this->doi);
        if (!$doi) return false;

        switch (substr($doi, -1)) {
            case '.':
                $trial[] = $doi . 'x';
            case ',':
            case ';':
                $trial[] = substr($doi, 0, -1);
                break;
        }

        if (substr($doi, 0, 3) != '10.') {
            $trial[] = $doi;
        }
        if (preg_match('~^(.+)(10\.\d{3|4}/.+)~', trim($doi), $match)) {
            $trial[] = $match[1];
            $trial[] = $match[2];
        }

        $replacements = ['&lt;' => '<', '&gt;' => '>'];
        if (preg_match('~&[lg]t;~', $doi)) {
            $trial[] = str_replace(array_keys($replacements), $replacements, $doi);
        }

        if (isset($trial)) foreach ($trial as $testDoi) {
            if (preg_match('~[^/]*(\d{3|4}/.+)$~', $testDoi, $match))
                $testDoi = '10.' . $match[1];

            $urlTest = 'https://doi.org/' . $testDoi;
            $headersTest = @get_headers($urlTest, 1);

            if ($headersTest) {
                $this->doi = $testDoi;
            }
        }

        if ($this->getDoiFromCrossRef() === false) {
            $urlTest = 'https://doi.org/' . $doi;
            $headersTest = @get_headers($urlTest, 1);

            if ($headersTest === false) {
                Yii::warning('DOI status unkown.  dx.doi.org failed to respond at all to: ' . htmlspecialchars($doi));
                return false;
            }

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function getDoiFromCrossRef()
    {
        $url = 'https://api.crossref.org/works/' . urlencode($this->doi);

        $response = Yii::$app->cache->getOrSet(self::CACHE_TAG_ONE_DOI . md5($this->doi), function () use ($url) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_FAILONERROR    => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_HEADER         => false,
                CURLOPT_HTTPGET        => true,
                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_CONNECTTIMEOUT_MS => 1200,

                CURLOPT_URL       => $url,
                CURLOPT_USERAGENT => self::USER_AGENT,
            ]);

            $response = @json_decode($data = curl_exec($curl));

            if (!$data) {
                Yii::warning('Curl error: ' . htmlspecialchars(curl_error($curl)));
                return false;
            }

            if ($response->status == 'error') {
                Yii::warning('Curl has errors');
                return false;
            }

            return $response;
        }, 0, new TagDependency(['tags' => self::CACHE_TAG_ALL_DOI]));

        if ($response->status == 'ok') {
            $message = $response->message;

            $this->title = strip_tags($message->title[0]);
            $this->url = $message->url;
            $this->publisher = strip_tags($message->publisher);

            $this->year = $message->issued->{'date-parts'}[0][0];
            if (isset($message->issued->{'date-parts'}[0][1])) $this->month = $message->issued->{'date-parts'}[0][1];
            if (isset($message->issued->{'date-parts'}[0][2])) $this->day = $message->issued->{'date-parts'}[0][2];

            if (isset($message->author)) {
                foreach ($message->author as $author) {
                    $this->author[] = $author->family . ' ' . $author->given;
                }
            }

            if ($message->{'container-title'}) {
                if (is_string($message->{'container-title'})) {
                    $this->edition = $message->{'container-title'};
                } elseif (is_array($message->{'container-title'})) {
                    $this->edition = $message->{'container-title'}[0];
                }
            }

            if ($message->{'original-title'}) {
                if (is_string($message->{'original-title'})) {
                    $this->titleOriginal = $message->{'original-title'};
                } elseif (is_array($message->{'original-title'})) {
                    $this->titleOriginal = $message->{'original-title'}[0];
                }
            }

            if (isset($message->issue))
                $this->issue = $message->issue;

            if (isset($message->volume))
                $this->volume = $message->volume;

            if (isset($message->page)) {
                $pages = $message->page;
                $this->pages = str_replace('-', '—', $pages);
            }

            if ($message->ISSN) {
                if (is_string($message->ISSN)) {
                    $this->issn = $message->ISSN;
                } elseif (is_array($message->ISSN)) {
                    $this->issn = $message->ISSN[0];
                }
            }

            if ($message->ISBN) {
                if (is_string($message->ISBN)) {
                    $this->isbn = $message->ISBN;
                } elseif (is_array($message->ISBN)) {
                    $this->isbn = $message->ISBN[0];
                }
            }

            return true;
        }

        return false;
    }
}