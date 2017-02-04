<?php

/**
 * The main controller of the MyLetters, a copy to the game Letterpress
 *
 * @Author RyQ & SnowZh
 * @Version 1.0
 * @Todo Onclick event by Javascript
 *
 * @return void
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use App\Http\Middleware;
use App\Record;
use Input;
use App\Word;
use App\Gamelog;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('initialize');
    }

    /**
     * Show the application dashboard.
     * Read the initialized board in Initialize.php
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /**
         * @user_id     the admin ID, same as the Game ID, same user use the same board
         * @char[][]    the letters of the board
         * @color[][]   the current color of the board
         * @counter[]   initialize the counter to count colored box
         * @status      the status of game, who turns and game over
         */
        $user = Auth::user();
        $user_id = $user->id;
        for ($i=0; $i<5 ; $i++) {
            for ($j=0; $j<5 ; $j++) {
                $unit = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                $char[$i][$j] = $unit->unit;
                $color[$i][$j] = $unit->color;
            }
        }
        $counter=[0,0];
        $status="Red Turn";
        $gamelog_word=":None";
        $gamelog_color="1";
        return view('home',compact('char','color','counter','status','gamelog_word','gamelog_color'));
    }
    public function order(Request $request){

        /**
         * Operate with the input order
         *
         * @str     the order submitted by player
         * @user_id the Game ID
         * @player  the player submitted by different button
         */
        $user = Auth::user();
        $user_id = $user->id;
        $str = $request->get('order');
        //echo "$str <br>";
        $player = $request->get('player');

        /**
         * Read status of board from Model:Record
         * Table name: boards
         *
         * @unit a temp var to get one box in specific row and line
         */
        for ($i=0; $i<5 ; $i++) {
            for ($j=0; $j<5 ; $j++) {
                $unit = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                $char[$i][$j] = $unit->unit;
                $color[$i][$j] = $unit->color;
            }
        }

        /**
         * Get letters from order, trans the [1:5] coordinates to [0:4]
         * @word append the letters from order
         */
        $word="";
        $length=strlen($str);
        //echo $length;
        if($length%4==0){
            for ($i=0; $i<$length; $i=$i+4){
                $word=$word.$char[$str[$i]-1][$str[$i+2]-1];
            }
        }
        #echo $word;

        /**
         * Game log: Store the each order and word, and check the repeat and subsequence
         * @gamelog             a table from Model: Gamelog
         * @gamelog->user_id    confirm the game ID
         * @gamelog->order      store the order
         * @gamelog->color      store which side just played this word
         * @gamelog->word       store the word, or the error code
         */
        $gamelog = new Gamelog;
        $gamelog->user_id = $user_id;
        $gamelog->order=$str;

        /**
         * Check if word not exists, change turn to the opponent
         * if exists, check if the word repeat
         * if repeats, change turn to the opponent
         * if not, store the word in Gamelog, as played, change the color and turn
         *
         * Need to re-read word and color after colored due to the error code may exist in else part.
         *
         * Error: False word!
         * error code: @gamelog->word="0"  //need a string here
         * Error: Repeat!
         * error code: @gamelog->word="1"  //need a string here
         *
         * @flag    deafault 0, if repeat, flag->1
         * @_word   all the played words
         */
        if(Word::where('words_Full', $word)->value('words_Full')==""){
            $gamelog->word="0";
            if($player==1){
                $status="Blue Turn";
                $gamelog->color="red";
            }else{
                $status="Red Turn";
                //To keep the same length of color(better for substr), use blue->blu
                $gamelog->color="blu";
            }
        }else{
            $flag=0;
            $_word=Gamelog::where('user_id',$user_id)->pluck('word');
            #echo $_word;
            for($i=0; $i<count($_word); $i=$i+1){
                if(strstr($_word,$word)!="") $flag=1;
            }
            if($flag==0){
                $gamelog->word=$word;
                #echo 'Fuck 1';
                /**
                 * Change color and turn.
                 * Judge if the box is static, not change
                 *
                 * @_color the current color after colored in this loop, need to save to cover the previous data
                 */
                for ($i=0; $i<$length; $i=$i+4){
                    if(strstr($color[$str[$i]-1][$str[$i+2]-1],'static')){
                        #echo 'static';
                    }else{
                        if($player==1){
                            $color[$str[$i]-1][$str[$i+2]-1]='red';
                            $status="Blue Turn";
                            $gamelog->color="red";
                        }else{
                            $color[$str[$i]-1][$str[$i+2]-1]='blu';
                            $status="Red Turn";
                            $gamelog->color="blu";
                        }
                        $_color = Record::where('user_id',$user_id)->where('row',$str[$i]-1)->where('line',$str[$i+2]-1)->first();
                        $_color->color=$color[$str[$i]-1][$str[$i+2]-1];
                        #echo $i;
                        $_color->save();
                        #echo $color[$str[$i]-1][$str[$i+2]-1];
                    }
                }
            }else{
                $gamelog->word="1";
                if($player==1){
                    $status="Blue Turn";
                    $gamelog->color="red";
                }else{
                    $status="Red Turn";
                    $gamelog->color="blu";
                }
            }

        }
        $gamelog->save();
        $gamelog_word=Gamelog::where('user_id',$user_id)->pluck('word');
        $gamelog_color=Gamelog::where('user_id',$user_id)->pluck('color');
        #echo $gamelog_word, $gamelog_color;

        /**
         * Check if the box is 'static'
         *
         * Need to substr all the '-static', because it may attach another '-static' after the judgement
         * Need to check the first 3 letters not the whole string, because the previous box may be attached by '-static' then can't be recgonized
         * Divide in to 9 cases, row in 0, 1:3 and 4, line in 0, 1:3 and 4
         */
        for ($i=0; $i<5 ; $i++) {
            for ($j=0; $j<5 ; $j++) {
                if(strstr($color[$i][$j],'-static')){
                    $color[$i][$j]=substr($color[$i][$j],0,3);
                    $_color = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                    $_color->color=$color[$i][$j];
                    $_color->save();
                }
                if($color[$i][$j]!=""){
                    switch($i){
                        case 0:
                            switch($j){
                                case 0:
                                    if(substr($color[$i+1][$j],0,3)==substr($color[$i][$j+1],0,3)&&$color[$i][$j]==substr($color[$i+1][$j],0,3)){
                                        $color[$i][$j]=$color[$i][$j]."-static";
                                        $_color = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                                        $_color->color=$color[$i][$j];
                                        $_color->save();
                                    }
                                    break;
                                case 4:
                                    if(substr($color[$i+1][$j],0,3)==substr($color[$i][$j-1],0,3)&&$color[$i][$j]==substr($color[$i+1][$j],0,3)){
                                        $color[$i][$j]=$color[$i][$j]."-static";
                                        $_color = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                                        $_color->color=$color[$i][$j];
                                        $_color->save();
                                    }
                                    break;
                                default:
                                    if(substr($color[$i+1][$j],0,3)==substr($color[$i][$j+1],0,3)&&substr($color[$i+1][$j],0,3)==substr($color[$i][$j-1],0,3)&&$color[$i][$j]==substr($color[$i+1][$j],0,3)){
                                        $color[$i][$j]=$color[$i][$j]."-static";
                                        $_color = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                                        $_color->color=$color[$i][$j];
                                        $_color->save();
                                    }
                                    break;
                            }
                            break;
                        case 4:
                            switch($j){
                                case 0:
                                    if(substr($color[$i-1][$j],0,3)==substr($color[$i][$j+1],0,3)&&$color[$i][$j]==substr($color[$i][$j+1],0,3)){
                                        $color[$i][$j]=$color[$i][$j]."-static";
                                        $_color = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                                        $_color->color=$color[$i][$j];
                                        $_color->save();
                                    }
                                    break;
                                case 4:
                                    if(substr($color[$i-1][$j],0,3)==substr($color[$i][$j-1],0,3)&&$color[$i][$j]==substr($color[$i-1][$j],0,3)){
                                        $color[$i][$j]=$color[$i][$j]."-static";
                                        $_color = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                                        $_color->color=$color[$i][$j];
                                        $_color->save();
                                    }
                                    break;
                                default:
                                    if(substr($color[$i-1][$j],0,3)==substr($color[$i][$j+1],0,3)&&substr($color[$i][$j-1],0,3)==substr($color[$i][$j+1],0,3)&&$color[$i][$j]==substr($color[$i][$j+1],0,3)){
                                        $color[$i][$j]=$color[$i][$j]."-static";
                                        $_color = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                                        $_color->color=$color[$i][$j];
                                        $_color->save();
                                    }
                                    break;
                            }
                            break;
                        default:
                            switch($j){
                                case 0:
                                    if(substr($color[$i-1][$j],0,3)==substr($color[$i][$j+1],0,3)&&substr($color[$i+1][$j],0,3)==substr($color[$i][$j+1],0,3)&&$color[$i][$j]==substr($color[$i][$j+1],0,3)){
                                        $color[$i][$j]=$color[$i][$j]."-static";
                                        $_color = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                                        $_color->color=$color[$i][$j];
                                        $_color->save();
                                    }
                                    break;
                                case 4:
                                    if(substr($color[$i+1][$j],0,3)==substr($color[$i][$j-1],0,3)&&substr($color[$i+1][$j],0,3)==substr($color[$i-1][$j],0,3)&&$color[$i][$j]==substr($color[$i+1][$j],0,3)){
                                        $color[$i][$j]=$color[$i][$j]."-static";
                                        $_color = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                                        $_color->color=$color[$i][$j];
                                        $_color->save();
                                    }
                                    break;
                                default:
                                    if(substr($color[$i-1][$j],0,3)==substr($color[$i][$j+1],0,3)&&substr($color[$i+1][$j],0,3)==substr($color[$i][$j+1],0,3)&&substr($color[$i][$j-1],0,3)==substr($color[$i][$j+1],0,3)&&$color[$i][$j]==substr($color[$i][$j+1],0,3)){
                                        $color[$i][$j]=$color[$i][$j]."-static";
                                        $_color = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                                        $_color->color=$color[$i][$j];
                                        $_color->save();
                                    }
                                    break;
                            }
                            break;
                    }
                }
            }
        }

        /*
         * Check if game over
         */
        $counter=[0,0];
        for ($i=0; $i<5 ; $i++) {
            for ($j=0; $j<5 ; $j++) {
                $unit = Record::where('user_id',$user_id)->where('row',$i)->where('line',$j)->first();
                $color[$i][$j] = $unit->color;
                if($color[$i][$j]!=""){
                    if(substr($color[$i][$j],0,3)=="red") $counter[0]++;
                    if(substr($color[$i][$j],0,3)=="blu") $counter[1]++;
                }
            }
        }
        if($counter[0]+$counter[1]==25){
            if($counter[0]>$counter[1]){
                $status="Red Win!";
            }else{
                $status="Blue Win!";
            }
        }

        #echo "$user_id <br>";
        #echo "$player <br>";
        return view('home',compact('char','color','counter','status','gamelog_word','gamelog_color'));
    }
}