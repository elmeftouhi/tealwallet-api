<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return [
            'expenses'    =>  Expense::with('category')->where('user_id', auth()->id())->get()
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
            'expense_category_id'   =>  'required|integer',
            'amount'                =>  'required|numeric',
            'expense_date'          =>  'required|date',
            'description'          =>  'sometimes|string|max:255'
        ]);

        // Check if Expense Category passed exists
        $category = ExpenseCategory::find($fields['expense_category_id']);
        if(!$category) return response(['Category Not Found'], 404);

        //return Carbon::now()->toDateTimeString();

        $expense = Expense::create([
            'expense_category_id'  =>  $fields['expense_category_id'],
            'user_id'           =>  auth()->id(),
            'description'       =>  $request->has('description')? $request->description: $category->expense_category,
            'expense_date'      =>  $fields['expense_date'],
            'amount'            =>  $fields['amount'],
        ]);

        return response([
            'expense'  =>  $expense
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
        $expense = Expense::where('id',$id)->where('user_id', auth()->id())->first();
        if(!$expense){
            return response([
                'message'   =>  'Expense Not Found'
            ], 404);
        }
        return $expense;
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
        $expense = Expense::where('id', $id)->where('user_id', auth()->id())->first();
        if(!$expense) return response(['message'=>'Expense Not Found'], 404);

        $fields = $request->validate([
            'expense_category_id'   =>  'required|integer',
            'amount'                =>  'required|numeric',
            'expense_date'          =>  'required|date',
            'description'          =>  'sometimes|string|max:255'
        ]);
        $category = ExpenseCategory::find($fields['expense_category_id']);
        if(!$category){
            return response(['message'=>'Category Not Found'], 404);
        }
        $expense->expense_category_id = $fields['expense_category_id'];
        $expense->amount = $fields['amount'];
        $expense->expense_date = $fields['expense_date'];
        $expense->description = $request->has('description')? $request->description: $category->expense_category;
        $expense->save();

        return response([
            'expense'  =>  $expense
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
        $expense = Expense::where('id', $id)->where('user_id', auth()->id())->first();
        if(!$expense) return response(['message'=>'Expense Not Found'], 404);

        $expense->delete();
        return response(['message'=>'Expense Was Deleted']);
    }
}
