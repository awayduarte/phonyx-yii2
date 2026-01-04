<?php
namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class CatalogCest
{
    public function catalogLoads(FunctionalTester $I)
    {
    
        $I->amOnPage('/site/catalog');

        $I->see('Catálogo');

        
        $I->seeElement('.track-card');
    }
}

