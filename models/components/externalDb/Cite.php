<?php

namespace models\components\externalDb;

use models\components\BaseModel;

/**
 * Class Cite
 * @package models\components\externalDb
 *
 * @property int        $id                    PMID
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
 */
class Cite extends BaseModel
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
    public $pmid;
    public $bibcode;

    protected $id;

    /**
     * @param string $author
     * @return bool
     */
    protected function isHumanAuthor($author = '')
    {
        $author = trim($author);
        $chars = count_chars($author);

        if ($chars[ord(':')] > 0 || $chars[ord(' ')] > 3 || strlen($author) > 33
            || substr(strtolower($author), 0, 4) === 'the '
            || stripos($author, 'collaborat') !== false
            || preg_match('~[A-Z]{3}~', $author)
            || substr(strtolower($author), -4) === ' inc'
            || substr(strtolower($author), -5) === ' inc.'
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @return array
     */
    protected function juniorAuthorName($name = '')
    {
        $junior = (substr($name, -3) == ' Jr') ? ' Jr.' : false;

        if ($junior) {
            $name = substr($name, 0, -3);
        } else {
            $junior = (substr($name, -4) == ' Jr.') ? ' Jr.' : false;
            if ($junior) {
                $name = substr($name, 0, -4);
            }
        }

        if (substr($name, -1) == ',') {
            $name = substr($name, 0, -1);
        }

        return array($name, $junior);
    }

    /**
     * Formatted journal options
     */
    protected function journalFormat()
    {
        // журнал "PLoS One"
        if ($this->edition == 'PloS one') {
            $this->pages = 'e' . $this->pages;
            $this->edition = 'Public Library of Science ONE';
        }

        // журнал "PLoS biology"
        if ($this->edition == 'PLoS biology') {
            $this->pages = 'e' . $this->pages;
            $this->edition = 'Public Library of Science Biology';
        }

        // журнал "PLoS computational biology"
        if ($this->edition == 'PLoS computational biology') {
            $this->pages = 'e' . $this->pages;
            $this->edition = 'Public Library of Science for Computational Biology';
        }

        // журнал "Bioinformatics"
        if ($this->edition == 'Bioinformatics (Oxford, England)') {
            $this->edition = 'Bioinformatics';
        }

        // при отсутствии номера выпуска
        if ($this->issue == 'Web Server issue') {
            $this->issue = null;
        }

        // при отсутствии номера выпуска
        if ($this->issue == 'Epub ahead of print') {
            $this->issue = null;
        }

        // при отсутствии номера выпуска
        if ($this->issue == 'Database issue') {
            $this->issue = null;
        }
    }
}