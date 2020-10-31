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

$GLOBALS['TL_DCA']['tl_content']['palettes']['mmn_gallery'] = '{type_legend},type;{source_legend},multiSRC;{image_legend},size;{protected_legend:hide},protected;{expert_legend:hide},guests;{invisible_legend:hide},invisible,start,stop;{mmn_ken_burns_legend},mmn_kb_timePerSlide,mmn_kb_maxZoom,mmn_kb_opacityTransistionTime,mmn_kb_shuffle';
$GLOBALS['TL_DCA']['tl_content']['fields']['multiSRC']['load_callback'][] = ['\MarcelMathiasNolte\KenBurnsSlideshowBundle\DcaCallbacks', 'setMultiSrcFlags'];
$GLOBALS['TL_DCA']['tl_content']['fields']['mmn_kb_timePerSlide'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['mmn_kb_timePerSlide'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('rgxp'=>'natural', 'maxlength'=>5, 'tl_class'=>'w50'),
    'sql'                     => "varchar(5) NOT NULL default '7500'"
];
$GLOBALS['TL_DCA']['tl_content']['fields']['mmn_kb_maxZoom'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['mmn_kb_maxZoom'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('rgxp'=>'natural', 'maxlength'=>3, 'tl_class'=>'w50'),
    'sql'                     => "varchar(3) NOT NULL default '150'"
];
$GLOBALS['TL_DCA']['tl_content']['fields']['mmn_kb_opacityTransistionTime'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['mmn_kb_opacityTransistionTime'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('rgxp'=>'natural', 'maxlength'=>4, 'tl_class'=>'w50'),
    'sql'                     => "varchar(4) NOT NULL default '300'"
];
$GLOBALS['TL_DCA']['tl_content']['fields']['mmn_kb_shuffle'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['mmn_kb_shuffle'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12'),
    'sql'                     => "char(1) NOT NULL default ''"
];
