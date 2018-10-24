<?php

namespace App\Http\Controllers;

use App\Matches;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;


class MatchController extends Controller {

    protected $matches;

    public function __construct(Matches $matchesModel)
    {
        $this->matches = $matchesModel;
    }

    public function index() {
        return view('index');
    }

    /**
     * Returns a list of matches
     *
     * TODO it's mocked, make this work :)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function matches() {
        return response()->json($this->getMatches());
    }

    /**
     * Returns the state of a single match
     *
     * TODO it's mocked, make this work :)
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function match($id) {
        $matchFound = $this->matches->find($id);

        return response()->json([
            'id' => $matchFound->match_id,
            'name' => $matchFound->match_name,
            'next' => $matchFound->match_next,
            'winner' => $matchFound->match_winner,
            'board' => json_decode($matchFound->match_board),
        ]);
    }

    /**
     * Makes a move in a match
     *
     * TODO it's mocked, make this work :)
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function move($id) {
        $matchFound = $this->matches->find($id);
        $player = $matchFound->match_next;
        $board = json_decode($matchFound->match_board);

        $position = Input::get('position');

        if($board[$position] === 0 && $matchFound->match_winner === 0)
        {            

            $board[$position] = $player;

            $matchFound->match_board = json_encode($board);
            $matchFound->match_next = ($player % 2) ==0 ? 1 : 2;

            $winner = $this->verifyWinner($board,$player);
            $matchFound->match_winner = $winner;

            $matchFound->save();
        }


        return response()->json([
            'id' => $matchFound->match_id,
            'name' => $matchFound->match_name,
            'next' => $matchFound->match_next,
            'winner' => $matchFound->match_winner,
            'board' => $board,
        ]);
    }

    /**
     * Creates a new match and returns the new list of matches
     *
     * TODO it's mocked, make this work :)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create() {
        $newBoard = [
                0, 0, 0,
                0, 0, 0,
                0, 0, 0,
            ];

        $count = $this->matches->count();
        $new = new Matches;
        $new->match_name = 'Match'.$count++;
        $new->match_next =  rand(1,2);
        $new->match_winner = 0;
        $new->match_board = json_encode($newBoard);
        $new->save();

        return response()->json($this->getMatches());
    }

    /**
     * Deletes the match and returns the new list of matches
     *
     * TODO it's mocked, make this work :)
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id) {

        $matchFound = $this->matches->find($id);

        $matchFound->delete();

        return response()->json($this->getMatches());
    }

    /**
     * Creates a fake array of matches
     *
     * @return \Illuminate\Support\Collection
     */
    private function fakeMatches() {
        return collect([
            [
                'id' => 1,
                'name' => 'Match1',
                'next' => 2,
                'winner' => 1,
                'board' => [
                    1, 0, 2,
                    0, 1, 2,
                    0, 2, 1,
                ],
            ],
            [
                'id' => 2,
                'name' => 'Match2',
                'next' => 1,
                'winner' => 0,
                'board' => [
                    1, 0, 2,
                    0, 1, 2,
                    0, 0, 0,
                ],
            ],
            [
                'id' => 3,
                'name' => 'Match3',
                'next' => 1,
                'winner' => 0,
                'board' => [
                    1, 0, 2,
                    0, 1, 2,
                    0, 2, 0,
                ],
            ],
            [
                'id' => 4,
                'name' => 'Match4',
                'next' => 2,
                'winner' => 0,
                'board' => [
                    0, 0, 0,
                    0, 0, 0,
                    0, 0, 0,
                ],
            ],
        ]);
    }

    /**
     * get the array of matches
     *
     *
     */
    private function getMatches() {
        $getAllMatches = $this->matches->get();
        $return = [];

        foreach ($getAllMatches as $key => $match) {
            $return[] = [
                'id' => $match->match_id,
                'name' => $match->match_name,
                'next' => $match->match_next,
                'winner' => $match->match_winner,
                'board' => json_decode($match->match_board),
            ];
        }

        return $return;
    }

    /**
     * verify the winner
     */

    private function verifyWinner($board,$player){
        $winner = 0; 
        $winCombination =[
            ['0'=>'x','1' => 'x','2'=>'x'],
            ['3'=>'x','4' => 'x','5'=>'x'],
            ['6'=>'x','7' => 'x','8'=>'x'],
            ['0'=>'x','3' => 'x','6'=>'x'],
            ['1'=>'x','4' => 'x','7'=>'x'],
            ['2'=>'x','5' => 'x','8'=>'x'],
            ['0'=>'x','4' => 'x','8'=>'x'],
            ['2'=>'x','4' => 'x','6'=>'x']
        ];

        foreach ($winCombination as $key => $value) {
            $count = array_count_values(array_intersect_key($board,$value));

            if(isset($count[$player]) && $count[$player] == 3)
            {
                $winner = $player;
                break;
            }
        }
        return $winner;
    }



}