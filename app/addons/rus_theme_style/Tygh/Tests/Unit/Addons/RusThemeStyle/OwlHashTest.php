<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Tests\Unit\Addons\RusThemeStyle;

use Tygh\Tests\Unit\ATestCase;

class OwlHashTest extends ATestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;

    protected $dir_root;

    protected function setUp()
    {
        $this->dir_root = __DIR__ . '/../../../../../../../../';
    }

    public function testBase()
    {
        $this->assertEquals('eb99f0e232ec3f2aeb946046c3a97b1250fdd2b8', sha1_file($this->dir_root . 'js/lib/owlcarousel/owl.carousel.min.js'));
    }

}

