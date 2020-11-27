<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\PostCategory;
use DataTables;
use App\Users;
use Validator;
use Auth;
use Image;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $posts = Post::where('status', '!=', 0)->orderBy('id', 'DESC');

        if ($request->ajax()) {
            return DataTables::of($posts)
            ->editColumn('status', function ($post) {
                if($post->status == 0) {
                    return status(_lang('Pending'), 'warning');
                } elseif($post->status == 1) {
                    return status(_lang('Published'), 'success');
                } elseif($post->status == 2) {
                    return status(_lang('Draft'), 'info');
                } elseif($post->status == 3) {
                    return status(_lang('Rejected'), 'danger');
                }
            })
               ->editColumn('first_name', function ($post) {
                return ($post->first_name);
            })
            ->editColumn('created_at', function ($post) {
                return date('d M, Y | h:i A', strtotime($post->created_at));
            })
            ->editColumn('views', function ($post) {
                return 0;
            })
            ->addColumn('featured_image', function ($post) {
                    return '<img class="img-sm img-thumbnail" src="' . asset('public/uploads/images/posts/' . $post->getImage('small') ) . '">';
            })
            ->addColumn('action', function($post){

                $action = '<div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            ' . _lang('Action') . '
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                $action .= '<a href="' . '#' . '" class="dropdown-item" data-title="' . _lang('Details') . '">
                            <i class="fas fa-eye"></i>
                            ' . _lang('Details') . '
                            </a>';
                $action .= '<a href="' . route('posts.edit', $post->id) . '" class="dropdown-item" data-title="' . _lang('Edit') . '">
                            <i class="fas fa-edit"></i>
                            ' . _lang('Edit') . '
                            </a>';
                if(Auth::user()->user_type == 'admin') {
                    $action .= '<form action="' . route('posts.destroy', $post->id) . '" method="post" class="ajax-delete">'
                                . csrf_field() 
                                . method_field('DELETE') 
                                . '<button type="button" class="btn-remove dropdown-item">
                                <i class="fas fa-trash-alt"></i>
                                ' . _lang('Delete') . '
                                </button>
                                </form>';
                    }
                $action .= '</div>
                            </div>';
                return $action;
            })
            ->rawColumns(['action', 'featured_image', 'status'])
            ->make(true);

        }
        return view('backend.posts.index');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pending(Request $request)
    {
        
        $posts = Post::where('status', 0)->orderBy('id', 'DESC')->get();
        
        if ($request->ajax()) {
            return DataTables::of($posts)
            ->editColumn('status', function ($post) {
                return status(_lang('Pending'), 'warning');
            })
            ->editColumn('created_at', function ($post) {
                return date('d M, Y | h:i A', strtotime($post->created_at));
            })
            ->editColumn('views', function ($post) {
                return 0;
            })
            ->addColumn('featured_image', function ($post) {
                return '<img class="img-sm img-thumbnail" src="' . asset('public/uploads/images/posts/' . $post->getImage('small') ) . '">';
            })
            ->addColumn('action', function($post){

                $action = '<div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            ' . _lang('Action') . '
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                $action .= '<a href="' . '#' . '" class="dropdown-item" data-title="' . _lang('Details') . '">
                            <i class="fas fa-eye"></i>
                            ' . _lang('Details') . '
                            </a>';
                $action .= '<a href="' . url('posts/status/1', $post->id) . '" class="dropdown-item" data-title="' . _lang('Approve') . '">
                            <i class="fas fa-edit"></i>
                            ' . _lang('Approve') . '
                            </a>';
                $action .= '<a href="' . url('posts/status/3', $post->id) . '" class="dropdown-item" data-title="' . _lang('Reject') . '">
                            <i class="fas fa-edit"></i>
                            ' . _lang('Reject') . '
                            </a>';
                $action .= '</div>
                            </div>';
                return $action;
            })
            ->rawColumns(['action', 'featured_image', 'status'])
            ->make(true);

        }
        return view('backend.posts.pending');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.posts.create');
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

            'title' => 'required|string',
          'slug' => 'required|string',
            'post_body' => 'required|string',
            'featured_image' => 'required|image' . file_settings(),
        ]);

        \DB::beginTransaction();

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }

        $post = new Post();
        $post->title = $request->title;
      $post->slug = $request->slug;
        $post->post_body = $request->post_body;
        $post->tags = $request->tags;
        $post->video_type = $request->video_type;
        $post->video_url = $request->video_url;
       $post->first_name = Auth::user()->first_name;
        $post->last_name = Auth::user()->last_name;
        $post->created_by = Auth::user()->id;
        $post->meta_description = $request->meta_description;
        $post->meta_tags = $request->meta_tags;
       $post->left1_body = $request->left1_body;
        $post->left2_body = $request->left2_body;
       $post->right1_body = $request->right1_body;
        $post->right2_body = $request->right2_body;
        if (Auth::user()->user_type == 'editor') {
            //$post->status = 0;
            $post->status = 1;
        } else {
            $post->status = $request->status;
        }
       
        $post->slug = str_replace(' ', '-', strtolower($request->slug));

        @ini_set('upload_max_filesize', '200M');
        @ini_set('post_max_size', '200M');
      
        if($request->hasFile('featured_image')){
            $file = $request->file('featured_image');
            $file_name = time() . rand() . '.' . $file->getClientOriginalExtension();

            $image = array();
            $image['orginal'] = \Image::make($file)->save(public_path("uploads/images/posts/orginal-$file_name"))->basename;
            //$image['square'] = \Image::make($file)->save(public_path("uploads/images/posts/orginal-$file_name"))->basename;
            $image['small'] = \Image::make($file)->crop(200,200)->save(public_path("uploads/images/posts/small-$file_name"))->basename;
            $image['medium'] = \Image::make($file)->save(public_path("uploads/images/posts/medium-$file_name"))->basename;
            $image['large'] = \Image::make($file)->save(public_path("uploads/images/posts/large-$file_name"))->basename;

            \Image::make($file)->fit(800, 450)->save(public_path("uploads/images/posts/pre_watermark-$file_name"))->basename;
            $img = \Image::canvas(860, 600, '#ffffff');
            $img->insert(public_path("uploads/images/posts/pre_watermark-$file_name"), 'top-center');
            $img->insert(public_path('uploads/images/watermark.png'), 'bottom-center', 0, 68);
            $img->save(public_path("uploads/images/posts/watermark-$file_name"));
            $image['watermark'] = $img->basename;
            \File::delete(public_path("uploads/images/posts/pre_watermark-$file_name"));

            $post->featured_image = json_encode($image);
        } 
        
        $post->save();

        if (count($request->categories) != 0) {
            for ($i = 0; $i < count($request->categories); $i++) {
                if ($request->categories[$i] != '') {
                    $postCategories = new PostCategory();
                    $postCategories->post_id = $post->id;
                    $postCategories->category_id = $request->categories[$i];
                    $postCategories->save();
                }
            }
        }

        //prefix
        $post->featured_image = '<img class="img-sm img-thumbnail" src="' . asset('public/uploads/images/' . $post->featured_image) . '">';


        \DB::commit();

        if (!$request->ajax()) {
            return back()->with('success', _lang('Information has been added.'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Information has been added sucessfully.'), 'data' => $user]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::find($id);
        $post_categories = PostCategory::where('post_id', $id)->pluck('category_id')->toJson();
        return view('backend.posts.edit', compact('post', 'post_categories'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function status($status, $post_id)
    {
        $post = Post::find($post_id);
        $post->status = $status;
        $post->save();
        return back()->with('success', _lang('Status has been updated.'));
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

            'title' => 'required|string',
            'slug' => 'required|string',
            'post_body' => 'required|string',
            'featured_image' => 'nullable|image' . file_settings(),
             'reporter_image' => 'nullable|image' . file_settings(),

        ]);

        \DB::beginTransaction();

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }

        $post = Post::find($id);
        $post->title = $request->title;
      $post->slug = $request->slug;
        $post->post_body = $request->post_body;
        $post->tags = $request->tags;
        $post->video_type = $request->video_type;
        $post->video_url = $request->video_url;
        $post->updated_by = Auth::user()->id;
        $post->meta_description = $request->meta_description;
        $post->meta_tags = $request->meta_tags;
          $post->left1_body = $request->left1_body;
        $post->left2_body = $request->left2_body;
       $post->right1_body = $request->right1_body;
        $post->right2_body = $request->right2_body;
        //if (Auth::user()->user_type == 'editor') {
         //   $post->status = 0;
     //   } else {
       //     $post->status = $request->status;
     //   }
  $post->slug = str_replace(' ', '-', strtolower($request->slug));
         @ini_set('upload_max_filesize', '200M');
        @ini_set('post_max_size', '200M');
        if($request->hasFile('featured_image')){
            $file = $request->file('featured_image');
            $file_name = time() . rand() . '.' . $file->getClientOriginalExtension();
            $image = array();
            $image['orginal'] = \Image::make($file)->save(public_path("uploads/images/posts/orginal-$file_name"))->basename;
            //$image['square'] = \Image::make($file)->save(public_path("uploads/images/posts/orginal-$file_name"))->basename;
            $image['small'] = \Image::make($file)->crop(200,200)->save(public_path("uploads/images/posts/small-$file_name"))->basename;
            $image['medium'] = \Image::make($file)->save(public_path("uploads/images/posts/medium-$file_name"))->basename;
            $image['large'] = \Image::make($file)->save(public_path("uploads/images/posts/large-$file_name"))->basename;
            \Image::make($file)->fit(800, 450)->save(public_path("uploads/images/posts/pre_watermark-$file_name"))->basename;
            $img = \Image::canvas(860, 600, '#ffffff');
            $img->insert(public_path("uploads/images/posts/pre_watermark-$file_name"), 'top-center');
            $img->insert(public_path('uploads/images/watermark.png'), 'bottom-center', 0, 68);
            $img->save(public_path("uploads/images/posts/watermark-$file_name"));
            $image['watermark'] = $img->basename;
            \File::delete(public_path("uploads/images/posts/pre_watermark-$file_name"));
            $post->featured_image = json_encode($image);
        } 
        $post->save();

        PostCategory::where('post_id', $id)->delete();
        if (count($request->categories) != 0) {
            for ($i = 0; $i < count($request->categories); $i++) {
                if ($request->categories[$i] != '') {
                    $postCategories = new PostCategory();
                    $postCategories->post_id = $post->id;
                    $postCategories->category_id = $request->categories[$i];
                    $postCategories->save();
                }
            }
        }

        //prefix
        $post->featured_image = '<img class="img-sm img-thumbnail" src="' . asset('public/uploads/images/' . $post->featured_image) . '">';

        \DB::commit();

        if (!$request->ajax()) {
            return redirect('posts')->with('success', _lang('Information has been updated.'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Information has been updated sucessfully.'), 'data' => $user]);
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
        $post = Post::find($id);
        $post->delete();
        PostCategory::where('post_id', $id)->delete();
        if (!$request->ajax()) {
            return back()->with('success', _lang('Information has been deleted'));
        } else {
            return response()->json(['result' => 'success', 'message' => _lang('Information has been deleted sucessfully')]);
        }
    }
}
