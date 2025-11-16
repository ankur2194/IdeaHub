<?php

namespace Tests\Feature;

use App\Events\BadgeEarned;
use App\Events\CommentCreated;
use App\Events\IdeaApproved;
use App\Events\IdeaCreated;
use App\Events\NewNotification;
use App\Events\UserLeveledUp;
use App\Models\Badge;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastEventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    public function test_new_notification_event_broadcasts_to_user_channel(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Test Notification',
            'message' => 'This is a test',
        ]);

        $event = new NewNotification($notification);

        $this->assertEquals($notification->id, $event->notification->id);
        $this->assertEquals("private-user.{$user->id}", $event->broadcastOn()[0]->name);
    }

    public function test_idea_created_event_broadcasts_to_notifications_channel(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create(['user_id' => $user->id]);

        $event = new IdeaCreated($idea);

        $this->assertEquals($idea->id, $event->idea->id);
        $this->assertEquals('notifications', $event->broadcastOn()[0]->name);
    }

    public function test_comment_created_event_broadcasts_to_idea_channel(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create(['user_id' => $user->id]);
        $comment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
        ]);

        $event = new CommentCreated($comment);

        $this->assertEquals($comment->id, $event->comment->id);
        $this->assertEquals("idea.{$idea->id}", $event->broadcastOn()[0]->name);
    }

    public function test_idea_approved_event_broadcasts_to_multiple_channels(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create(['user_id' => $user->id]);

        $event = new IdeaApproved($idea);

        $channels = $event->broadcastOn();
        $this->assertCount(2, $channels);
        $this->assertEquals("private-user.{$user->id}", $channels[0]->name);
        $this->assertEquals("idea.{$idea->id}", $channels[1]->name);
    }

    public function test_badge_earned_event_broadcasts_to_user_channel(): void
    {
        $user = User::factory()->create();
        $badge = Badge::factory()->create(['name' => 'Test Badge']);

        $event = new BadgeEarned($user, $badge);

        $this->assertEquals($user->id, $event->user->id);
        $this->assertEquals($badge->id, $event->badge->id);
        $this->assertEquals("private-user.{$user->id}", $event->broadcastOn()[0]->name);
    }

    public function test_user_leveled_up_event_broadcasts_to_user_channel(): void
    {
        $user = User::factory()->create(['level' => 5]);
        $oldLevel = 4;
        $newLevel = 5;

        $event = new UserLeveledUp($user, $oldLevel, $newLevel);

        $this->assertEquals($user->id, $event->user->id);
        $this->assertEquals($oldLevel, $event->oldLevel);
        $this->assertEquals($newLevel, $event->newLevel);
        $this->assertEquals("private-user.{$user->id}", $event->broadcastOn()[0]->name);
    }

    public function test_events_have_correct_broadcast_names(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create(['user_id' => $user->id]);
        $comment = Comment::factory()->create(['idea_id' => $idea->id, 'user_id' => $user->id]);
        $badge = Badge::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('notification.new', (new NewNotification($notification))->broadcastAs());
        $this->assertEquals('idea.created', (new IdeaCreated($idea))->broadcastAs());
        $this->assertEquals('comment.created', (new CommentCreated($comment))->broadcastAs());
        $this->assertEquals('idea.approved', (new IdeaApproved($idea))->broadcastAs());
        $this->assertEquals('badge.earned', (new BadgeEarned($user, $badge))->broadcastAs());
        $this->assertEquals('user.leveled_up', (new UserLeveledUp($user, 1, 2))->broadcastAs());
    }

    public function test_events_contain_correct_broadcast_data(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Idea',
        ]);
        $comment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'content' => 'Test Comment',
        ]);

        // Test IdeaCreated broadcast data
        $ideaEvent = new IdeaCreated($idea);
        $ideaData = $ideaEvent->broadcastWith();
        $this->assertEquals('Test Idea', $ideaData['title']);
        $this->assertEquals($user->id, $ideaData['user']['id']);

        // Test CommentCreated broadcast data
        $commentEvent = new CommentCreated($comment);
        $commentData = $commentEvent->broadcastWith();
        $this->assertEquals('Test Comment', $commentData['content']);
        $this->assertEquals($idea->id, $commentData['idea_id']);
    }
}
