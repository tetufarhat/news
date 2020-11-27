<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utilities\Overrider;
use App\Post;
use App\PostCategory;
use App\PostView;
use App\Category;

class WebsiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::orderBy('name', 'ASC')->get();
        return view('website.index', compact('categories'));
    }

    public function single_post($slug){
        //$fb_page = json_decode("https://graph.facebook.com/DeshiITBD/?fields=fan_count&access_token=EAAl4xHDm1gQBAPj30tgrb1IYEDx5jkDZC82ONQSAyTE2ZB6HYKz2ZCjTXGtERB6hWroplRJ724MqMScAvxqVNbxuK1x8CrpGjHgtcAMMSmLAYheATbr9Vz2cDKmQZCPNFgQbU9qfJ69Ilj6jzLpG1SDDTLHiAQZAa2FsoYQfs3duIG5SBMyBRoAIiYRzbjJzTJxu7lywL5F5MeMlVb9gGEd9CiWBpdyK1TjrXF0pM2AZDZD");

        $post = Post::where('slug', $slug)->where('status', 1)->first();
        $viewPost = PostView::where('post_id', $post->id);

        if ($viewPost->exists()) {
            $countViews = $viewPost->first();
            $countViews->increment('views');
        } else {
            $postView = new PostView();
            $postView->post_id = $post->id;
            $postView->views = 1;
            $postView->save();
        }

        $categories = Category::orderBy('name', 'ASC')->get();
        $category_ids = PostCategory::where('post_id', $post->id)->pluck('category_id')->toArray();
        $posts = PostCategory::where('post_id', '!=', $post->id)->whereIn('category_id', $category_ids)->pluck('post_id')->toArray();
        $relatedPosts = Post::whereIn('id', $posts)->limit(3)->get(); 

        return view('website.post_details', compact('post', 'relatedPosts', 'categories'));
    }

    public function categoryPosts($slug)
    {
       $category = Category::where('slug', $slug)->first();
       $post_ids = PostCategory::where('category_id', $category->id)->pluck('post_id')->toArray();
       $posts = Post::whereIn('id', $post_ids)->where('status', 1)->orderBy('id', 'DESC')->paginate(21);
       $categories = Category::orderBy('name', 'ASC')->get();
       return view('website.posts_by_category', compact('category', 'posts', 'categories'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function aboutus(Request $request){
        
            return view('website.aboutus');
        }
     public function privacy(Request $request){
        
            return view('website.privacy');
        }
     public function archive(Request $request){
        
            return view('website.archive');
        }

     public function terms(Request $request){
        
            return view('website.terms');
        }

    public function contact(Request $request){
        if(! $request->isMethod('post')){
            return view('website.contact');
        }else{
            $validator = \Validator::make($request->all(), [

                'name' => 'required|string|max:191',
                'email' => 'required_without:phone|max:191',
                'phone' => 'required_without:email|max:25',
                'subject' => 'required|string|max:191',
                'message' => 'required|string|max:500',

            ]);

            if ($validator->fails()) {
                if($request->ajax()){ 
                    return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
                }else{
                    return back()->withErrors($validator)->withInput();
                }           
            }
            
            
            // $contact_message = new ContactMessage();

            // $contact_message->name = $request->name;
            // $contact_message->email = $request->email;
            // $contact_message->phone = $request->phone;
            // $contact_message->subject = $request->subject;
            // $contact_message->message = $request->message;

            // $contact_message->save();

            $mail  = new \stdClass();

            $mail->name = $request->name;
            $mail->email = $request->email;
            $mail->phone = $request->phone;
            $mail->subject = $request->subject;
            $mail->message = $request->message;

            try {
                \App\Utilities\Overrider::load('settings');
                \Mail::to(get_option('contact_email'))->send(new \App\Mail\ContactMail($mail));
            } catch (\Throwable $th) {
                //throw $th;
            }

            return back()->with('success', _lang('Your message has been sended sucessfully'));
        }
    }

    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function set_lang($lang){
        \Session::put('_lang', $lang);
        return back();
    }

    

}
