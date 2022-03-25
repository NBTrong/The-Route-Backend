<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Milestone;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Auth $user)
    {
        $milestones = $user->milestones;
        return response()->json(
            [
                'status' => 'success',
                'error' => false,
                'message' => 'Milestones retrieved successfully',
                'data' => $milestones
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
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|min:3',
            'description' => 'required|string|max:500',
            'start_date' => 'required|date|date_format:Y-m-d|beforeOrEqual:end_date',
            'end_date' => 'required|date|date_format:Y-m-d|afterOrEqual:start_date',
            'type' => 'required|string|in:date,month,year',
            'roadmap_id' => 'required|exists:roadmaps,id|in:'.$user->roadmaps->pluck('id')->implode(','),
        ]);

        if($validator->fails()){
            return $this->validationErrors($validator->errors());
        }

        try {
            $milestone = Milestone::create([
                'roadmap_id' => $request->roadmap_id,
                'user_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'type' => $request->type
            ]);
            return response()->json([
                'status' => 'success',
                'error' => false,
                'message' => 'Milestone created successfully',
                'milestone' => $milestone
            ], 201);
        }
        catch(Exception $e){
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

        $milestone = $user->milestones->find($id);

        if(!$milestone){
            return response()->json([
                'status' => 'error',
                'error' => true,
                'message' => 'Milestone not found'
            ], 404);
        } else {
            try{
                $milestone->update([
                    'name' => $request->name ? $request->name : $milestone->name,
                    'description' => $request->description ? $request->description : $milestone->description,
                    'start_date' => $request->start_date ? $request->start_date : $milestone->start_date,
                    'end_date' => $request->end_date ? $request->end_date : $milestone->end_date,
                    'type' => $request->type ? $request->type : $milestone->type
                ]);
                return response()->json([
                    'status' => 'success',
                    'error' => false,
                    'message' => 'Milestone updated successfully',
                    'milestone' => $milestone
                ], 200);
            }
            catch(Exception $e){
                return response()->json([
                    'status' => 'error',
                    'error' => true,
                    'message' => $e->getMessage()
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
        $milestone = $user->milestones->find($id);
        if(!$milestone){
            return response()->json([
                'status' => 'error',
                'error' => true,
                'message' => 'Milestone not found'
            ], 404);
        } else {
            $milestone->delete();
            return response()->json([
                'status' => 'success',
                'error' => false,
                'message' => 'Milestone deleted successfully'
            ], 200);
        }
    }
}
