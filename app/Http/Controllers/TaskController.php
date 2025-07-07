<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        Task::where('user_id', $userId)
            ->where('status', 2)
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '<', now()->subDays(7))
            ->delete();

        $tasks = Cache::remember("user:{$userId}:tasks", now()->addMinutes(10), function () use ($request) {
            return $request->user()->tasks()
                ->orderByDesc('created_at')
                ->get();
        });

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:255',
            'color' => 'nullable|string|size:7',
        ]);

        $task = $request->user()->tasks()->create([
            'text' => $data['text'],
            'done' => false,
            'status' => 0,
            'color' => $data['color'] ?? null,
        ]);

        Cache::forget("user:{$request->user()->id}:tasks");

        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'text' => 'string|nullable',
            'done' => 'boolean|nullable',
            'status' => 'integer|in:0,1,2|nullable',
            'color' => 'string|size:7|nullable',
            'deleted_at' => 'date|nullable',
        ]);

        if (isset($data['status']) && $data['status'] == 2) {
            $data['deleted_at'] = now();
        }

        $task->fill($data)->save();

        Cache::forget("user:{$request->user()->id}:tasks");

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $task->delete();

        Cache::forget("user:{$request->user()->id}:tasks");

        return response()->json(['message' => 'Task permanently deleted']);
    }

    public function trash(Request $request)
    {
        $tasks = $request->user()->tasks()
            ->where('status', 2)
            ->orderByDesc('deleted_at')
            ->get();

        return response()->json($tasks);
    }

    public function restore(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id || $task->status !== 2) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $task->update([
            'status' => 0,
            'deleted_at' => null,
        ]);

        Cache::forget("user:{$request->user()->id}:tasks");

        return response()->json(['message' => 'Task restored']);
    }

    public function clearTrash(Request $request)
    {
        $request->user()->tasks()
            ->where('status', 2)
            ->delete();

        Cache::forget("user:{$request->user()->id}:tasks");

        return response()->json(['message' => 'Trash cleared']);
    }
}

