<?php

namespace Tests;

use App\Models\Report;
use App\Models\Texture;
use App\Models\User;
use Blessing\Filter;
use Blessing\Rejection;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;

class ReportControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testSubmit()
    {
        Event::fake();

        $filter = resolve(Filter::class);
        $user = User::factory()->create();
        $texture = Texture::factory()->create();

        // without `tid` field
        $this->actingAs($user)
            ->postJson('/skinlib/report')
            ->assertJsonValidationErrors('tid');

        // invalid texture
        $this->postJson('/skinlib/report', ['tid' => $texture->tid - 1])
            ->assertJsonValidationErrors('tid');

        // without `reason` field
        $this->postJson('/skinlib/report', ['tid' => $texture->tid])
            ->assertJsonValidationErrors('reason');

        // lack of score
        $user->score = 0;
        $user->save();
        option(['reporter_score_modification' => -5]);
        $this->postJson('/skinlib/report', ['tid' => $texture->tid, 'reason' => 'reason'])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.upload.lack-score'),
            ]);
        option(['reporter_score_modification' => 5]);

        // rejection
        $filter->add(
            'user_can_report',
            function ($can, $tid, $reason, $reporter) use ($texture, $user) {
                $this->assertEquals($texture->tid, $tid);
                $this->assertEquals('reason', $reason);
                $this->assertEquals($user->uid, $reporter->uid);

                return new Rejection('rejected');
            }
        );
        $this->postJson('/skinlib/report', ['tid' => $texture->tid, 'reason' => 'reason'])
            ->assertJson(['code' => 1, 'message' => 'rejected']);
        $filter->remove('user_can_report');

        // success
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
        Event::assertDispatched('report.submitting', function ($event, $payload) use ($texture, $user) {
            [$tid, $reason, $reporter] = $payload;
            $this->assertEquals($texture->tid, $tid);
            $this->assertEquals('reason', $reason);
            $this->assertEquals($user->uid, $reporter->uid);

            return true;
        });
        Event::assertDispatched('report.submitted', function ($event, $payload) use ($texture, $user) {
            [$report] = $payload;
            $this->assertEquals($texture->tid, $report->tid);
            $this->assertEquals($texture->uploader, $report->uploader);
            $this->assertEquals($user->uid, $report->reporter);
            $this->assertEquals('reason', $report->reason);
            $this->assertEquals(Report::PENDING, $report->status);

            return true;
        });

        // prevent duplication
        $this->postJson('/skinlib/report', ['tid' => $texture->tid, 'reason' => 'reason'])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.report.duplicate'),
            ]);
    }

    public function testTrack()
    {
        $user = User::factory()->create();
        $report = new Report();
        $report->tid = 1;
        $report->uploader = 0;
        $report->reporter = $user->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();

        $this->actingAs($user)
            ->getJson('/user/reports')
            ->assertSee('test')
            ->assertSee(trans('front-end.report.status')[0]);
    }

    public function testManage()
    {
        $uploader = User::factory()->create();
        $reporter = User::factory()->admin()->create();
        $texture = Texture::factory()->create(['uploader' => $uploader->uid]);

        $report = new Report();
        $report->tid = $texture->tid;
        $report->uploader = $uploader->uid;
        $report->reporter = $reporter->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();

        $this->actingAs($reporter)
            ->getJson('/admin/reports/list')
            ->assertJson(['data' => [$report->toArray()]]);
    }

    public function testReview()
    {
        Event::fake();

        $admin = User::factory()->admin()->create();
        $texture = Texture::factory()->create(['uploader' => $admin->uid]);

        $report = new Report();
        $report->tid = $texture->tid;
        $report->uploader = $admin->uid;
        $report->reporter = $admin->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();
        $report->refresh();

        // Without `action` field
        $this->actingAs($admin)
            ->putJson('/admin/reports/'.$report->id)
            ->assertJsonValidationErrors('action');

        // invalid action
        $this->putJson('/admin/reports/'.$report->id, ['action' => 'a'])
            ->assertJsonValidationErrors('action');

        // allow to process again
        $this->putJson('/admin/reports/'.$report->id, ['action' => 'reject'])
            ->assertJson(['code' => 0]);
        $id = $report->id;
        Event::assertDispatched('report.reviewing', function ($event, $payload) use ($id) {
            [$report, $action] = $payload;
            $this->assertEquals($id, $report->id);
            $this->assertEquals('reject', $action);

            return true;
        });
    }

    public function testReviewReject()
    {
        Event::fake();

        $uploader = User::factory()->create();
        $reporter = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $texture = Texture::factory()->create(['uploader' => $uploader->uid]);

        $report = new Report();
        $report->tid = $texture->tid;
        $report->uploader = $uploader->uid;
        $report->reporter = $reporter->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();
        $report->refresh();
        $id = $report->id;

        // should not cost score
        $score = $reporter->score;
        $this->actingAs($admin)
            ->putJson('/admin/reports/'.$report->id, ['action' => 'reject'])
            ->assertJson([
                'code' => 0,
                'message' => trans('general.op-success'),
                'data' => ['status' => Report::REJECTED],
            ]);
        $report->refresh();
        $reporter->refresh();
        $this->assertEquals(Report::REJECTED, $report->status);
        $this->assertEquals($score, $reporter->score);
        Event::assertDispatched('report.reviewing', function ($event, $payload) use ($id) {
            [$report, $action] = $payload;
            $this->assertEquals($id, $report->id);
            $this->assertEquals('reject', $action);

            return true;
        });
        Event::assertDispatched('report.rejected', function ($event, $payload) use ($id) {
            [$report] = $payload;
            $this->assertEquals($id, $report->id);

            return true;
        });

        // should cost score
        $report->status = Report::PENDING;
        $report->save();
        option(['reporter_score_modification' => 5]);
        $score = $reporter->score;
        $this->putJson('/admin/reports/'.$report->id, ['action' => 'reject'])
            ->assertJson(['code' => 0]);
        $reporter->refresh();
        $this->assertEquals($score - 5, $reporter->score);
    }

    public function testReviewDelete()
    {
        Event::fake();
        $disk = Storage::fake('textures');

        $uploader = User::factory()->create();
        $reporter = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $texture = Texture::factory()->create(['uploader' => $uploader->uid]);
        $disk->put($texture->hash, '');

        $report = new Report();
        $report->tid = $texture->tid;
        $report->uploader = $uploader->uid;
        $report->reporter = $reporter->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();
        $report->refresh();
        $id = $report->id;
        $tid = $texture->tid;

        option([
            'reporter_score_modification' => -7,
            'return_score' => false,
            'take_back_scores_after_deletion' => false,
        ]);
        $score = $reporter->score;
        $this->actingAs($admin)
            ->putJson('/admin/reports/'.$report->id, ['action' => 'delete'])
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
        Storage::assertMissing($texture->hash);
        Event::assertDispatched('report.reviewing', function ($event, $payload) use ($id) {
            [$report, $action] = $payload;
            $this->assertEquals($id, $report->id);
            $this->assertEquals('delete', $action);

            return true;
        });
        Event::assertDispatched('texture.deleting', function ($event, $payload) use ($tid) {
            [$texture] = $payload;
            $this->assertEquals($tid, $texture->tid);

            return true;
        });
        Event::assertDispatched('texture.deleted', function ($event, $payload) use ($tid) {
            [$texture] = $payload;
            $this->assertEquals($tid, $texture->tid);

            return true;
        });
        Event::assertDispatched('report.resolved', function ($event, $payload) use ($id) {
            [$report, $action] = $payload;
            $this->assertEquals($id, $report->id);
            $this->assertEquals('delete', $action);

            return true;
        });
    }

    public function testReviewDeleteNonExistentTexture()
    {
        Event::fake();

        $uploader = User::factory()->create();
        $reporter = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $texture = Texture::factory()->create(['uploader' => $uploader->uid]);

        $report = new Report();
        $report->tid = $texture->tid;
        $report->uploader = $uploader->uid;
        $report->reporter = $reporter->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();
        $report->refresh();
        $id = $report->id;

        option([
            'reporter_reward_score' => 6,
            'reporter_score_modification' => -7,
        ]);
        $score = $reporter->score;
        $texture->delete();
        $this->actingAs($admin)
            ->putJson('/admin/reports/'.$report->id, ['action' => 'delete'])
            ->assertJson([
                'code' => 0,
                'message' => trans('general.texture-deleted'),
                'data' => ['status' => Report::RESOLVED],
            ]);
        $report->refresh();
        $this->assertEquals(Report::RESOLVED, $report->status);
        $this->assertEquals($score, $reporter->score);
        Event::assertNotDispatched('texture.deleted');
        Event::assertDispatched('report.resolved', function ($event, $payload) use ($id) {
            [$report, $action] = $payload;
            $this->assertEquals($id, $report->id);
            $this->assertEquals('delete', $action);

            return true;
        });
    }

    public function testReviewBan()
    {
        Event::fake();

        $uploader = User::factory()->create();
        $reporter = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $texture = Texture::factory()->create(['uploader' => $uploader->uid]);

        $report = new Report();
        $report->tid = $texture->tid;
        $report->uploader = $uploader->uid;
        $report->reporter = $reporter->uid;
        $report->reason = 'test';
        $report->status = Report::PENDING;
        $report->save();
        $report->refresh();
        $id = $report->id;

        // uploader should be banned
        option(['reporter_reward_score' => 6]);
        $score = $reporter->score;
        $this->actingAs($admin)
            ->putJson('/admin/reports/'.$report->id, ['action' => 'ban'])
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

        // should not ban admin uploader
        $report->refresh();
        $report->status = Report::PENDING;
        $report->save();
        $uploader->refresh();
        $uploader->permission = User::ADMIN;
        $uploader->save();
        $this->putJson('/admin/reports/'.$report->id, ['action' => 'ban'])
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.users.operations.no-permission'),
            ]);
        $report->refresh();
        $this->assertEquals(Report::PENDING, $report->status);
        $this->assertEquals(User::ADMIN, $uploader->permission);
        Event::assertDispatched('report.reviewing', function ($event, $payload) use ($id) {
            [$report, $action] = $payload;
            $this->assertEquals($id, $report->id);
            $this->assertEquals('ban', $action);

            return true;
        });
        Event::assertDispatched('user.banned', function ($event, $payload) use ($uploader) {
            [$up] = $payload;
            $this->assertEquals($uploader->uid, $up->uid);

            return true;
        });
        Event::assertDispatched('report.resolved', function ($event, $payload) use ($id) {
            [$report, $action] = $payload;
            $this->assertEquals($id, $report->id);
            $this->assertEquals('ban', $action);

            return true;
        });

        // uploader has deleted its account
        $report->uploader = -1;
        $report->save();
        $this->putJson('/admin/reports/'.$report->id, ['action' => 'ban'])
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.users.operations.non-existent'),
            ]);
    }
}
