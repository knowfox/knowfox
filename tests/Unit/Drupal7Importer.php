<?php

namespace Tests\Unit;

use Knowfox\Drupal7\Commands\ImportDrupal7;
use Tests\TestCase;

class Drupal7Importer extends TestCase
{
    const MARKUP1 = '[img_assist|nid=1294|title=|desc=|link=none|align=left|width=100|height=98][img_assist|nid=1292|title=|desc=|link=none|align=right|width=100|height=98]Nachdem mein Heim-PC (ein <a href="http://de.shuttle.com/sv25.htm">Shuttle Barebone</a>) neuerdings immer nach 5 Minuten stehenbleibt, habe ich mich zum Umrüsten mit einem <a href="http://www.viac3.de/vpsd/produkte/epia_v/specs.htm">EPIA Mini-ITX Mainboard</a> entschlossen. Das ist heute gekommen, d.h. heute abend werde ich ein wenig schrauben und sägen.';
    const REPLACEMENT1 = '<div class="image float-left">![image-1294](/uuid/olav.net:1294/image?width=100)</div><div class="image float-right">![image-1292](/uuid/olav.net:1292/image?width=100)</div>Nachdem mein Heim-PC (ein <a href="http://de.shuttle.com/sv25.htm">Shuttle Barebone</a>) neuerdings immer nach 5 Minuten stehenbleibt, habe ich mich zum Umrüsten mit einem <a href="http://www.viac3.de/vpsd/produkte/epia_v/specs.htm">EPIA Mini-ITX Mainboard</a> entschlossen. Das ist heute gekommen, d.h. heute abend werde ich ein wenig schrauben und sägen.';

    const MARKUP2 = '[img_assist|nid=2944|align=right]... ist "erforderlich":http://dankbarkeit-ist-nicht-erforderlich.de/69. Das ganze gehuldigt in einem kleinen Spaßprojekt von mir (mit Nicos Hilfe).';
    const REPLACEMENT2 = '[img_assist|nid=2944|align=right]... ist [erforderlich](http://dankbarkeit-ist-nicht-erforderlich.de/69). Das ganze gehuldigt in einem kleinen Spaßprojekt von mir (mit Nicos Hilfe).';

    public function testTextileLinks()
    {
        $importer = app(ImportDrupal7::class);
        $this->assertEquals(self::REPLACEMENT2, $importer->replaceTextileLinks(self::MARKUP2));
    }

    public function testShortcodes()
    {
        $importer = app(ImportDrupal7::class);
        $this->assertEquals(self::REPLACEMENT1, $importer->replaceShortcodes(self::MARKUP1));
    }
}
