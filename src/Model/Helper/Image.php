<?php

namespace ASMBS\ScheduleBuilder\Model\Helper;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Image
{
    /** @var  array */
    protected $image;

    public function __construct($fieldData)
    {
        // ID
        // title
        // filename
        // url
        // alt
        // author
        // description
        // caption
        // name
        // modified
        // mime_type
        // type
        // icon
        // width
        // height
        // sizes
        //   {size}
        //   {size-width}
        //   {size-height}
        $this->image = $fieldData;
    }

    public function getTitle()
    {
        return isset($this->image['title']) ? $this->image['title'] : null;
    }

    public function getAlt()
    {
        return isset($this->image['alt']) ? $this->image['alt'] : null;
    }

    public function getDescription()
    {
        return isset($this->image['description']) ? $this->image['description'] : null;
    }

    public function getCaption()
    {
        return isset($this->image['caption']) ? $this->image['caption'] : null;
    }

    public function getUrl($size = false)
    {
        if ($size && $this->sizeExists($size)) {
            return $this->image['sizes'][$size];
        }

        return $this->image['url'];
    }

    public function getWidth($size = false)
    {
        if ($size && $this->sizeExists($size)) {
            return $this->image['sizes'][$size .'-width'];
        }

        return $this->image['width'];
    }

    public function getHeight($size = false)
    {
        if ($size && $this->sizeExists($size)) {
            return $this->image['sizes'][$size .'-height'];
        }

        return $this->image['height'];

    }

    public function getSizes()
    {
        $sizes = [];
        foreach ($this->image['sizes'] as $key => $val) {
            if (preg_match('/\-(width|height)$/i', $key) === 0) {
                $sizes[] = $key;
            }
        }

        return $sizes;
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function hasCaption()
    {
        return (!empty($this->image['caption']));
    }

    /**
     * Determine whether a given image size identifier has been registered.
     *
     * @param   string  $size
     * @return  bool
     */
    public function sizeExists($size)
    {
        return (in_array($size, get_intermediate_image_sizes()) && isset($this->image['sizes'][$size]));
    }
}
