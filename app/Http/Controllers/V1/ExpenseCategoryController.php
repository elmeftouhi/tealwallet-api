<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collection = ExpenseCategory::orderBy('expense_category')->get();
        $categories = $collection->filter(function($c){
            $c->expense_total = $c->expenses->sum('amount');
            return $c;
        });
        return [
            'expense_categories'    =>  $categories
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $expense_category = ExpenseCategory::find($id);
        if(!$expense_category){
            return response(['message'=>'Category Not Found'], 404);
        }
        $expense_category->delete();
        return response(['message'=>'Category Was Deleted']);
    }
}
