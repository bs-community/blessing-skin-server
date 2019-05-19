<?php

namespace Tests;

use App\Models\User;
use App\Models\Report;
use App\Models\Texture;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReportControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testSubmit()
    {
        $user = factory(User::class)->create();
        $texture = factory(Texture::class)->create();

        // Without `tid` field
        $this->actingAs($user)
            ->postJson('/skinlib/report')
            ->assertJsonValidationErrors('tid');

        // Invalid texture
        $this->postJson('/skinlib/report', ['tid' => $texture->tid - 1])
            ->assertJsonValidationErrors('tid');

        // Without `reason` field
        $this->postJson('/skinlib/report', ['tid' => $texture->tid])
            ->assertJsonValidationErrors('reason');

        // Lack of score
        $user->score = 0;
        $user->save();
        option(['reporter_score_modification' => -5]);
        $this->postJson('/skinlib/report', ['tid' => $texture->tid, 'reason' => 'reason'])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.upload.lack-score'),
            ]);

        // Success
        option(['reporter_score_modification' => 5]);
        $this->postJson('/skinlib/report', ['tid' => $texture->tid, 'reason' => 'reason'])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.report.success'),
            ]);
        $user->refresh();
        $this->assertEquals(5, $user->score);
        $report = Report::where('reporter', $user->uid)->first();
        $this->assertEquals($texture->tid, $report->tid);
        $this->assertEquals($texture->uploader, $report->uploader);
        $this->assertEquals('reason', $report->reason);
        $this->assertEquals(Report::PENDING, $report->status);

        // Prevent duplication
        $this->postJson('/skinlib/report', ['tid' => $texture->tid, 'reason' => 'reason'])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.report.duplicate'),
            ]);
    }

    public function testTrack()
    {
        $user = factory(User::class)->create();
        $report = new Report;
        $report->tid = 1;
        $report->uploader = 0;
        $report->reporter = $user->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();

        $this->actingAs($user)
            ->getJson('/user/report-list')
            ->assertJson([[
                'tid' => 1,
                'reason' => 'test',
                'status' => Report::PENDING,
            ]]);
    }

    public function testManage()
    {
        $uploader = factory(User::class)->create();
        $reporter = factory(User::class, 'admin')->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        $report = new Report;
        $report->tid = $texture->tid;
        $report->uploader = $uploader->uid;
        $report->reporter = $reporter->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();

        $this->actingAs($reporter)
            ->getJson('/admin/report-data')
            ->assertJson([
                'totalRecords' => 1,
                'data' => [[
                    'tid' => $texture->tid,
                    'uploader' => $uploader->uid,
                    'reporter' => $reporter->uid,
                    'reason' => 'test',
                    'status' => Report::PENDING,
                    'uploaderName' => $uploader->nickname,
                    'reporterName' => $reporter->nickname,
                ]],
            ]);
    }

    public function testReview()
    {
        $admin = factory(User::class, 'admin')->create();
        $report = new Report;
        $report->save();
        $report->refresh();

        // Without `id` field
        $this->actingAs($admin)
            ->postJson('/admin/reports')
            ->assertJsonValidationErrors('id');

        // Not existed
        $this->postJson('/admin/reports', ['id' => $report->id - 1])
            ->assertJsonValidationErrors('id');

        // Without `action` field
        $this->postJson('/admin/reports', ['id' => $report->id])
            ->assertJsonValidationErrors('action');

        // Invalid action
        $this->postJson('/admin/reports', ['id' => $report->id, 'action' => 'a'])
            ->assertJsonValidationErrors('action');

        // Allow to process again
        $this->postJson('/admin/reports', ['id' => $report->id, 'action' => 'reject'])
            ->assertJson(['code' => 0]);
    }

    public function testReviewReject()
    {
        $uploader = factory(User::class)->create();
        $reporter = factory(User::class)->create();
        $admin = factory(User::class, 'admin')->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        $report = new Report;
        $report->tid = $texture->tid;
        $report->uploader = $uploader->uid;
        $report->reporter = $reporter->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();
        $report->refresh();

        // Should not cost score
        $score = $reporter->score;
        $this->actingAs($admin)
            ->postJson('/admin/reports', ['id' => $report->id, 'action' => 'reject'])
            ->assertJson([
                'code' => 0,
                'message' => trans('general.op-success'),
                'data' => ['status' => Report::REJECTED],
            ]);
        $report->refresh();
        $reporter->refresh();
        $this->assertEquals(Report::REJECTED, $report->status);
        $this->assertEquals($score, $reporter->score);

        // Should cost score
        $report->status = Report::PENDING;
        $report->save();
        option(['reporter_score_modification' => 5]);
        $score = $reporter->score;
        $this->postJson('/admin/reports', ['id' => $report->id, 'action' => 'reject'])
            ->assertJson(['code' => 0]);
        $reporter->refresh();
        $this->assertEquals($score - 5, $reporter->score);
    }

    public function testReviewDelete()
    {
        $uploader = factory(User::class)->create();
        $reporter = factory(User::class)->create();
        $admin = factory(User::class, 'admin')->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        $report = new Report;
        $report->tid = $texture->tid;
        $report->uploader = $uploader->uid;
        $report->reporter = $reporter->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();
        $report->refresh();

        option([
            'reporter_score_modification' => -7,
            'return_score' => false,
            'take_back_scores_after_deletion' => false,
        ]);
        $score = $reporter->score;
        $this->actingAs($admin)
            ->postJson('/admin/reports', ['id' => $report->id, 'action' => 'delete'])
            ->assertJson([
                'code' => 0,
                'message' => trans('general.op-success'),
                'data' => ['status' => Report::RESOLVED],
            ]);
        $report->refresh();
        $reporter->refresh();
        $this->assertEquals(Report::RESOLVED, $report->status);
        $this->assertNull(Texture::find($texture->tid));
        $this->assertEquals($score + 7, $reporter->score);
    }

    public function testReviewDeleteNonExistentTexture()
    {
        $uploader = factory(User::class)->create();
        $reporter = factory(User::class)->create();
        $admin = factory(User::class, 'admin')->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        $report = new Report;
        $report->tid = $texture->tid;
        $report->uploader = $uploader->uid;
        $report->reporter = $reporter->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();
        $report->refresh();

        option([
            'reporter_reward_score' => 6,
            'reporter_score_modification' => -7,
        ]);
        $score = $reporter->score;
        $texture->delete();
        $this->actingAs($admin)
            ->postJson('/admin/reports', ['id' => $report->id, 'action' => 'delete'])
            ->assertJson([
                'code' => 0,
                'message' => trans('general.texture-deleted'),
                'data' => ['status' => Report::RESOLVED],
            ]);
        $report->refresh();
        $this->assertEquals(Report::RESOLVED, $report->status);
        $this->assertEquals($score, $reporter->score);
    }

    public function testReviewBan()
    {
        $uploader = factory(User::class)->create();
        $reporter = factory(User::class)->create();
        $admin = factory(User::class, 'admin')->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        $report = new Report;
        $report->tid = $texture->tid;
        $report->uploader = $uploader->uid;
        $report->reporter = $reporter->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();
        $report->refresh();

        // Uploader should be banned
        option(['reporter_reward_score' => 6]);
        $score = $reporter->score;
        $this->actingAs($admin)
            ->postJson('/admin/reports', ['id' => $report->id, 'action' => 'ban'])
            ->assertJson([
                'code' => 0,
                'message' => trans('general.op-success'),
                'data' => ['status' => Report::RESOLVED],
            ]);
        $reporter->refresh();
        $uploader->refresh();
        $this->assertEquals(User::BANNED, $uploader->permission);
        $this->assertEquals($score + 6, $reporter->score);
        option(['reporter_reward_score' => 0]);

        // Should not ban admin uploader
        $report->refresh();
        $report->status = Report::PENDING;
        $report->save();
        $uploader->refresh();
        $uploader->permission = User::ADMIN;
        $uploader->save();
        $this->postJson('/admin/reports', ['id' => $report->id, 'action' => 'ban'])
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.users.operations.no-permission'),
            ]);
        $report->refresh();
        $this->assertEquals(Report::PENDING, $report->status);
        $this->assertEquals(User::ADMIN, $uploader->permission);

        // Uploader has deleted its account
        $report->uploader = -1;
        $report->save();
        $this->postJson('/admin/reports', ['id' => $report->id, 'action' => 'ban'])
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.users.operations.non-existent'),
            ]);
    }
}
