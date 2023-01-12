<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{

    public function index(Request $request)
    {
        if($request->details){
            $collection = ExpenseCategory::orderBy('level')->get();
            $categories = $collection->filter(function($c){
                $c->expense_total = $c->expenses->sum('amount');
                return $c;
            });
            return [
                'expense_categories'    =>  $categories
            ];            
        }else{
            //$collection = ExpenseCategory::orderBy('expense_category')->get();
            return [
                'expense_categories'    =>  ExpenseCategory::orderBy('level')->get()
            ];        
        }

    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'expense_category'  =>  'required|string|max:255|unique:expense_categories',
            'status'            =>  'required|integer|max:1',
            'icon'              =>  'required|string|max:255',
            'level'             =>  'required|integer',
            'is_budget'         =>  'required|integer|max:1',
            'budget_amount'     =>  'required|numeric',
        ]);

        $expense_category = ExpenseCategory::create([
            'expense_category'  =>  $fields['expense_category'],
            'status'            =>  $fields['status'],
            'icon'              =>  $fields['icon'],
            'level'             =>  $fields['level'],
            'is_budget'         =>  $fields['is_budget'],
            'budget_amount'     =>  $fields['budget_amount'],
        ]);

        return response([
            'category'  =>  $expense_category
        ], 201);
    }

    public function show($id)
    {
        $category = ExpenseCategory::find($id);
        if(!$category){
            return response([
                'message'   =>  'Category Not Found'
            ], 404);
        }
        return $category;

    }

    public function update(Request $request, $id)
    {
        $expense_category = ExpenseCategory::find($id);
        if(!$expense_category){
            return response(['message'=>'Category Not Found'], 404);
        }
        $fields = $request->validate([
            'expense_category'  =>  ['required', 'string', 'max:255', Rule::unique('expense_categories', 'expense_category')->ignore($id)],
            'status'            =>  'required|integer|max:1',
            'icon'              =>  'required|string|max:255',
            'level'             =>  'required|integer',
            'is_budget'         =>  'required|integer|max:1',
            'budget_amount'     =>  'required|numeric',
        ]);


        $expense_category->expense_category = $fields['expense_category'];
        $expense_category->status = $fields['status'];
        $expense_category->icon = $fields['icon'];
        $expense_category->level = $fields['level'];
        $expense_category->is_budget = $fields['is_budget'];
        $expense_category->budget_amount = $fields['budget_amount'];
        $expense_category->save();

        return response([
            'category'  =>  $expense_category
        ], 200);
    }

    public function destroy($id)
    {
        $expense_category = ExpenseCategory::find($id);
        if(!$expense_category){
            return response(['message'=>'Category Not Found'], 404);
        }
        $expense_category->delete();
        return response(['message'=>'Category Was Deleted']);
    }

    public function total(Request $request){
        $months = [];

        if($request->id){
            $categoryId = $request->id;
            $year = $request->year;
            if($year){
                $expenses = auth()->user()
                    ->expenses()->whereHas('category', 
                    function(Builder $query) use ($categoryId){
                        $query->where('id', '=', $categoryId);
                    })
                    ->whereYear('expense_date', $year)
                    ->sum('amount');

                for($i=1; $i<13; $i++){
                    array_push($months, [
                        'month' =>  $i,
                        'total' =>  $this->totalByMonth([
                            'id'    =>  $categoryId,
                            'month' =>  $i,
                            'year'  =>  $year
                        ])
                    ]);
                }

                return [
                    'id'        =>      $categoryId,
                    'year'      =>      $year,
                    'total'     =>      $expenses,
                    'months'    =>      $months
                ];
            }else{
                $expenses = auth()->user()->expenses()->whereHas('category', 
                    function(Builder $query) use ($categoryId){
                        $query->where('id', '=', $categoryId);
                    })->sum('amount');
                return [
                    'id'        =>      $categoryId,
                    'total'     =>      $expenses,
                    'months'    =>      $months
                ];
            }
        }
        return 0;
    }

    public function totalByMonth($parameters = []){
        $id = isset($parameters['id'])? $parameters['id']: 0;
        $month = isset($parameters['month'])? $parameters['month']: date('m');
        $year = isset($parameters['year'])? $parameters['year']: date('Y');

        $expenses = auth()->user()
                    ->expenses()->whereHas('category', 
                    function(Builder $query) use ($id){
                        $query->where('id', '=', $id);
                    })
                    ->whereYear('expense_date', $year)
                    ->whereMonth('expense_date', $month)
                    ->sum('amount');
        return $expenses;

    }
}
