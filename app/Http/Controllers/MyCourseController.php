<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\MyCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MyCourseController extends Controller
{

    public function index(Request $request)
    {
        $myCourses = MyCourse::query()->with('course');
        $userId = $request->query('user_id');
        $myCourses->when($userId, function($query) use ($userId){
            return $query->where('user_id', $userId);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'get all my courses',
            'data' => $myCourses->get()
        ]);
    }

    public function create(Request $request)
    {
        $rules = [
            'course_id' => 'required|integer',
            'user_id' => 'required|integer'
        ];

        $data = $request->all();
        $validate = Validator::make($data, $rules);
        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        $courseId = $request->input('course_id');
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }

        $userId = $request->input('user_id');
        $user = getUser($userId);
        if ($user['status'] === 'error') {
            return response()->json([
                'status' => $user['status'],
                'message' => $user['message']
            ], $user['http_code']);
        }

        $isExistMyCourse = MyCourse::where('course_id', $courseId)->where('user_id', $userId)->exists();
        if ($isExistMyCourse) {
            return response()->json([
                'status' => 'error',
                'message' => 'user already taken this course'
            ], 409);
        }

        $myCourse = MyCourse::create($data);
        return response()->json([
            'status' => 'success',
            'message' => 'my course inserted',
            'data' => $myCourse
        ]);
    }
}
