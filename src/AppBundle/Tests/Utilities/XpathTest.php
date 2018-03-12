<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Utilities;

use AppBundle\Utilities\Xpath;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Description of XpathTest
 */
class XpathTest extends TestCase {

    /**
     * @dataProvider getXmlData
     */
    public function testGetXmlValue($expected, $query, $default = null) {
        $xml = $this->getXml();
        $this->assertEquals($expected, Xpath::getXmlValue($xml, $query, $default));
    }
    
    public function getXmlData() {
        return [
            ['1', '//a', null],
            ['1', '//a', 3],
            ['3', '//c', 3],
        ];
    }

    /**
     * @expectedException Exception
     */
    public function testGetXmlValueException() {
        $xml = $this->getXml();
        Xpath::getXmlValue($xml, '/root/node()');
    }

    public function testQuery() {
        $xml = $this->getXml();
        $result = Xpath::query($xml, '/root/*');
        $this->assertEquals(2, count($result));
    }
    
    private function getXml() {
        $data = <<<'ENDXML'
        <root>
          <a>1</a>
          <b>2</b>
        </root>
ENDXML;
        return simplexml_load_string($data);
    }
    
}
