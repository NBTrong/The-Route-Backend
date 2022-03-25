<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Exception;
use Facade\FlareClient\Http\Exception\NotFound;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Auth $user)
    {
        if($user->id != Auth::user()->id){
            return response()->json(
                [
                    'status' => 'error',
                    'error' => true,
                    'message' => 'You are not authorized to view this resource',
                    'data' => null
                ], 401
            );
        }
        $tasks = $user->tasks;
        return response()->json(
            [
                'status' => 'success',
                'error' => false,
                'message' => 'Tasks retrieved successfully',
                'data' => $tasks
            ], 200
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'roadmap_id' => 'required|integer|exists:roadmaps,id|in:'.$user->roadmaps->pluck('id')->implode(','),
            'milestone_id' => 'required|integer|exists:milestones,id|in:'.$user->milestones->pluck('id')->implode(','),
            'content' => 'required|string|max:100|min:1',
            'start_date' => 'required|date|date_format:Y-m-d|beforeOrEqual:end_date',
            'end_date' => 'required|date|date_format:Y-m-d|afterOrEqual:start_date'
        ]);

        if ($validator->fails()) {
            return $this->validationErrors($validator->errors());
        }

        try {
            $task = Task::create([
                'roadmap_id' => $request->roadmap_id,
                'milestone_id' => $request->milestone_id,
                'user_id' => $user->id,
                'content' => $request->content,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'completed' => false,
                'note' => $request->note
            ]);

            return response()->json([
                'status' => 'success',
                'error' => false,
                'message' => 'Task created successfully',
                'task' => $task
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => true,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $user = Auth::user();
        $task = $user->tasks->find($id);

        if(!$task){
            return response()->json([
                'status' => 'error',
                'error' => true,
                'message' => 'Task not found'
            ], 404);
        } else {
            try {
                $task->update([
                    'content' => $request->content ? $request->content : $task->content,
                    'start_date' => $request->start_date ? $request->start_date : $task->start_date,
                    'end_date' => $request->end_date ? $request->end_date : $task->end_date,
                    'completed' => $request->completed === null ? $task->completed : $request->completed,
                    'note' => $request->note ? $request->note : $task->note
                ]);

                return response()->json([
                    'status' => 'success',
                    'error' => false,
                    'message' => 'Task updated successfully',
                    'task' => $task,
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'error' => true,
                    'message' => $e->getMessage(),
                    'task' => $request->completed
                ], 404);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $user = Auth::user();
        $task = $user->tasks->find($id);

        if(!$task){
            return response()->json([
                'status' => 'error',
                'error' => true,
                'message' => 'Task not found'
            ], 404);
        } else {
            try {
                $task->delete();

                return response()->json([
                    'status' => 'success',
                    'error' => false,
                    'message' => 'Task deleted successfully'
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'error' => true,
                    'message' => $e->getMessage()
                ], 404);
            }
        }
    }
}
