<?php

namespace models\components;


/**
 * Class Template
 * @package models\components
 *
 * @property array      $author                Author
 * @property string     $title                 Title
 * @property string     $titleOriginal         Title in original lang
 * @property string     $url                   Url
 * @property array      $lang                  Lang
 * @property string     $responsible           Responsible (Составитель)
 * @property string     $authorEdition         Author of edition
 * @property string     $edition               Edition
 * @property string     $type                  Type of edition (journal, site, etc)
 * @property string     $publisher             Publisher
 * @property int        $year                  Year
 * @property int        $month                 Month
 * @property int        $day                   Day
 * @property string|int $volume                Volume
 * @property string|int $issue                 Issue
 * @property string|int $number                Number
 * @property string|int $pages                 Pages
 * @property string     $isbn                  ISBN
 * @property string     $issn                  ISSN
 * @property string     $doi                   DOI
 * @property int        $pmid                  PMID
 * @property string     $bibcode               Bibcode
 * @property string     $arxiv                 arXiv
 * @property string     $archiveurl            arXiv url
 * @property string     $archivedate           arXiv date
 */
class Template extends BaseModel
{
    public $author;
    public $title;
    public $titleOriginal;
    public $url;
    public $lang;
    public $responsible;
    public $authorEdition;
    public $edition;
    public $type;
    public $publisher;
    public $year;
    public $month;
    public $day;
    public $volume;
    public $issue;
    public $number;
    public $pages;
    public $isbn;
    public $issn;
    public $doi;
    public $bibcode;
    public $arxiv;
    public $pmid;
    public $ref;
    public $archiveurl;
    public $archivedate;

    /**
     * @param $values
     * @throws \ReflectionException
     */
    public function setAttributes($values)
    {
        if (is_array($values)) {
            $attributes = array_flip($this->attributes());
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    $this->$name = $value;
                }
            }
        }
    }

    public function getOutput()
    {
        $output = '{{статья' . PHP_EOL
            . '|автор=' . $this->authorsFormat() . PHP_EOL
            . '|заглавие=' . $this->title . PHP_EOL;

        if ($this->url) $output .= '|ссылка=' . $this->url . PHP_EOL;
        if ($this->lang) $output .= '|язык=' . $this->lang[0] . PHP_EOL;
        if ($this->edition) $output .= '|издание=' . $this->edition . PHP_EOL;
        if ($this->year) $output .= '|год=' . $this->year . PHP_EOL;
        if ($this->month) $output .= '|месяц=' . $this->month . PHP_EOL;
        if ($this->day) $output .= '|день=' . $this->day . PHP_EOL;
        if ($this->volume) $output .= '|том=' . $this->volume . PHP_EOL;
        if ($this->issue) $output .= '|номер=' . $this->issue . PHP_EOL;
        if ($this->pages) $output .= '|страницы=' . $this->pages . PHP_EOL;
        if ($this->pmid) $output .= '|pmid=' . $this->pmid . PHP_EOL;
        if ($this->doi) $output .= '|doi=' . $this->doi . PHP_EOL;
        if ($this->issn) $output .= '|issn=' . $this->issn . PHP_EOL;
        if ($this->isbn) $output .= '|doi=' . $this->isbn . PHP_EOL;

        $output .= '}}<noinclude>{{doc|Шаблон:cite_pmid/subpage}}</noinclude>';

        return $output;
    }

    protected function authorsFormat()
    {
        $authors = $this->author;
        $output = [];

        foreach ($authors as $key => $author) {
            $output[$key] = '{{nobr|' . $author . '}}';
        }

        return implode(', ', $output);
    }
}