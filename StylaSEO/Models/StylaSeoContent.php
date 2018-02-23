<?php
namespace StylaSEO\Models;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Styla Seo Content
 *
 * @ORM\Entity
 * @ORM\Table(name="s_styla_seo_content")
 */
class StylaSeoContent extends ModelEntity
{
    /**
     * Primary Key - autoincrement value
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $path
     *
     * @ORM\Column(name="path", type="string", nullable=false)
     */
    private $path;

    /**
     * @var string $locale
     *
     * @ORM\Column(name="locale", type="string", nullable=false)
     */
    private $locale;

    /**
     * @var string $content
     *
     * @ORM\Column(name="content", type="string", nullable=false)
     */
    private $content;

    /**
     * @var string $time_updated
     *
     * @ORM\Column(name="time_updated", type="string", nullable=false)
     */
    private $time_updated;

    /**
     * @var string $time_created
     *
     * @ORM\Column(name="time_created", type="string", nullable=false)
     */
    private $time_created;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $path string
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param $locale string
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $locale string
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getTimeUpdated()
    {
        return $this->time_updated;
    }

    /**
     * @param $time_updated string
     */
    public function setTime_updated($time_updated)
    {
        $this->time_updated = $time_updated;
    }

    /**
     * @return string
     */
    public function getTimeCreated()
    {
        return $this->time_created;
    }

    /**
     * @param $time_created string
     */
    public function setTimeCreated($time_created)
    {
        $this->time_created = $time_created;
    }

}
