<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Texture;
use App\Models\User;
use Blessing\Filter;
use Blessing\Rejection;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function submit(Request $request, Dispatcher $dispatcher, Filter $filter)
    {
        $data = $this->validate($request, [
            'tid' => 'required|exists:textures',
            'reason' => 'required',
        ]);
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
        return Report::where('reporter', auth()->id())
            ->orderBy('report_at', 'desc')
            ->get();
    }

    public function manage(Request $request)
    {
        $search = $request->input('search', '');
        $sortField = $request->input('sortField', 'report_at');
        $sortType = $request->input('sortType', 'desc');
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);

        $reports = Report::where('tid', 'like', '%'.$search.'%')
                        ->orWhere('reporter', 'like', '%'.$search.'%')
                        ->orWhere('reason', 'like', '%'.$search.'%')
                        ->orderBy($sortField, $sortType)
                        ->offset(($page - 1) * $perPage)
                        ->limit($perPage)
                        ->get()
                        ->makeHidden(['informer'])
                        ->map(function ($report) {
                            $uploader = User::find($report->uploader);
                            if ($uploader) {
                                $report->uploaderName = $uploader->nickname;
                            }
                            if ($report->informer) {
                                $report->reporterName = $report->informer->nickname;
                            }

                            return $report;
                        });

        return [
            'totalRecords' => Report::count(),
            'data' => $reports,
        ];
    }

    public function review(Request $request, Dispatcher $dispatcher)
    {
        $data = $this->validate($request, [
            'id' => 'required|exists:reports',
            'action' => ['required', Rule::in(['delete', 'ban', 'reject'])],
        ]);
        $action = $data['action'];
        $report = Report::find($data['id']);

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
                $texture = $report->texture;
                if ($texture) {
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
        if ($report->status == Report::PENDING && ($score = option('reporter_score_modification', 0)) < 0) {
            $report->informer->score -= $score;
            $report->informer->save();
        }
    }

    public static function giveAward($report)
    {
        if ($report->status == Report::PENDING) {
            $report->informer->score += option('reporter_reward_score', 0);
            $report->informer->save();
        }
    }
}
