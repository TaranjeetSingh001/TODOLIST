<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        return view('tasks.index');
    }

    public function getTasks()
    {
        return response()->json(Task::all());
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:tasks']);
        $task = Task::create(['name' => $request->name, 'is_completed' => false]);
        return response()->json($task);
    }

    public function destroy($id)
    {
        Task::findOrFail($id)->delete();
        return response()->json(['message' => 'Task deleted successfully.']);
    }

    public function markAsComplete($id)
    {
        $task = Task::findOrFail($id);
        $task->is_completed = !$task->is_completed;
        $task->save();

        return response()->json(['message' => 'Task Staus Updated.']);
    }

    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        $task->name =  $request->name;
        $task->is_completed = $request->is_completed;
        $task->save();

        return response()->json($task);
    }

    public function edit($id)
    {
        $task = Task::find($id);
        return response()->json($task);
    }
}
