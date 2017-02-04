<?php

use Illuminate\Database\Seeder;
use App\Board;

class BoardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('boards')->delete();
        for ($i=0; $i < 100; $i++) {
            $str = "";
            for($j = 0; $j<25; $j++){
                $str .= chr(rand(65,90));
            }
            Board::create([
                'letters'   => $str,
            ]);
        }//
    }
}
