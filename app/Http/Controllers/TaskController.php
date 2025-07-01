<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->tasks()->orderByDesc('created_at')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:255',
        ]);

        $task = $request->user()->tasks()->create([
            'text' => $data['text'],
            'done' => false,
        ]);

        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task)
    {
        // Проверка на владельца
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'text' => 'string|nullable',
            'done' => 'boolean|nullable',
        ]);

        $task->update(array_filter($data, fn($v) => !is_null($v)));

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
}
