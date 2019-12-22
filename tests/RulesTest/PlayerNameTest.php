<?php

namespace Tests;

use App\Rules\PlayerName;

class PlayerNameTest extends TestCase
{
    public function testOfficialRule()
    {
        $rule = new PlayerName();

        $this->assertTrue($rule->passes('', '_name_'));
        $this->assertTrue($rule->passes('', 'NaN'));

        $this->assertFalse($rule->passes('', '中文'));
        $this->assertFalse($rule->passes('', '§Me'));
        $this->assertFalse($rule->passes('', ';'));
        $this->assertFalse($rule->passes('', '\\'));

        $this->assertEquals(
            trans('user.player.player-name-rule.official'),
            $rule->message()
        );
    }

    public function testCJK()
    {
        option(['player_name_rule' => 'cjk']);
        $rule = new PlayerName();

        $this->assertTrue($rule->passes('', '_name_'));
        $this->assertTrue($rule->passes('', 'NaN'));
        $this->assertTrue($rule->passes('', '中文'));
        $this->assertTrue($rule->passes('', '§Me'));

        $this->assertFalse($rule->passes('', ';'));
        $this->assertFalse($rule->passes('', '\\'));

        $this->assertEquals(
            trans('user.player.player-name-rule.cjk'),
            $rule->message()
        );
    }

    public function testCustom()
    {
        option(['player_name_rule' => 'custom']);
        $rule = new PlayerName();

        $this->assertTrue($rule->passes('', '_name_'));
        $this->assertTrue($rule->passes('', 'NaN'));
        $this->assertTrue($rule->passes('', '中文'));
        $this->assertTrue($rule->passes('', '§Me'));
        $this->assertTrue($rule->passes('', ';'));
        $this->assertTrue($rule->passes('', '\\'));

        option(['custom_player_name_regexp' => '/[ab]/']);
        $this->assertTrue($rule->passes('', 'a'));
        $this->assertFalse($rule->passes('', 'c'));

        $this->assertEquals(
            trans('user.player.player-name-rule.custom'),
            $rule->message()
        );
    }
}
