<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Category;
use DataTables;
use Validator;

class CategoryController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $categories = Category::orderBy('id', 'DESC')->get();

        if ($request->ajax()) {
            return DataTables::of($categories)
                    ->editColumn('status', function ($category) {
                        return $category->status == 1 ? status(_lang('Active'), 'success') : status(_lang('In-Active'), 'danger');
                    })
                    ->addColumn('action', function($category){

                        $action = '<div class="dropdown">
                                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            ' . _lang('Action') . '
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                        $action .= '<a href="' . route('categories.show', $category->id) . '" class="dropdown-item ajax-modal" data-title="' . _lang('Details') . '">
                                        <i class="fas fa-eye"></i>
                                        ' . _lang('Details') . '
                                    </a>';
                        $action .= '<a href="' . route('categories.edit', $category->id) . '" class="dropdown-item ajax-modal" data-title="' . _lang('Edit') . '">
                                        <i class="fas fa-edit"></i>
                                        ' . _lang('Edit') . '
                                    </a>';
                        $action .= '<form action="' . route('categories.destroy', $category->id) . '" method="post" class="ajax-delete">'
                                    . csrf_field() 
                                    . method_field('DELETE') 
                                    . '<button type="button" class="btn-remove dropdown-item">
                                            <i class="fas fa-trash-alt"></i>
                                            ' . _lang('Delete') . '
                                        </button>
                                    </form>';
                        $action .= '</div>
                                </div>';
                        return $action;
                    })
                    ->rawColumns(['action', 'status'])
                    ->make(true);
        }

        return view('backend.categories.index');
    }


    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create(Request $request)
    {
        if( ! $request->ajax()){
            return view('backend.categories.create');
        }else{
            return view('backend.categories.modal.create');
        }
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
           'name' => 'required|string|max:191',
           'slug' => 'required|string|max:191',
           'description' => 'nullable|string',
           'parent_id' => 'nullable|numeric|digits_between:0,11',
           'status' => 'required|numeric|digits_between:0,11',

        ]);

        if ($validator->fails()) {
            if($request->ajax()){ 
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            }else{
                return back()->withErrors($validator)->withInput();
            }			
        }

        $category = new Category();
        
        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->description = $request->description;
        $category->parent_id = $request->parent_id;
        $category->status = $request->status;

        $category->save();

        //prefix
        $category->status = $category->status == 1 ? status(_lang('Active'), 'success') : status(_lang('In-Active'), 'danger');

        if(! $request->ajax()){
            return back()->with('success', _lang('Information has been added sucessfully.'));
        }else{
            return response()->json(['result' => 'success', 'redirect' => url('categories'), 'message' => _lang('Information has been added sucessfully.'), 'data' => $category]);
        }
    }


    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show(Request $request, $id)
    {
        $category = Category::find($id);
        if(! $request->ajax()){
            return view('backend.categories.show', compact('category'));
        }else{
            return view('backend.categories.modal.show', compact('category'));
        } 
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit(Request $request,$id)
    {
        $category = Category::find($id);
        if(! $request->ajax()){
            return view('backend.categories.edit', compact('category'));
        }else{
            return view('backend.categories.modal.edit', compact('category'));
        }  
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
        $validator = Validator::make($request->all(), [
            
           'name' => 'required|string|max:191',
           'slug' => 'required|string|max:191',
           'description' => 'nullable|string',
           'parent_id' => 'nullable|numeric|digits_between:0,11',
           'status' => 'required|numeric|digits_between:0,11',

        ]);

        if ($validator->fails()) {
            if($request->ajax()){ 
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            }else{
                return back()->withErrors($validator)->withInput();
            }			
        }

        $category = Category::find($id);
        
        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->description = $request->description;
        $category->parent_id = $request->parent_id;
        $category->status = $request->status;

        $category->save();

        //prefix
        $category->status = $category->status == 1 ? status(_lang('Active'), 'success') : status(_lang('In-Active'), 'danger');

        if(! $request->ajax()){
            return redirect('categories')->with('success', _lang('Information has been updated sucessfully.'));
        }else{
            return response()->json(['result' => 'success', 'redirect' => url('categories'), 'message' => _lang('Information has been updated sucessfully.'), 'data' => $category]);
        }
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy(Request $request, $id)
    {
        $category = Category::find($id);
        $category->delete();
        
        if(! $request->ajax()){
            return redirect('categories')->with('success', _lang('Information has been deleted sucessfully.'));
        }else{
            return response()->json(['result' => 'success', 'message' => _lang('Information has been deleted sucessfully.')]);
        }
    }
}
