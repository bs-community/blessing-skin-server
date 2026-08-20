<?php

namespace Tests\ServicesTest;

use App\Models\Player;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use DatabaseTransactions;

    public function testEmptyQueryIsIgnored()
    {
        User::factory()->count(3)->create();

        $this->assertSame(3, User::search(null)->count());
        $this->assertSame(3, User::search('')->count());
        $this->assertSame(3, User::search('   ')->count());
    }

    public function testFreeTextMatchesSearchableColumns()
    {
        User::factory()->create(['nickname' => 'Alice', 'email' => 'a@example.com']);
        User::factory()->create(['nickname' => 'Bob', 'email' => 'bob@other.test']);

        $this->assertSame(1, User::search('Alice')->count());
        // email is searchable too, so a fragment of it matches.
        $this->assertSame(1, User::search('other.test')->count());
        $this->assertSame(0, User::search('nobody')->count());
    }

    public function testFreeTextIsCaseInsensitiveSubstring()
    {
        User::factory()->create(['nickname' => 'Alexander']);

        $this->assertSame(1, User::search('exand')->count());
    }

    public function testExactFieldMatch()
    {
        $user = User::factory()->create(['score' => 42]);
        User::factory()->create(['score' => 7]);

        $this->assertSame(1, User::search('score:42')->count());
        $this->assertSame(1, User::search('score=42')->count());
        $this->assertSame($user->uid, User::search('score:42')->first()->uid);
    }

    public function testComparisonOperators()
    {
        User::factory()->create(['score' => 10]);
        User::factory()->create(['score' => 50]);
        User::factory()->create(['score' => 90]);

        $this->assertSame(2, User::search('score>10')->count());
        $this->assertSame(3, User::search('score>=10')->count());
        $this->assertSame(1, User::search('score<50')->count());
        $this->assertSame(2, User::search('score<=50')->count());
        $this->assertSame(2, User::search('score!=50')->count());
    }

    public function testBooleanColumns()
    {
        User::factory()->create(['verified' => true]);
        User::factory()->count(2)->create(['verified' => false]);

        $this->assertSame(1, User::search('verified:true')->count());
        $this->assertSame(2, User::search('verified:false')->count());
        $this->assertSame(1, User::search('verified:1')->count());
        $this->assertSame(1, User::search('verified:yes')->count());
    }

    public function testBareDateMatchesTheWholeDay()
    {
        User::factory()->create(['register_at' => '2020-01-01 13:45:00']);
        User::factory()->create(['register_at' => '2020-01-02 00:00:00']);

        $this->assertSame(1, User::search('register_at:2020-01-01')->count());
        $this->assertSame(1, User::search('register_at>2020-01-01')->count());
        $this->assertSame(2, User::search('register_at>=2020-01-01')->count());
    }

    public function testColumnAliases()
    {
        $user = User::factory()->create();
        Player::factory()->create(['uid' => $user->uid, 'tid_skin' => 5]);
        Player::factory()->create(['uid' => $user->uid, 'tid_skin' => 9]);

        $this->assertSame(1, Player::search('skin:5')->count());
        $this->assertSame(1, Player::search('tid_skin:5')->count());
    }

    public function testNegation()
    {
        User::factory()->create(['nickname' => 'Alice']);
        User::factory()->create(['nickname' => 'Bob']);

        $this->assertSame(1, User::search('not Alice')->count());
        $this->assertSame(1, User::search('!Alice')->count());
        // The factory gives every user a score of 1000.
        $this->assertSame(2, User::search('not score:0')->count());
        $this->assertSame(0, User::search('not score:1000')->count());
    }

    public function testConjunctionAndDisjunction()
    {
        User::factory()->create(['nickname' => 'Alice', 'score' => 10]);
        User::factory()->create(['nickname' => 'Alice', 'score' => 90]);
        User::factory()->create(['nickname' => 'Bob', 'score' => 90]);

        // Adjacent terms are ANDed.
        $this->assertSame(1, User::search('Alice score:90')->count());
        $this->assertSame(1, User::search('Alice and score:90')->count());
        $this->assertSame(3, User::search('Alice or score:90')->count());
        $this->assertSame(2, User::search('(Alice or Bob) and score:90')->count());
    }

    public function testInList()
    {
        User::factory()->create(['score' => 1]);
        User::factory()->create(['score' => 2]);
        User::factory()->create(['score' => 3]);

        $this->assertSame(2, User::search('score in (1,3)')->count());
    }

    /**
     * No column in this schema is nullable, so the NULL keyword is asserted
     * against the generated SQL rather than against rows.
     */
    public function testNullKeyword()
    {
        $this->assertStringContainsString(
            '"last_sign_at" is null',
            User::search('last_sign_at:NULL')->toSql(),
        );
        $this->assertStringContainsString(
            '"last_sign_at" is not null',
            User::search('last_sign_at!=NULL')->toSql(),
        );
    }

    public function testQuotedValuesArePreserved()
    {
        User::factory()->create(['nickname' => 'Alice Smith']);
        User::factory()->create(['nickname' => 'Alice']);

        $this->assertSame(1, User::search('"Alice Smith"')->count());
        $this->assertSame(2, User::search('Alice')->count());
    }

    public function testWildcardsInFreeTextAreEscaped()
    {
        User::factory()->create(['nickname' => 'Alice']);

        // Without escaping, "%" would match every row.
        $this->assertSame(0, User::search('%')->count());
    }

    public function testUnknownColumnFallsBackToFreeText()
    {
        User::factory()->create(['nickname' => 'Alice']);
        User::factory()->create(['nickname' => 'Bob']);

        $this->assertSame(1, User::search('nikname:Alice')->count());
    }

    public function testModelWithoutSearchableColumnsRejectsFreeText()
    {
        $report = new Report(['uploader' => 1, 'reporter' => 2, 'reason' => 'spam', 'status' => 0]);
        $report->tid = 1;
        $report->save();

        $this->assertSame(1, Report::search('status:0')->count());
        // Report declares no searchable columns, so a bare term cannot match.
        $this->assertSame(0, Report::search('spam')->count());
    }

    public function testMalformedInputDoesNotThrow()
    {
        User::factory()->create(['nickname' => 'Alice']);

        foreach ([')', '((', 'score:', ':', 'and', '"unterminated', 'a>'] as $query) {
            $this->assertIsInt(User::search($query)->count(), "failed on: $query");
        }
    }
}
