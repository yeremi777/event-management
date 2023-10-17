<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Http\Traits\CanLoadRelationships;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['user', 'attendees', 'attendees.user'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('throttle:api')->only(['store', 'update', 'destroy']);
        $this->authorizeResource(Event::class, 'event');
    }

    public function index()
    {
        $event = $this->loadRelationships(Event::query());

        return EventResource::collection($event->latest()->paginate());
    }


    public function store(Request $request)
    {
        $event = Event::create([
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time'
            ]),
            'user_id' => $request->user()->id
        ]);

        return new EventResource($this->loadRelationships($event));
    }

    public function show(Event $event)
    {
        return new EventResource($this->loadRelationships($event));
    }

    public function update(Request $request, Event $event)
    {
        $event->update(
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time'
            ]),
        );

        return new EventResource($this->loadRelationships($event));
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response(status: 204);
    }
}
