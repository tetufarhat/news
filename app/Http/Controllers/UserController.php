<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::whereIn('user_type', ['admin', 'editor'])->orderBy('id', 'DESC');

        if ($request->ajax()) {
            return \DataTables::of($users)
                ->addColumn('name', function ($user) {
                    return "$user->first_name $user->last_name";
                })
                ->editColumn('user_type', function ($user) {
                    return ucfirst($user->user_type);
                })
                ->addColumn('image', function ($user) {
                    return '<img class="img-sm img-thumbnail" src="' . asset('public/uploads/images/' . $user->profile) . '">';
                })
                ->addColumn('status', function ($user) {
                    return $user->status == 1 ? status(_lang('Active'), 'success') : status(_lang('In-Active'), 'danger');
                })
                ->addColumn('action', function ($user) {
                    $action = '<form action="' . route('users.destroy', $user->id) . '" method="post" class="ajax-delete">' . csrf_field() . method_field('DELETE');
                    $action .= '<a href="' . route('users.show', $user->id) . '" class="btn btn-info btn-xs ajax-modal" data-title="' . _lang('Details') . '">
                                        <i class="mdi mdi-eye"></i>
                                    </a>';
                    $action .= '<a href="' . route('users.edit', $user->id) . '" class="btn btn-warning btn-xs ajax-modal" data-title="' . _lang('Edit') . '">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>';
                    $action .= '<button type="button" class="btn btn-danger btn-xs btn-remove" type="submit">
                                        <i class="mdi mdi-eraser"></i>
                                    </button>
                                </form>';

                    return $action;
                })
                ->setRowId(function ($user) {
                    return "row_" . $user->id;
                })
                ->rawColumns(['action', 'status', 'image'])
                ->make(true);
        }

        return view('backend.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!$request->ajax()) {
            return view('backend.users.create');
        } else {
            return view('backend.users.modal.create');
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
        $validator = \Validator::make($request->all(), [

            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:191|unique:users',
            'user_type' => 'required',
            'password' => 'required|string|min:6|confirmed',
            'profile' => 'nullable|image' . file_settings(),

        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }

        $user = new user();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = \Hash::make($request->password);
        $user->user_type = $request->user_type;

        if ($request->hasFile('profile')) {
            $image = $request->file('profile');
            $ImageName = time() . '.' . $image->getClientOriginalExtension();
            \Image::make($image)->resize(300, 300)->save(base_path('public/uploads/images/users/') . $ImageName);
            $user->profile = 'users/' . $ImageName;
        }

        $user->save();

        //prefix
        $user->image = '<img class="img-sm img-thumbnail" src="' . asset('public/uploads/images/' . $user->profile) . '">';
        $user->user_type = ucfirst($request->user_type);

        if (!$request->ajax()) {
            return redirect('users')->with('success', _lang('Information has been added.'));
        } else {
            return response()->json(['result' => 'success', 'redirect' => url('users'), 'message' => _lang('Information has been added sucessfully.'), 'data' => $user]);
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
        $user = User::find($id);

        if (!$request->ajax()) {
            return view('backend.users.show', compact('user'));
        } else {
            return view('backend.users.modal.show', compact('user'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $user = User::find($id);

        if (!$request->ajax()) {
            return view('backend.users.edit', compact('user'));
        } else {
            return view('backend.users.modal.edit', compact('user'));
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
        $validator = \Validator::make($request->all(), [

            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                Rule::unique('users')->ignore($id),
            ],
            'user_type' => 'required',
            'status' => 'required',
            'profile' => 'nullable|image' . file_settings(),

        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }

        $user = User::find($id);

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->user_type = $request->user_type;
        $user->status = $request->status;

        if ($request->hasFile('profile')) {
            $image = $request->file('profile');
            $ImageName = time() . '.' . $image->getClientOriginalExtension();
            \Image::make($image)->resize(300, 300)->save(base_path('public/uploads/images/users/') . $ImageName);
            $user->profile = 'users/' . $ImageName;
        }

        $user->save();

        //prefix
        $user->image = '<img class="img-sm img-thumbnail" src="' . asset('public/uploads/images/' . $user->profile) . '">';
        $user->user_type = ucfirst($request->user_type);
        $user->status = $user->status == 1 ? status(_lang('Active'), 'success') : status(_lang('In-Active'), 'danger');

        if (!$request->ajax()) {
            return redirect('users')->with('success', _lang('Information has updated added.'));
        } else {
            return response()->json(['result' => 'success', 'redirect' => url('users'), 'message' => _lang('Information has been updated sucessfully.'), 'data' => $user]);
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
        $user = User::find($id);
        $user->delete();

        if (!$request->ajax()) {
            return back()->with('success', _lang('Information has been deleted'));
        } else {
            return response()->json(['result' => 'success', 'message' => _lang('Information has been deleted sucessfully')]);
        }
    }
}
