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
        $uploader = factory(User::class)->create();
        $reporter = factory(User::class, 'admin')->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        $report = new Report;
        $report->tid = $texture->tid;
        $report->uploader = $uploader->uid;
        $report->reporter = $reporter->uid;
        $report->reason = 'test';
        $report->status = Report::REJECTED;
        $report->save();
        $report->refresh();

        // Without `id` field
        $this->actingAs($reporter)
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

        // Only process pending report
        $this->postJson('/admin/reports', ['id' => $report->id, 'action' => 'reject'])
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.report-reviewed'),
            ]);

        // Reject
        $report->status = Report::PENDING;
        $report->save();
        $score = $reporter->score;
        $this->postJson('/admin/reports', ['id' => $report->id, 'action' => 'reject'])
            ->assertJson([
                'code' => 0,
                'message' => trans('general.op-success'),
                'data' => ['status' => Report::REJECTED],
            ]);
        $report->refresh();
        $reporter->refresh();
        $this->assertEquals(Report::REJECTED, $report->status);
        $this->assertEquals($score, $reporter->score);

        $report->refresh();
        $report->status = Report::PENDING;
        $report->save();
        option(['reporter_score_modification' => 5]);
        $score = $reporter->score;
        $this->postJson('/admin/reports', ['id' => $report->id, 'action' => 'reject'])
            ->assertJson(['code' => 0]);
        $reporter->refresh();
        $this->assertEquals($score - 5, $reporter->score);

        // Delete texture
        option([
            'reporter_score_modification' => -7,
            'return_score' => false,
            'take_back_scores_after_deletion' => false,
        ]);
        $report->refresh();
        $report->status = Report::PENDING;
        $report->save();
        $score = $reporter->score;
        $this->postJson('/admin/reports', ['id' => $report->id, 'action' => 'delete'])
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
        option(['reporter_score_modification' => 0]);

        // Ban uploader
        option(['reporter_reward_score' => 6]);
        $report->refresh();
        $report->status = Report::PENDING;
        $report->reporter = $uploader->uid; // I REPORT MYSELF. (我 举 报 我 自 己)
        $report->save();
        $reporter = $uploader;
        $score = $reporter->score;
        $this->postJson('/admin/reports', ['id' => $report->id, 'action' => 'ban'])
            ->assertJson([
                'code' => 0,
                'message' => trans('general.op-success'),
                'data' => ['status' => Report::RESOLVED],
            ]);
        $reporter->refresh();
        $this->assertEquals(User::BANNED, $uploader->permission);
        $this->assertEquals($score + 6, $reporter->score);
        option(['reporter_reward_score' => 0]);

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
    }
}
