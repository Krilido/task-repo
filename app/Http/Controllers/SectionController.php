<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Section;
use App\Model\Task;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Validator;

class SectionController extends Controller
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
        } catch (\Throwable $th) {dd($th);
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
        //
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
    
            $sec = new Section;
            $sec->name      = $request->name;
            if (isset($request->description)) {
                $sec->description  = $request->description;
            } else{
                $sec->description  = null;
            }
            $sec->status    = Section::ACTIVE;
            $sec->save();
            if ($sec) {
                 return response()->json([
                        'code' => 200,
                        'data' => $sec,
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
        $data = Section::find($id);
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

    public function showWithTask($id)
    {
        $data = Section::with(['tasks' => function ($var)
        {
            
            $var->orderBy('created_at', 'desc');
        }])->where('id',$id)->where('status',Section::ACTIVE)->first();
        if (empty($data)) {
            return response()->json([
                'code' => 404,
                'error' => ['data not found'],
                'message' => 'Failed get data.'
            ], 200);
        } else{
            if (count($data->tasks) > 0) {
                foreach ($data->tasks as $key => $value) {
                    $data->tasks[$key]->progress = $value->progress == Task::DONE ? "Done" : "To Do";
                }
            }
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
    
            $sec = Section::find($request->id);
            if (empty($sec)) {
                return response()->json([
                    'code' => 404,
                    'error' => ['data not found'],
                    'message' => 'Failed update data.'
                ], 200);
            }
            $sec->name      = $request->name;
            if (isset($request->description) && !empty($request->description)) {
                $sec->description = $request->description;
            }
            $sec->save();
            if ($sec) {
                 return response()->json([
                        'code' => 200,
                        'data' => $sec,
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
            $data = Section::find($id);
            if (empty($data)) {
                return response()->json([
                    'code' => 404,
                    'error' => ['data not found'],
                    'message' => 'Failed delete data.'
                ], 200);
            } 
    
            if ($data->status == Section::NON_ACTIVE) {
                return response()->json([
                    'code' => 400,
                    'error' => ['data already non active'],
                    'message' => 'Failed delete data.'
                ], 200);
            }
    
            $data->status = Section::NON_ACTIVE;
            $data->save();
            if ($data) {
                Task::where('section_id',$id)->update(['status' => Task::NON_ACTIVE]);
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
