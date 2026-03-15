<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use App\Models\UserRelationship;

class BpmBroadcasted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_id;
    public $bpm;
    public $time;
    public $ekg;

    public function __construct($user_id, $bpm, $time, $ekg = [])
    {
        $this->user_id = $user_id;
        $this->bpm = $bpm;
        $this->time = $time;
        $this->ekg = $ekg;
        
        // Debug logging
        \Log::info('BpmBroadcasted event created', [
            'user_id' => $user_id,
            'bpm' => $bpm,
            'time' => $time,
            'ekg_count' => count($ekg),
            'channel' => 'bpm-channel.' . $user_id
        ]);
    }

    public function broadcastOn()
    {
        // Get all connected partners for this user
        $connectedUserIds = UserRelationship::where('user_id', $this->user_id)
            ->orWhere('related_user_id', $this->user_id)
            ->pluck('user_id')
            ->merge(UserRelationship::where('user_id', $this->user_id)
                ->orWhere('related_user_id', $this->user_id)
                ->pluck('related_user_id'))
            ->unique()
            ->filter(function($id) {
                return $id != $this->user_id; // Exclude self
            });

        // Debug the relationship query
        \Log::info('UserRelationship query debug', [
            'user_id' => $this->user_id,
            'relationships_as_user' => UserRelationship::where('user_id', $this->user_id)->get()->toArray(),
            'relationships_as_related' => UserRelationship::where('related_user_id', $this->user_id)->get()->toArray(),
            'connected_user_ids' => $connectedUserIds->toArray()
        ]);

        // Create channels for all connected partners
        $channels = [];
        foreach ($connectedUserIds as $partnerId) {
            $channels[] = new Channel('bpm-channel.' . $partnerId);
        }

        // Also broadcast to the user's own channel
        $channels[] = new Channel('bpm-channel.' . $this->user_id);

        \Log::info('BpmBroadcasted broadcasting to channels', [
            'user_id' => $this->user_id,
            'channels' => $connectedUserIds->toArray(),
            'total_channels' => count($channels),
            'channel_names' => array_map(function($channel) { return $channel->name; }, $channels)
        ]);

        return $channels;
    }

    public function broadcastAs()
    {
        \Log::info('BpmBroadcasted event name', ['event_name' => 'bpm-updated']);
        return 'bpm-updated';
    }

    public function broadcastWith()
    {
        $data = [
            'user_id' => $this->user_id,
            'bpm' => $this->bpm,
            'time' => $this->time,
            'ekg' => $this->ekg,
        ];
        
        \Log::info('BpmBroadcasted payload data', $data);
        
        return $data;
    }
}
