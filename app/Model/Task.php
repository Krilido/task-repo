<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    const ACTIVE = 1;
    const NON_ACTIVE = 0;
    const TODO = 0;
    const DONE = 1;

    public function section()
    {
        return $this->belongsTo('App\Model\Section','section_id','id');
    }


    public static function browseByUser($request)
    {
        $data = [];
        
        if (isset($request->section_id)) {
            $tasks = Task::where('status', Task::ACTIVE)->where('section_id',$request->section_id);
        } else{
            $tasks = Task::where('status', Task::ACTIVE);
        }

        if (isset($request->filters)) {
            $like = $request->filters;
            if (isset($like['status'])) {
                $tasks = $tasks->where('status', $like['status']);
            } elseif (isset($like['name'])) {
                $tasks = $tasks->where('name',$like['name']);
            } elseif (isset($like['progress'])) {
                $tasks = $tasks->where('progress',$like['progress']);
            }
        }

        if (isset($request->search)) {
            $like = $request->search;
            $tasks = $tasks->where(function ($query) use ($like) {
                $query->orWhere('name', 'like', '%' . $like['name'] . '%');
            }); 
        }

        $tasks = $tasks->orderBy('created_at', 'desc');

        if (!$request->has('page')) {
            $request->merge(['page' => 1]);
        }
        if (!$request->has('limit')) {
            $request->merge(['limit' => 10]);
        }

        $page = $request->input('page');
        $limit = $request->input('limit');

        $skip = ($page * $limit) - $limit;

        if (!$request->input('search')) {
            $request->merge(['search' => '']);
        }

        $now = Carbon::now();
        $total = $tasks->count();
        $data['tasks'] = $tasks->skip($skip)->take($limit)->get();
        $l_Tasks = count($data['tasks']);
        for ($i = 0; $i < $l_Tasks; $i++) {
            $data['tasks'][$i]->status       = $data['tasks'][$i]->status == Task::ACTIVE ? "Active" : "Nonactive";
            $temp_created_at                    = Carbon::createFromFormat('Y-m-d H:i:s', $data['tasks'][$i]->created_at);
            $data['tasks'][$i]->time_create   = $now->diffForHumans($temp_created_at);
            $data['tasks'][$i]->progress       = $data['tasks'][$i]->status == Task::TODO ? "To Do" : "Done";
        }
        $total_page = ceil($total / $limit);
        $data['total'] = $total;
        $data['page'] = $page;
        $data['total_page'] = $total_page;

        return $data;
    }
}
