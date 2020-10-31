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

namespace MarcelMathiasNolte\KenBurnsSlideshowBundle\Tests;

use PHPUnit\Framework\TestCase;

class KenBurnsSlideshowBundleTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new KenBurnsSlideshowBundle();

        $this->assertInstanceOf('MarcelMathiasNolte\KenBurnsSlideshowBundle\KenBurnsSlideshowBundle', $bundle);
    }
}
