<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Section extends Model
{
    const ACTIVE = 1;
    const NON_ACTIVE = 0;

    public static function browseByUser($request)
    {
        $data = [];
        
        $sections = Section::where('status', Section::ACTIVE)->where('user_id', $user_id);

        if (isset($request->filters)) {
            $like = $request->filters;
            if (isset($like['status'])) {
                $sections = $sections->where(function ($query) use ($like) {
                    $query->orWhere('status', $like['status']);
                });
            } elseif (isset($like['name'])) {
                $sections = $sections->where(function ($query) use ($like) {
                    $query->orWhere('name',$like['name']);
                });
            } elseif (isset($like['desc'])) {
                $sections = $sections->where(function ($query) use ($like) {
                    $query->orWhere('description',$like['desc']);
                });
            }
        }

        if (isset($request->search)) {
            $like = $request->filters;
            if (isset($like['status'])) {
                $sections = $sections->where(function ($query) use ($like) {
                    $query->orWhere('status', 'like', '%' . $like['status'] . '%');
                });
            } elseif (isset($like['name'])) {
                $sections = $sections->where(function ($query) use ($like) {
                    $query->orWhere('name', 'like', '%' . $like['name'] . '%');
                });
            } elseif (isset($like['desc'])) {
                $sections = $sections->where(function ($query) use ($like) {
                    $query->orWhere('description', 'like', '%' . $like['desc'] . '%');
                });
            }
        }

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
        $total = $sections->count();
        $data['sections'] = $sections->skip($skip)->take($limit)->get();
        $l_sections = count($data['sections']);
        for ($i = 0; $i < $l_sections; $i++) {
            $data['sections'][$i]->status       = $data['sections'][$i]->status == Section::ACTIVE ? "Active" : "Nonactive";
            $temp_created_at                    = Carbon::createFromFormat('Y-m-d H:i:s', $data['sections'][$i]->created_at);
            $data['sections'][$i]->created_at   = $now->diffForHumans($temp_created_at);
        }
        $total_page = ceil($total / $limit);
        $data['total'] = $total;
        $data['page'] = $page;
        $data['total_page'] = $total_page;

        return $data;
    }
}
