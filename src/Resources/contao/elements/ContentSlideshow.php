<?php

/*
 * This file is part of KenBurnsSlideshowBundle.
 *
 * @package   KenBurnsSlideshowBundle
 * @author    Marcel Mathias Nolte
 * @copyright Marcel Mathias Nolte 2020
 * @website	  https://github.com/marcel-mathias-nolte
 * @license   LGPL-3.0-or-later
 */

namespace MarcelMathiasNolte\KenBurnsSlideshowBundle;

use \Contao\CoreBundle\Exception\PageNotFoundException;
use \Contao\Model\Collection;

/**
 * Front end content element "gallery".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContentGallery extends \Contao\ContentElement
{
    /**
     * Files object
     * @var Collection|\Contao\FilesModel
     */
    protected $objFiles;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_mmn_kb_slideshow';

    /**
     * Return if there are no files
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . $GLOBALS['TL_LANG']['CTE']['mmn_kenburns'][0] . ' ###';
            $objTemplate->id = $this->id;

            return $objTemplate->parse();
        }

        $this->multiSRC = \Contao\StringUtil::deserialize($this->multiSRC);

        // Return if there are no files
        if (empty($this->multiSRC) || !\is_array($this->multiSRC))
        {
            return '';
        }

        // Get the file entries from the database
        $this->objFiles = \Contao\FilesModel::findMultipleByUuids($this->multiSRC);

        if ($this->objFiles === null)
        {
            return '';
        }

        $GLOBALS['TL_CSS'][] = 'bundles/kenburnsslideshow/css/jquery.kenBurnsSlideshow.css';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/kenburnsslideshow/js/jquery.kenBurnsSlideshow.js';

        return parent::generate();
    }

    /**
     * Generate the content element
     */
    protected function compile()
    {
        $this->Template->id = md5(uniqid());

        $images = array();
        $auxDate = array();
        $objFiles = $this->objFiles;

        // Get all images
        while ($objFiles->next())
        {
            // Continue if the files has been processed or does not exist
            if (isset($images[$objFiles->path]) || !file_exists(\Contao\System::getContainer()->getParameter('kernel.project_dir') . '/' . $objFiles->path))
            {
                continue;
            }

            // Single files
            if ($objFiles->type == 'file')
            {
                $newPath = DcaCallbacks::getBlurredSrc($objFiles);
                if ($newPath != $objFiles->path) {
                    continue;
                }

                $objFile = new \Contao\File($objFiles->path);

                if (!$objFile->isImage)
                {
                    continue;
                }

                // Add the image
                $images[$objFiles->path] = array
                (
                    'id'         => $objFiles->id,
                    'uuid'       => $objFiles->uuid,
                    'name'       => $objFile->basename,
                    'singleSRC'  => $objFiles->path,
                    'filesModel' => $objFiles->current()
                );

                $auxDate[] = $objFile->mtime;
            }

            // Folders
            else
            {
                $objSubfiles = \Contao\FilesModel::findByPid($objFiles->uuid, array('order' => 'name'));

                if ($objSubfiles === null)
                {
                    continue;
                }

                while ($objSubfiles->next())
                {
                    // Skip subfolders
                    if ($objSubfiles->type == 'folder')
                    {
                        continue;
                    }

                    $newPath = DcaCallbacks::getBlurredSrc($objSubfiles);;
                    if (!$newPath) {
                        continue;
                    } elseif ($newPath != $objSubfiles->path) {
                        $objSubfiles->path = $newPath;
                    }

                    $objFile = new \Contao\File($objSubfiles->path);

                    if (!$objFile->isImage)
                    {
                        continue;
                    }

                    // Add the image
                    $images[$objSubfiles->path] = array
                    (
                        'id'         => $objSubfiles->id,
                        'uuid'       => $objSubfiles->uuid,
                        'name'       => $objFile->basename,
                        'singleSRC'  => $objSubfiles->path,
                        'filesModel' => $objSubfiles->current(),
                        'isHidden'   => !FE_USER_LOGGED_IN && $objSubfiles->hidden
                    );

                    $auxDate[] = $objFile->mtime;
                }
            }
        }

        $images = array_values($images);
        $arrImages = [];
        foreach ($images as $i => $image) {
            $objCell = new \stdClass();
            $images[$i]['size'] = $this->size;
            $images[$i]['imagemargin'] = '';
            $images[$i]['fullsize'] = '';
            $this->addImageToTemplate($objCell, $images[$i], null, '', $images[$i]['filesModel']);
            $arrImages[] = $objCell;
        }

        $this->Template->images = $arrImages;
        $this->Template->opacityTransistionTime = $this->mmn_kb_opacityTransistionTime;
        $this->Template->timePerSlide = $this->mmn_kb_timePerSlide;
        $this->Template->maxZoom = $this->mmn_kb_maxZoom;
        $this->Template->shuffle = $this->mmn_kb_shuffle;
    }
}
