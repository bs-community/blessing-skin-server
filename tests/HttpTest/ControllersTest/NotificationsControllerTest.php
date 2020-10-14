<?php

namespace Tests;

use App\Models\User;
use App\Notifications;
use Illuminate\Support\Facades\Notification;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class NotificationsControllerTest extends TestCase
{
    public function testSend()
    {
        $admin = User::factory()->admin()->create();
        $normal = User::factory()->create();
        Notification::fake();

        $this->actingAs($admin)
            ->post('/admin/notifications/send', [
                'receiver' => 'all',
                'title' => 'all users',
                'content' => null,
            ])
            ->assertRedirect('/admin')
            ->assertSessionHas('sentResult', trans('admin.notifications.send.success'));
        Notification::assertSentTo(
            [$admin, $normal],
            Notifications\SiteMessage::class,
            function ($notification) {
                $this->assertEquals('all users', $notification->title);

                return true;
            }
        );

        Notification::fake();
        Notification::assertNothingSent();
        $this->post('/admin/notifications/send', [
            'receiver' => 'normal',
            'title' => 'normal only',
            'content' => 'hi',
        ]);
        Notification::assertSentTo(
            $normal,
            Notifications\SiteMessage::class,
            function ($notification) {
                $this->assertEquals('normal only', $notification->title);
                $this->assertEquals('hi', $notification->content);

                return true;
            }
        );
        Notification::assertNotSentTo($admin, Notifications\SiteMessage::class);

        Notification::fake();
        Notification::assertNothingSent();
        $this->post('/admin/notifications/send', [
            'receiver' => 'uid',
            'title' => 'uid',
            'content' => null,
            'uid' => $normal->uid,
        ]);
        Notification::assertSentTo(
            $normal,
            Notifications\SiteMessage::class,
            function ($notification) {
                $this->assertEquals('uid', $notification->title);

                return true;
            }
        );
        Notification::assertNotSentTo($admin, Notifications\SiteMessage::class);

        Notification::fake();
        Notification::assertNothingSent();
        $this->post('/admin/notifications/send', [
            'receiver' => 'email',
            'title' => 'email',
            'content' => null,
            'email' => $normal->email,
        ]);
        Notification::assertSentTo(
            $normal,
            Notifications\SiteMessage::class,
            function ($notification) {
                $this->assertEquals('email', $notification->title);

                return true;
            }
        );
        Notification::assertNotSentTo($admin, Notifications\SiteMessage::class);
    }

    public function testAll()
    {
        $user = User::factory()->create();

        $notification = new Notifications\SiteMessage('title', 'content');
        Notification::send([$user], $notification);

        $id = $user->unreadNotifications->first()->id;

        $this->actingAs($user, 'oauth')
            ->getJson('/api/user/notifications')
            ->assertJson([['id' => $id, 'title' => 'title']]);
    }

    public function testRead()
    {
        $user = User::factory()->create();
        $user->notify(new Notifications\SiteMessage('Hyouka', 'Kotenbu?'));
        $user->refresh();
        $notification = $user->unreadNotifications->first();

        $this->actingAs($user)->get('/user')->assertSee('Hyouka');

        $converter = new GithubFlavoredMarkdownConverter();
        $this->postJson('/user/notifications/'.$notification->id)
            ->assertJson([
                'title' => $notification->data['title'],
                'content' => $converter->convertToHtml($notification->data['content']),
                'time' => $notification->created_at->toDateTimeString(),
            ]);
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }
}
