<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Texture;
use App\Models\User;
use Blessing\Filter;
use Blessing\Rejection;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function submit(Request $request, Dispatcher $dispatcher, Filter $filter)
    {
        $data = $request->validate([
            'tid' => 'required|exists:textures',
            'reason' => 'required',
        ]);
        /** @var User */
        $reporter = auth()->user();
        $tid = $data['tid'];
        $reason = $data['reason'];

        $can = $filter->apply('user_can_report', true, [$tid, $reason, $reporter]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $dispatcher->dispatch('report.submitting', [$tid, $reason, $reporter]);

        if (Report::where('reporter', $reporter->uid)->where('tid', $tid)->count() > 0) {
            return json(trans('skinlib.report.duplicate'), 1);
        }

        $score = option('reporter_score_modification', 0);
        if ($score < 0 && $reporter->score < -$score) {
            return json(trans('skinlib.upload.lack-score'), 1);
        }
        $reporter->score += $score;
        $reporter->save();

        $report = new Report();
        $report->tid = $tid;
        $report->uploader = Texture::find($tid)->uploader;
        $report->reporter = $reporter->uid;
        $report->reason = $reason;
        $report->status = Report::PENDING;
        $report->save();

        $dispatcher->dispatch('report.submitted', [$report]);

        return json(trans('skinlib.report.success'), 0);
    }

    public function track()
    {
        $reports = Report::where('reporter', auth()->id())
            ->orderBy('report_at', 'desc')
            ->paginate(10);

        return view('user.report', ['reports' => $reports]);
    }

    public function manage(Request $request)
    {
        $q = $request->input('q');

        return Report::usingSearchString($q)
            ->with(['texture', 'textureUploader', 'informer'])
            ->paginate(9);
    }

    public function review(
        Report $report,
        Request $request,
        Dispatcher $dispatcher
    ) {
        $data = $request->validate([
            'action' => ['required', Rule::in(['delete', 'ban', 'reject'])],
        ]);
        $action = $data['action'];

        $dispatcher->dispatch('report.reviewing', [$report, $action]);

        if ($action == 'reject') {
            if (
                $report->informer &&
                ($score = option('reporter_score_modification', 0)) > 0 &&
                $report->status == Report::PENDING
            ) {
                $report->informer->score -= $score;
                $report->informer->save();
            }
            $report->status = Report::REJECTED;
            $report->save();

            $dispatcher->dispatch('report.rejected', [$report]);

            return json(trans('general.op-success'), 0, ['status' => Report::REJECTED]);
        }

        switch ($action) {
            case 'delete':
                /** @var Texture */
                $texture = $report->texture;
                if ($texture) {
                    $dispatcher->dispatch('texture.deleting', [$texture]);
                    Storage::disk('textures')->delete($texture->hash);
                    $texture->delete();
                    $dispatcher->dispatch('texture.deleted', [$texture]);
                } else {
                    // The texture has been deleted by its uploader
                    // We will return the score, but will not give the informer any reward
                    self::returnScore($report);
                    $report->status = Report::RESOLVED;
                    $report->save();

                    $dispatcher->dispatch('report.resolved', [$report, $action]);

                    return json(trans('general.texture-deleted'), 0, ['status' => Report::RESOLVED]);
                }
                break;
            case 'ban':
                $uploader = User::find($report->uploader);
                if (!$uploader) {
                    return json(trans('admin.users.operations.non-existent'), 1);
                }
                if (auth()->user()->permission <= $uploader->permission) {
                    return json(trans('admin.users.operations.no-permission'), 1);
                }
                $uploader->permission = User::BANNED;
                $uploader->save();
                $dispatcher->dispatch('user.banned', [$uploader]);
                break;
        }

        self::returnScore($report);
        self::giveAward($report);
        $report->status = Report::RESOLVED;
        $report->save();

        $dispatcher->dispatch('report.resolved', [$report, $action]);

        return json(trans('general.op-success'), 0, ['status' => Report::RESOLVED]);
    }

    public static function returnScore($report)
    {
        if (
            $report->status == Report::PENDING &&
            ($score = option('reporter_score_modification', 0)) < 0 &&
            $report->informer
        ) {
            $report->informer->score -= $score;
            $report->informer->save();
        }
    }

    public static function giveAward($report)
    {
        if ($report->status == Report::PENDING && $report->informer) {
            $report->informer->score += option('reporter_reward_score', 0);
            $report->informer->save();
        }
    }
}
