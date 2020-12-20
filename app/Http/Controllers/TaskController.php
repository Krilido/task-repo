<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Section;
use App\Model\Task;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $data = Section::browseByUser($request);
            return response()->json([
                    'code' => 200,
                    'data' => $data,
                    'message' => 'Successfully get data.'
                ], 200);
        } catch (\Throwable $th) {
             return response()->json([
                    'code' => 500,
                    'error' => ['some error acquired, please contact admin'],
                    'message' => 'Failed get data.'
                ], 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|max:200'
            ];
            $validator = Validator::make($request->all(), $rules);
            if (!$validator->passes()) {
                $data = $this->get_error_from_validation($validator->errors()->all());
                return response()->json(['code' => 400,'error' => $data, 'message' => "error in validation"], 200);
            }
    
            $task = new Task;
            $task->name      = $request->name;
            if (isset($request->description)) {
                $task->description  = $request->description;
            } else{
                $task->description  = null;
            }
            $task->status    = Task::ACTIVE;
            $task->progress  = Task::TODO;
            $task->save();
            if ($task) {
                 return response()->json([
                        'code' => 200,
                        'data' => $task,
                        'message' => 'Successfully save data.'
                    ], 200);
            } else{
                return response()->json([
                    'code' => 400,
                    'error' => ['some error acquired, please contact admin'],
                    'message' => 'Failed save data.'
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'code' => 400,
                'error' => ['some error acquired, please contact admin'],
                'message' => 'Failed save data.'
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Task::find($id);
        if (empty($data)) {
            return response()->json([
                'code' => 404,
                'error' => ['data not found'],
                'message' => 'Failed get data.'
            ], 200);
        } else{
            return response()->json([
                'code' => 200,
                'data' => $data,
                'message' => 'Success get data.'
            ], 200);
        }
    }

    public function showWithSection($id)
    {
        $data = Task::with('section')->where('id',$id)->first();
        if (empty($data)) {
            return response()->json([
                'code' => 404,
                'error' => ['data not found'],
                'message' => 'Failed get data.'
            ], 200);
        } else{
            $data->status       = $data->status == Task::ACTIVE ? "Active" : "Nonactive";
            $temp_created_at    = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at);
            $data->created_at   = $Carbon::now()->diffForHumans($temp_created_at);
            $data->progress     = $data->status == Task::TODO ? "To Do" : "Done";
            return response()->json([
                'code' => 200,
                'data' => $data,
                'message' => 'Success get data.'
            ], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
    }

    public function setStatus($id)
    {
        try {
            $data = Task::find($id);
            if (empty($data)) {
                return response()->json([
                    'code' => 404,
                    'error' => ['data not found'],
                    'message' => 'Failed change status data.'
                ], 200);
            } 
    
            if ($data->status == Task::NON_ACTIVE) {
                return response()->json([
                    'code' => 400,
                    'error' => ['data already non active'],
                    'message' => 'Failed change status data.'
                ], 200);
            }
    
            if ($data->progress == Task::TODO) {
                $data->progress = Task::DONE;
                $data->save();
                return response()->json([
                    'code' => 200,
                    'data' => $task,
                    'message' => 'Successfully change status data.'
                ], 200);
            }elseif ($data->progress == Task::DONE) {
                $data->progress = Task::TODO;
                $data->save();
                return response()->json([
                    'code' => 200,
                    'data' => $task,
                    'message' => 'Successfully change status data.'
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'code' => 400,
                'error' => ['some error acquired, please contact admin'],
                'message' => 'Failed change status data.'
            ], 200);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|max:200',
                'id' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if (!$validator->passes()) {
                $data = $this->get_error_from_validation($validator->errors()->all());
                return response()->json(['code' => 400,'error' => $data, 'message' => "error in validation"], 200);
            }
    
            $task = Task::find($request->id);
            if (empty($task)) {
                return response()->json([
                    'code' => 404,
                    'error' => ['data not found'],
                    'message' => 'Failed update data.'
                ], 200);
            }
            $task->name      = $request->name;
            if (isset($request->description) && !empty($request->description)) {
                $task->description = $request->description;
            }
            $task->save();
            if ($task) {
                return response()->json([
                    'code' => 200,
                    'data' => $task,
                    'message' => 'Successfully update data.'
                ], 200);
            } else{
                return response()->json([
                    'code' => 400,
                    'error' => ['some error acquired, please contact admin'],
                    'message' => 'Failed update data.'
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'code' => 400,
                'error' => ['some error acquired, please contact admin'],
                'message' => 'Failed update data.'
            ], 200);
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
        try {
            DB::beginTransaction();
            $data = Task::find($id);
            if (empty($data)) {
                return response()->json([
                    'code' => 404,
                    'error' => ['data not found'],
                    'message' => 'Failed delete data.'
                ], 200);
            } 
    
            if ($data->status == Task::NON_ACTIVE) {
                return response()->json([
                    'code' => 400,
                    'error' => ['data already non active'],
                    'message' => 'Failed delete data.'
                ], 200);
            }
    
            $data->status = Task::NON_ACTIVE;
            $data->save();
            if ($data) {
                DB::commit();
                return response()->json([
                    'code' => 200,
                    'data' => ['Success delete Data'],
                    'message' => 'Success delete data.'
                ], 200);
            } else{
                DB::rollback();
            }
        } catch (\Throwable $th) {
            Log::info($th);
            DB::rollback();
            return response()->json([
                'code' => 400,
                'error' => ['some error acquire, please contact your admin'],
                'message' => 'Failed delete data.'
            ], 200);
        }
    }
}
