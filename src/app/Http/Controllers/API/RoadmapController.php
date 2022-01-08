<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Roadmap;
use App\Models\Milestone;
use App\Models\Task;
use App\Models\User;
use Exception;
use Facade\FlareClient\Http\Exception\NotFound;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RoadmapController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $user)
    {
        $roadmaps = $user->roadmaps;
        return response()->json(
            [
                'status' => 'success',
                'error' => false,
                'message' => 'Roadmaps retrieved successfully',
                'data' => $roadmaps
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:30|min:3',
            'description' => 'required|string|max:255',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:1000000',
        ]);

        if($validator->fails()){
            return $this->validationErrors($validator->errors());
        }

        try {
            $imageName = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/images');
                $image->move($destinationPath, $imageName);
            } else {
                $imageName = 'default.png';
            }

            $roadmap = Roadmap::create([
                'name' => $request->name,
                'description' => $request->description,
                'slug' => this->createUniqueSlug($request->name),
                'image' => $imageName,
                'user_id' => Auth::user()->id,
            ]);

            return response()->json([
                'status' => 'success',
                'error' => false,
                'message' => 'Roadmap created successfully',
                'roadmap' => $roadmap
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Roadmap $roadmap)
    {
        //
        return response()->json([
            'status' => 'success',
            'error' => false,
            'message' => 'Roadmap retrieved successfully',
            'roadmap' => $roadmap
        ], 200);
    }

    /**
     * Display all data about a roadmap.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function full(Roadmap $roadmap)
    {
        //Get milestones sorted by start date
        $milestones = $roadmap->milestones()->orderBy('start_date', 'asc')->get();
        $user = Auth::user();
        if($user->id == $roadmap->user_id){
            $milestones = $milestones->map(function($milestone) {
                $milestone->tasks = Task::where('milestone_id', $milestone->id)->get();
                return $milestone;
            });
        }
        return response()->json([
            'status' => 'success',
            'error' => false,
            'message' => 'Full roadmap retrieved successfully',
            'roadmap' => $roadmap,
            'milestones' => $milestones,
            'is_roadmap_owner' => $user->id == $roadmap->user_id
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Roadmap $roadmap)
    {
        //
        $user = Auth::user();
        //Check if roadmap is belong to user
        if($roadmap->user_id != $user->id){
            return response()->json([
                'status' => 'error',
                'error' => true,
                'message' => 'You are not authorized to update this roadmap'
            ], 404);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:30|min:3',
                'description' => 'required|string|max:255',
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:1000000',
            ]);

            if($validator->fails()){
                return $this->validationErrors($validator->errors());
            }

            try {
                $imageName = null;
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $imageName = time() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $imageName);
                } else {
                    $imageName = $roadmap->image;
                }

                $roadmap->update([
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'description' => $request->description,
                    'image' => $imageName,
                    'slug' => this->createUniqueSlug($request->name, $roadmap->id),
                ]);

                return response()->json([
                    'status' => 'success',
                    'error' => false,
                    'message' => 'Roadmap updated successfully',
                    'roadmap' => $roadmap
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Roadmap $roadmap)
    {
        $authUser = Auth::user();
        //Check if roadmap is belong to authUser
        if($roadmap->user_id != $authUser->id){
            return response()->json([
                'status' => 'error',
                'error' => true,
                'message' => 'You are not authorized to delete this roadmap'
            ], 404);
        } else {
            try {
                $roadmap->delete();
                return response()->json([
                    'status' => 'success',
                    'error' => false,
                    'message' => 'Roadmap deleted successfully',
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

    public function createUniqueSlug($slug, $id = null)
    {
        $slug = Str::slug($slug);
        $count = Roadmap::where('slug', $slug)->count();
        if($count > 0 && $id != null){
            $count = Roadmap::where('slug', $slug)->where('id', '!=', $id)->count();
        }
        if($count > 0){
            $slug = $slug.'-'.$count;
        }
        return $slug;
    }
}
