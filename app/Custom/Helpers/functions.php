<?php

if(!function_exists('sitename')){
    function sitename(){
        return CustomHelper::sitename();
    }
}

if(!function_exists('siteurl')){
    function siteurl(){
        return CustomHelper::mainurl();
    }
}

if(!function_exists('adminurl')){
    function adminurl(){
        return CustomHelper::admin_url();
    }
}

if(!function_exists('main_banner')){
    function main_banner(){
        return CustomHelper::custom_asset_url('theme/img/pk_bg.jpg');
    }
}

if(!function_exists('like_query')){
    // not in use
    function like_query($search, $type = '/$tosearch/'){
        return new \MongoDB\BSON\Regex(str_replace('$tosearch', $search, $type), 'i');
    }
}

if(!function_exists('logged_in_user')){
    function logged_in_user(){
        return \App\User::find(Auth::user()->id);
    }
}

if(!function_exists('helper')){
    /**
     * @return \App\Custom\Helpers\CustomHelper|mixed
     */
    function helper(){
        return app(\App\Custom\Helpers\CustomHelper::class);
    }
}

if(!function_exists('subscription')){
    /**
     * @return \App\Custom\Helpers\Subscription|mixed
     */
    function subscription(){
        return app(\App\Custom\Helpers\Subscription::class);
    }
}

if(!function_exists('permission')){
    /**
     * @return \App\Custom\Helpers\SubscriptionPermissions|mixed
     */
    function permission(){
        return app(\App\Custom\Helpers\SubscriptionPermissions::class);
    }
}

if (!function_exists('convert_elequent_to_sql_query')) {
    function convert_eloquent_to_sql_query($eloquent)
    {

        $query = $eloquent->toSql();
        $biding = $eloquent->getBindings();
        if (count($biding) > 0) {
            foreach ($biding as $oneBind) {
                $from = '/' . preg_quote('?', '/') . '/';
                $to = "'" . $oneBind . "'";
                $query = preg_replace($from, $to, $query, 1);
            }
        }
        return $query;
    }
}

if(!function_exists('custom_flash')){
    function custom_flash($message, $level, $keep = 0){
        $flashMessages = request()->session()->has('custom_flash_notification') ? request()->session()->get('custom_flash_notification') : [];
        $flashMessages[] = [
            'message' => $message,
            'level' => $level,
            'keep' => $keep
        ];
        request()->session()->put('custom_flash_notification', $flashMessages);
    }
}

if(!function_exists('get_custom_flash')){
    function get_custom_flash($key){
        if(!session()->has($key) && empty(session()->get($key)))
            return false;
        $flashMessages = session($key);
        foreach($flashMessages as $skey => $flash) {
            if($flash['keep'] < 2){
                session()->forget($key . '.' . $skey);
            } else {
                $flash['keep']--;
                request()->session()->flash($key . '.' . $skey, $flash);
            }
        }
        return $flashMessages;
    }
}

if(!function_exists('get_carbon_instance')){
    function get_carbon_instance($datetime, $format = 'Y-m-d H:i:s')
    {
        return Carbon::createFromFormat($format, $datetime);
    }
}

if(!function_exists('parse_videos')){
    function parse_videos($string)
    {
        $autoEmbed = new App\Custom\Helpers\AutoEmbed();
//        $autoEmbed->set_attr([
//            'width' => 100
//        ]);
        return $autoEmbed->parse($string);
    }
}

if(!function_exists('parse_string')){
    function parse_string($string)
    {
        $string = parse_videos($string);
        return $string;
    }
}

if(!function_exists('get_captcha')){
    function get_captcha()
    {
        return app('captcha')->display($attributes = [], $lang = app()->getLocale());
    }
}
