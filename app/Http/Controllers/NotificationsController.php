<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class NotificationsController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'receiver' => 'required|in:all,normal,uid,email',
            'uid' => 'required_if:receiver,uid|nullable|integer|exists:users',
            'email' => 'required_if:receiver,email|nullable|email|exists:users',
            'title' => 'required|max:20',
            'content' => 'string|nullable',
        ]);

        $notification = new Notifications\SiteMessage($data['title'], $data['content']);

        switch ($data['receiver']) {
            case 'all':
                $users = User::all();
                break;
            case 'normal':
                $users = User::where('permission', User::NORMAL)->get();
                break;
            case 'uid':
                $users = User::where('uid', $data['uid'])->get();
                break;
            case 'email':
                $users = User::where('email', $data['email'])->get();
                break;
        }
        Notification::send($users, $notification);

        session(['sentResult' => trans('admin.notifications.send.success')]);

        return redirect('/admin');
    }

    public function all()
    {
        return auth()->user()
            ->unreadNotifications
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'],
            ]);
    }

    public function read($id)
    {
        $notification = auth()
            ->user()
            ->unreadNotifications
            ->first(fn ($notification) => $notification->id === $id);
        $notification->markAsRead();

        $converter = new GithubFlavoredMarkdownConverter();

        return [
            'title' => $notification->data['title'],
            'content' => $converter->convertToHtml($notification->data['content'] ?? ''),
            'time' => $notification->created_at->toDateTimeString(),
        ];
    }
}
