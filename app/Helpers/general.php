<?php

if ( ! function_exists('_lang')){
	function _lang($string = ''){

		$action = app('request')->route()->getAction();
		$controller = class_basename($action['controller']);
        $target_lang = '';

		if(explode('@', $controller)[0] == 'WebsiteController'){
            $target_lang = session('_lang');
		}else{
			$target_lang = get_lang();
        }	
		
		if($target_lang == ''){
			$target_lang = "language";
		}
		
		if(file_exists(resource_path() . "/_lang/$target_lang.php")){
			include(resource_path() . "/_lang/$target_lang.php"); 
		}else{
			include(resource_path() . "/_lang/language.php"); 
		}
		
		if (array_key_exists($string,$language)){
			return $language[$string];
		}else{
			return $string;
		}
	}
}

if ( ! function_exists('get_lang')){
	function get_lang($string = ''){
		$set_lang = Session::get('_lang');
		$default_lang = get_option('language');
		$lang = (($set_lang != '') ? $set_lang : $default_lang);
		return $lang;
	}
}

if ( ! function_exists('get_category_widget')){
	function get_category_widget($slug = '', $title){
	    
	    $category = App\Category::where('slug', $slug)->first();
	    
	    if(! $category){
	        return;
	    }
	    
	    $post_ids = App\PostCategory::where('category_id', $category->id)->pluck('post_id')->toArray();
        $posts = App\Post::whereIn('id', $post_ids)->orderBy('id', 'DESC')->get();
        
        $widget = '<div class="single-sidebar-widget p-30">
                <div class="section-heading">
                    <h5>' . $title . '</h5>
                </div>';
        foreach($posts AS $post){
            $widget .= '<div class="single-blog-post d-flex">
                    <div class="post-thumbnail">
                        <img src="' . asset('public/uploads/images/posts/' . $post->getImage('small') ) . '" alt="">
                    </div>
                    <div class="post-content">
                        <a href="' . url('post', $post->slug) . '" class="post-title">' . $post->title . '</a>
                        <div class="post-meta d-flex justify-content-between">
                            <a href="' . url('post', $post->slug) . '"><i class="fa fa-eye" aria-hidden="true"></i> ' . $post->getViews->views . ' </a>
                        </div>
                    </div>
                </div>';
            $widget .= '</div>';
        }
        
		
		return $widget;
	}
}

if (!function_exists('buildTree')) {

    function buildTree($object, $currentParent, $url, $currLevel = 0, $prevLevel = -1)
    {
        foreach ($object as $category) {
            if ($currentParent == $category->parent_id) {
                if ($currLevel > $prevLevel) {
                    echo "<ul class='menutree'>";
                }
                if ($currLevel == $prevLevel) {
                    echo "</li>";
                }
                echo '<li> <label class="menu_label" for=' . $category->id . '><a href="' . route($url, $category->id) . '" class="ajax-modal" title="' . _lang('Details') . '">' . $category->name . '</a><a href="' . route($url, $category->id) . '" class="btn btn-warning btn-xs float-right">' . _lang('Edit') . '</a></label>';
                if ($currLevel > $prevLevel) {
                    $prevLevel = $currLevel;
                }
                $currLevel++;
                buildTree($object, $category->id, $url, $currLevel, $prevLevel);
                $currLevel--;
            }
        }
        if ($currLevel == $prevLevel) {
            echo "</li> </ul>";
        }
    }
}

if (!function_exists('settings')) {
    function settings($name, $value = '')
    {
        $setting = \App\Setting::where('name', $name)->first();
        if (! $setting) {
            $setting = new \App\Setting();
            $setting->name = $name;
            $setting->value = $value;
            $setting->save();
            return $setting;
        }

        $setting->value = $value;
        $setting->save();
        return $setting;
    }
}

if (!function_exists('create_option')) {
    function create_option($table = '', $value = '', $show = '', $selected = '', $where = null)
    {
        if ($where != null) {
            $results = DB::table($table)->where($where)->orderBy('id', 'DESC')->get();
        } else {
            $results = DB::table($table)->orderBy('id', 'DESC')->get();
        }
        $option = '';
        foreach ($results as $data) {
            if ($data->$value == $selected) {
                $option .= '<option value="' . $data->$value . '" selected>' . $data->$show . '</option>';
            } else {
                $option .= '<option value="' . $data->$value . '">' . $data->$show . '</option>';
            }
        }
        echo $option;
    }
}

if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false)
    {
        $now = new \DateTime;
        $ago = new \DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}

if (!function_exists('get_table')) {
    function get_table($table, $where = null, $order = 'DESC')
    {
        if ($where != null) {
            $results = DB::table($table)->where($where)->orderBy('id', $order)->get();
        } else {
            $results = DB::table($table)->orderBy('id', $order)->get();
        }
        return $results;
    }
}

