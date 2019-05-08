<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use App\Models\Texture;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function submit(Request $request)
    {
        $data = $this->validate($request, [
            'tid' => 'required|exists:textures',
            'reason' => 'required',
        ]);
        $reporter = auth()->user();

        if (Report::where('reporter', $reporter->uid)->where('tid', $data['tid'])->count() > 0) {
            return json(trans('skinlib.report.duplicate'), 1);
        }

        $score = option('reporter_score_modification', 0);
        if ($score < 0 && $reporter->score < -$score) {
            return json(trans('skinlib.upload.lack-score'), 1);
        }
        $reporter->score += $score;
        $reporter->save();

        $report = new Report;
        $report->tid = $data['tid'];
        $report->uploader = Texture::find($data['tid'])->uploader;
        $report->reporter = $reporter->uid;
        $report->reason = $data['reason'];
        $report->status = Report::PENDING;
        $report->save();

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

    public function review(Request $request)
    {
        $data = $this->validate($request, [
            'id' => 'required|exists:reports',
            'action' => ['required', Rule::in(['delete', 'ban', 'reject'])],
        ]);
        $report = Report::find($data['id']);

        if ($data['action'] == 'reject') {
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

            return json(trans('general.op-success'), 0, ['status' => Report::REJECTED]);
        }

        switch ($data['action']) {
            case 'delete':
                $report->texture->delete();
                break;
            case 'ban':
                if (auth()->user()->permission <= $report->informer->permission) {
                    return json(trans('admin.users.operations.no-permission'), 1);
                }
                $report->informer->permission = User::BANNED;
                $report->informer->save();
                break;
        }

        if ($report->status == Report::PENDING) {
            if (($score = option('reporter_score_modification', 0)) < 0) {
                $report->informer->score -= $score;
            }
            $report->informer->score += option('reporter_reward_score', 0);
            $report->informer->save();
        }

        $report->status = Report::RESOLVED;
        $report->save();

        return json(trans('general.op-success'), 0, ['status' => Report::RESOLVED]);
    }
}
