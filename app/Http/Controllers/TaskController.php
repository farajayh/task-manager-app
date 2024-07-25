<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;
use App\Http\Requests\TaskRequest;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

use App\Enums\TaskStatus;
use App\Events\TaskEvent;

class TaskController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(middleware: 'auth:api', except: ['index', 'show']),
        ];
    }

    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Task::simplePaginate(10);

        return response()->json([
            'status'  => true,
            'message' => "Success",
            'data'    => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest  $request)
    {
        //validate input using TaskRequest form request
        $validated = $request->validated();

        $task = new Task();
        $task->title = $validated['title'];
        $task->description = $validated['description'];
        $task->due_date = $validated['due_date'];

        //set user_id to logged in user
        $task->user_id = Auth::user()->id;

        if($task->save()){
            $response = [
                'status'  => true,
                'message' => "New task created successfully",
                'data'    => $task->only(['id', 'title', 'description',  'due_date'])
            ];

            return response()->json($response, 201);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return response()->json([
            'status'  => true,
            'message' => "Success",
            'data'    => $task
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        //check if user is authorized to update, only the creator of the task is allowed to update
        if (! Gate::allows('update-delete-task', $task)) {
            return response()->json([
                'status'  => false,
                'message' => "Unauthorized Action"
            ], 403);
        }

        //validate input using TaskRequest form request
        $validated = $request->validated();

        //set the values to be updated based on the available request fields
        foreach ($validated as $key => $value) {
            $task->{$key} = $value;
        }

        //if status is set to completed, set date_completed to current date, else set  date_completed to null
        if($task->status == TaskStatus::COMPLETED->value){
            $task->date_completed = date('Y-m-d');
        }else{
            $task->date_completed = null;
        }

        if($task->save()){
            $response = [
                'status'  => true,
                'message' => "Task was updated successfully",
                'data'    => $task
            ];
    

            return response()->json($response, 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //check if user is authorized to delete, only the creator of the task is allowed to delete
        if (! Gate::allows('update-delete-task', $task)) {
            return response()->json([
                'status'  => false,
                'message' => "Unauthorized Action"
            ], 403);
        }

        $task_data = $task;

        if($task->delete()){
            $response = [
                'status'  => true,
                'message' => "Task was deleted successfully",
                'data' => $task_data
            ];
    
            return response()->json([
                'status'  => true,
                'message' => "Success",
            ], 200);
        }    
    }
}
