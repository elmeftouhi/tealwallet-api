<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use DateTime;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function totals(Request $request){
        $params = $request->validate([
            'year'  =>  'sometimes|digits:4|integer|min:1900|max:'.date('Y'),
        ]);
        if($request->has('year')){
            $year = $params['year'];
            return $this->getTotalByYear($year);;
        }else{
            $thisYear = date('Y');
            $date = auth()->user()->expenses()->orderBy('expense_date', 'asc')->pluck('expense_date')->first();

            if(!$date) return [];
            $d    = new DateTime($date);
            $firstYear = $d->format('Y');
            $report = [];
            for($i=$firstYear; $i<=$thisYear; $i++){
                $report[] = $this->getTotalByYear($i);
            }
            return $report;
        }
    }

    //** Get Data from expense by year */

    public function getTotalByYear($year){
        for($i=1; $i<13; $i++){
            $months[$i] = [
                'total' =>  0,
                'categories'    =>  ExpenseCategory::all()
                                        ->map(function($cat){
                                            return [
                                                'expense_category_id'   => $cat->id,
                                                'name'  =>  $cat->expense_category,
                                                'total' =>  0
                                            ];
                                        })
                                        ->toArray()
            ];
        }

        $report = [
            $year => [
                'total'     =>  0,
                'months'    =>  [],
            ],
        ];
        $total = 0;

        $expenses = auth()->user()->expenses()->with('category')->whereYear('expense_date', $year)->get();
        foreach($expenses as $exp){
            $date = $exp->expense_date;
            $d    = new DateTime($date);
            isset($months[intval($d->format('m'))])? $months[intval($d->format('m'))]['total'] += $exp->amount: $months[intval($d->format('m'))]['total'] = $exp->amount;

            foreach($months[intval($d->format('m'))]['categories'] as $k=>$c){
                if($c['expense_category_id'] == $exp->expense_category_id){
                    $months[intval($d->format('m'))]['categories'][$k]['total'] += $exp->amount;
                }
            }

            $total += $exp->amount;

        }
        $report[$year]['months'] = $months;
        $report[$year]['total'] = $total;
        return $report;
    }
}
