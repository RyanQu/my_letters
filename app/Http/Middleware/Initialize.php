<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Board;
use App\Record;
use App\User;

class Initialize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user->state == 0) {
            $seed_list = Board::pluck('letters');
            $seed_num = rand(0,sizeof($seed_list));
            $seed = $seed_list[$seed_num];
            for ($i=0; $i<5 ; $i++) {
                for ($j=0; $j<5 ; $j++) {
                    // 新建数据记录请参照此处，下一行注释可进行数据修改，修改的其他步骤同新建记录
                    // $record = Record::find($id);
                    $record = new Record;
                    $record->user_id = $user->id;
                    $record->line = $i;
                    $record->row = $j;
                    $num = $i*4 + $j;
                    $record->unit = $seed[$num];
                    $record->save();
                }
            }
            $user_unit = User::find($user->id);
            $user_unit->state = 1;
            $user_unit->save();
        }
        // var_dump("$request");
        return $next($request);
    }
}