if (!function_exists('get_headlines')) {
    function get_headlines()
    {
        $ids = App\PostCategory::where('category_id', get_option('headline_category'))->limit(get_option('headline_limit'))->orderBy('post_id', 'DESC')->pluck('post_id')->toArray();

        $posts = App\Post::whereIn('id', $ids)->get();
        return $posts;
    }
}



if (!function_exists('get_logo')) {
    function get_logo()
    {
        $logo = get_option("logo");
        if ($logo == '') {
            return asset("public/uploads/images/default-logo.png");
        }
        return asset("public/uploads/images/$logo");
    }
}

if (!function_exists('get_icon')) {
    function get_icon()
    {
        $icon = get_option("icon");

        if ($icon == '') {
            return asset("public/uploads/images/default-icon.png");
        }
        return asset("public/uploads/images/$icon");
    }
}

if (!function_exists('get_option')) {
    function get_option($name, $optional = '')
    {
        $setting = App\Setting::where('name', $name)->first();
        if ($setting) {
            return $setting->value;
        }
        return $optional;

    }
}

if (!function_exists('file_settings')) {
    function file_settings($max_upload_size = null, $file_type_supported = null)
    {
        if (!$max_upload_size) {
            $max_upload_size = (get_option('max_upload_size', 5) * 1024);
        }
        if (!$file_type_supported) {
            $file_type_supported = get_option('file_type_supported', 'PNG,JPG,JPEG,png,jpg,jpeg');
        }
        return "|max:$max_upload_size|mimes:$file_type_supported";
    }
}

if (!function_exists('status')) {
    function status($label, $badge, $raw = true)
    {
        return '<span class="badge badge-' . $badge . '">' . $label . '</span>';
    }
}

if (!function_exists('counter')) {
    function counter($table, $where = null)
    {
        if ($where) {
            $count = DB::table($table)->where($where)->count('id');
        } else {
            $count = DB::table($table)->count('id');
        }
        return $count;
    }
}

if (!function_exists('timezone_list')) {
    function timezone_list()
    {
        $zones_array = array();
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['ZONE'] = $zone;
            $zones_array[$key]['GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }
        return $zones_array;
    }

}

if (!function_exists('create_timezone_option')) {

    function create_timezone_option($old = "")
    {
        $option = "";
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $selected = $old == $zone ? "selected" : "";
            $option .= '<option value="' . $zone . '"' . $selected . '>' . 'GMT ' . date('P', $timestamp) . ' ' . $zone . '</option>';
        }
        echo $option;
    }

}

if (!function_exists('get_country_list')) {
    function get_country_list($selected = '')
    {
        if ($selected == "") {
            echo file_get_contents(app_path() . '/Helpers/country.txt');
        } else {
            $pattern = '<option value="' . $selected . '">';
            $replace = '<option value="' . $selected . '" selected="selected">';
            $country_list = file_get_contents(app_path() . '/Helpers/country.txt');
            $country_list = str_replace($pattern, $replace, $country_list);
            echo $country_list;
        }
    }
}

if (!function_exists('load_language')) {
    function load_language($active = '')
    {
        $path = resource_path() . "/_lang";
        $files = scandir($path);
        $options = "";
        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if ($name == "." || $name == "" || $name == "language") {
                continue;
            }
            $selected = "";
            if ($active == $name) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            $options .= "<option value='$name' $selected>" . ucwords($name) . "</option>";
        }
        echo $options;
    }
}

if (!function_exists('get_language_list')) {
    function get_language_list()
    {
        $path = resource_path() . "/_lang";
        $files = scandir($path);
        $array = array();

        $default = get_option('language');
        $array[] = $default;

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if ($name == "." || $name == "" || $name == "language" || $name == $default) {
                continue;
            }

            $array[] = $name;

        }
        return $array;
    }
}


// parse video id from youtube embed url
function get_youtube_video_id($embed_url = '') {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $embed_url, $match);
    $video_id = $match[1];
    return $video_id;
}
// parse video id from vimeo embed url
function get_vimeo_video_id($embed_url = '') {
    if(preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $embed_url, $output_array)) {
    $video_id = $output_array[5];
    return $video_id;
}
}

function get_video_id($url){
$host = explode('.', str_replace('www.', '', strtolower(parse_url($url, PHP_URL_HOST))));
$host = isset($host[0]) ? $host[0] : $host;

if ($host == 'vimeo') {
    return $video_id = get_vimeo_video_id($url);
}elseif ($host == 'youtube' || $host ==  'youtu') {
    return $video_id = get_youtube_video_id($url);
}

return ;
}
