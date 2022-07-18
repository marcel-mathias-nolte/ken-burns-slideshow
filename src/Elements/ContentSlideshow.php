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

namespace MarcelMathiasNolte\KenBurnsSlideshowBundle\Elements;

use \Contao\CoreBundle\Exception\PageNotFoundException;
use \Contao\Model\Collection;

/**
 * Front end content element "gallery".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContentSlideshow extends \Contao\ContentElement
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
			$objTemplate->title = $this->mm_kb_title;
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

        $GLOBALS['TL_CSS']['jquery.kenBurnsSlideshow.css'] = 'bundles/kenburnsslideshow/css/jquery.kenBurnsSlideshow.css';
        $GLOBALS['TL_JAVASCRIPT']['jquery.kenBurnsSlideshow.js'] = 'bundles/kenburnsslideshow/js/jquery.kenBurnsSlideshow.js';

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
                        'filesModel' => $objSubfiles->current()
                    );

                    $auxDate[] = $objFile->mtime;
                }
            }
        }

        $member = FE_USER_LOGGED_IN ? \FrontendUser::getInstance() : false;
        $showFsk18 = $member && $member->isMemberOf(6);
        $showSexual = $member && $member->isMemberOf(7);
        if (is_array($images) && count($images) > 0)
        {
            foreach ($images as $key => $image)
            {
                if (!$showSexual && $image['filesModel']->fsk == 'erotic')
                {
                    unset($images[$key]);
                    continue;
                }
                if (!$showFsk18 && $image['filesModel']->fsk == 'porn')
                {
                    unset($images[$key]);
                    continue;
                }
                $videoFile = TL_ROOT . '/' . $image['filesModel']->path;
                $videoFile = substr($videoFile, 0, strlen($videoFile) - strlen($image['filesModel']->extension)) . 'mp4';
                $videoFile = str_replace('-blur', '', $videoFile);
                $videoFile = str_replace('-icon', '', $videoFile);
                $videoFile = str_replace('_blur', '', $videoFile);
                $videoFile = str_replace('_icon', '', $videoFile);
                if (file_exists($videoFile)) {
                    unset($images[$key]);
                    continue;
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
