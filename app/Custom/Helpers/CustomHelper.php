<?php

namespace App\Custom\Helpers;

use App\Emails;
use App\Settings;
use App\Templates;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Custom\Helpers\CustomAdminHelper;
use Illuminate\Support\Facades\URL;
use Spamassassin\Client;

class CustomHelper {

    use CustomAdminHelper;

    // this contains variables to be used every where like Post slug
    private $global;
    private $http_protocol = '';
    private $main_url = '';

    function __construct()
    {
        $this->global = [];
        $this->http_protocol = !isset($_SERVER['SERVER_PROTOCOL']) ? 'http://' : (stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://');
        $this->main_url = Config::get('app.url') . '/';
    }

    function getProtocol()
    {
        return $this->http_protocol;
    }

    function mainurl()
    {
        return $this->main_url;
    }

    function sitename()
    {
        return "Bulk Mailer";
    }

    public function get_menus($menu = false)
    {
        $menubars = [
            'main' => [
            ],
            'active' => (isset($this->getPathActions()['as'])) ? $this->getPathActions()['as'] : ''
        ];
        if($menu){
            $menubars = isset($menubars[$menu]) ? $menubars[$menu] : false;
        }
        return $menubars;
    }

    public function get_frontend_menu_html($menu = 'main', $listClass = '')
    {
        return $this->generate_navigation($this->get_menus()[$menu], 1, $listClass);
    }

    public function getPathActions()
    {
        if(is_null(Route::getCurrentRoute()))
            return null;
        return Route::getCurrentRoute()->getAction();
    }

    public function getPreviousLink()
    {
        $url = URL::previous();
        if(strpos($url, 'register') === false || strpos($url, 'login') === false){
            $url = ($this->is_admin()) ? $this->getPageUrl('admin::dashboard') : '/';
        }
        return $url;
    }

    public function getPageUrl($page, $args=[])
    {
        if(is_int(strpos($page, '|'))) {
            $args = explode('|', $page);
            $page = $args[0];
            unset($args[0]);
        }
        if(is_int(strpos($page, '::'))){
            $url = route($page, $args);
        } else if(is_int(strpos($page, '@'))) {
            $url = action($page, $args);
        } else if($page == '/') {
            $url = url($page, $args);
        } else {
            $url = url((is_int(strpos($page, '/')) ? '' : '/') . $page, $args);
        }

        return $url;
    }

    public function get_current_page($returnArray = false){
        $page = isset(CustomHelper::getPathActions()['as']) ? CustomHelper::getPathActions()['as'] : '';
        return $returnArray ? explode("::", $page) : $page;
    }

    public function get_asset_path($dir)
    {
        return public_path('assets/' . $dir . '/');
    }

    /**
     * @return array
     */
    public function getGlobal($key)
    {
        return isset($this->global[$key]) ? $this->global[$key] : null;
    }

    /**
     * @param $key
     * @param null $value
     * @internal param array $global
     */
    public function setGlobal($key, $value = null)
    {
        $global = $this->global;
        if(is_array($key)){
            $global = array_merge($global, $key);
        } else {
            $global[$key] = $value;
        }
        $this->global = $global;
    }

    protected function get_files_of_type($folder, array $ext)
    {
        $files = File::files($folder);
        $reqFiles = [];
        if(count($files) > 0) {
            foreach ($files as $file){
                if(in_array(File::extension($file), $ext)){
                    $reqFiles[] = $file;
                }
            }
        }
        return $reqFiles;
    }

    public function convert_path_to_url($path, $cutFolder = 'public')
    {
        return url(str_replace([base_path(), $cutFolder, "\\", "//"], ['', '', '/', '/'], $path));
    }

    public function custom_asset_url($file)
    {
        return "{$this->main_url}assets/" . $file;
    }

    public function stars($value, $all = 5, $add_count = false)
    {
        $stars = '<ul class="stars">';
        for($i=1; $i<=$all; $i++){
            $stars .= "<li><i class='fa ". (($value >= $i) ? "fa-star" : ((($i - $value) > 0 && ($i - $value) < 1) ? "fa-star-half-o" : "fa-star-o"))."'></i></li>";
        }
        if($add_count){
            $stars .= "<li class='total-rating'>({$value})</li>";
        }
        $stars .= '</ul>';
        return $stars;
    }

    public function average_stars($all_reviews, $total = 5)
    {
        if(count($all_reviews) == 0)
            return "No reviews yet";
        $total = (array_sum($all_reviews) / count($all_reviews));
        $total = $this->stars($total);
        return $total;
    }

    function convert_elequent_to_sql_query($elequent){
        $query = $elequent->toSql();
        $biding = $elequent->getBindings();
        if (count($biding) > 0) {
            foreach ($biding as $oneBind) {
                $from = '/' . preg_quote('?', '/') . '/';
                $to = "'" . $oneBind . "'";
                $query = preg_replace($from, $to, $query, 1);
            }
        }
        return $query;
    }

    function content_to_replace($content, Emails $emailObj, $imgTag){
        $unsubscribeLink = '<a href="' . $this->getPageUrl('main::email.unsubscribe', ['email' => $emailObj->email, 'token' => $emailObj->token]) . '">Unsubscribe</a>';
        $email = $emailObj->email;

        $content = str_replace(['$${UNSUBSCRIBE}$$', '$${EMAIL}$$'], [$unsubscribeLink, $email], $content);
        if(is_int(strpos($content, '</body>'))) {
            $content = str_replace(['</body>'], [$imgTag . '</body>'], $content);
        } else {
            $content .= $imgTag;
        }
        return $content;
    }

    function is_spam_free_template(Templates $template){
        $validScore = $this->getSettings('min_spam_score');
        return ($template->spam_score >= $validScore);
    }

    function template_spam_test(Templates $template){
        try {
            $checker = new Client([]);
            if ($checker->ping()) {
                $template->spam_score = $checker->getScore($template->message);
                $template->update();

                return true;
            } else {
                return ['Unable to connect server to verify template'];
            }
        } catch (\Exception $e){
            return [$e->getMessage()];
        }
    }

    // This will return free tube
    function getFreeQueue(){
        if(env('QUEUE_DRIVER') != 'beanstalkd'){
            return 'default';
        }
        $beanstalk = new \Beanstalk\Client();
        $beanstalk->connect();
        if(!$beanstalk->connected){
            return 'default';
        }
        $tubes = $beanstalk->listTubes();
        $usedOnes = $beanstalk->listTubeUsed();
        $usedOnes = is_array($usedOnes) ? $usedOnes : [$usedOnes];
        $freeTubes = is_array($tubes) ? array_diff($tubes, $usedOnes) : [];

        if(empty($freeTubes)){
            $tube = 'default';
        } else {
            $tubeStats = [];
            foreach($freeTubes as $freeTube){
                $tubeStat = $beanstalk->statsTube($freeTube);
                if(is_array($tubeStat)) {
                    $tubeStats[$freeTube] = $tubeStat['current-jobs-ready'];
                }
            }
            if(empty($tubeStats)){
                $tube = 'default';
            } else {
                $tubeStats = collect($tubeStats);
                $jobsCount = $tubeStats->min();
                $tube = $tubeStats->search($jobsCount);
            }
        }
        $beanstalk->disconnect();
        return $tube;
    }

    function getSettings($key = null){
        $settings = $this->getGlobal('settings');
        if(!is_array($settings)){
            $settings = Settings::all()->pluck('value', 'key');
            $this->setGlobal('settings', $settings);
        }
        return is_null($key) ? $settings : (isset($settings[$key]) ? $settings[$key] : null);
    }

    function getMailListStatuses(){
        return ['In Queue', 'Sent', 'Failed'];
    }

    function getTemplateStatuses(){
        return ['Draft', 'In Queue', 'Sending', 'Sent'];
    }

    function getCampaignsStatuses(){
        return ['Draft', 'In Queue', 'In Progress', 'Completed'];
    }

    function randomNumber($length, $prevGenerated = false) {
        $result = '';
        for($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }
        if(is_int($prevGenerated) && $prevGenerated == $result){
            $result = $this->randomNumber($length, $prevGenerated);
        }
        return $result;
    }

    function getDefaultSpamScore(){
    	return 6;
    }
}
