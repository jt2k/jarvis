<?php
/*
    PHP REST API
    Jason Tan
    http://code.google.com/p/php-rest-api/
*/
namespace jt2k\RestApi;

class Twitter extends OAuthRestApi
{
    protected $cache_ext = 'twitter';
    protected $endpoint = 'https://api.twitter.com/1.1';
    protected $encodepost = true;

    public function getAuthorizeUrl($callback = false)
    {
        return parent::getAuthorizeUrl("https://api.twitter.com/oauth/request_token", "https://twitter.com/oauth/authorize", $callback);
    }

    public function getAccessToken()
    {
        return parent::getAccessToken("https://api.twitter.com/oauth/access_token");
    }

    public function setCurlOpts($ch)
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        parent::setCurlOpts($ch);
    }

    /********* STATUS METHODS *********/

    public function public_timeline()
    {
        $url = "{$this->endpoint}/statuses/public_timeline.{$this->format}";

        return $this->request($url);
    }

    /*
        Available parameters:
            since: HTTP formatted date
            since_id: twitter status ID
            count: integer up to 200
            page: integer
    */
    public function friends_timeline($params = array())
    {
        $url = "{$this->endpoint}/statuses/friends_timeline.{$this->format}";
        if (count($params) > 0)
            return $this->request($url, array('get'=>$params));
        else
            return $this->request($url);
    }

    /*
        Available parameters:
            since: HTTP formatted date
            since_id: twitter status ID
            count: integer up to 200
            page: integer
    */
    public function user_timeline($id = false, $params = array())
    {
        if ($id === false)
            $url = "{$this->endpoint}/statuses/user_timeline.{$this->format}";
        else {
            $id = urlencode($id);
            $url = "{$this->endpoint}/statuses/user_timeline/$id.{$this->format}";
        }
        if (count($params) > 0)
            return $this->request($url, array('get'=>$params));
        else
            return $this->request($url);
    }

    public function show_status($id)
    {
        $url = "{$this->endpoint}/statuses/show/$id.{$this->format}";

        return $this->request($url);
    }

    public function update_status($status, $reply_to = false)
    {
        $url = "{$this->endpoint}/statuses/update.{$this->format}";
        $post = array('status' => $status);
        if ($reply_to !== false)
            $post['in_reply_to_status_id'] = $reply_to;

        return $this->request($url, array('post'=>$post));
    }

    /*
        Available parameters:
            since: HTTP formatted date
            since_id: twitter status ID
            page: integer
    */
    public function replies($params = array())
    {
        $url = "{$this->endpoint}/statuses/replies.{$this->format}";
        if (count($params) > 0)
            return $this->request($url, array('get'=>$params));
        else
            return $this->request($url);
    }

    public function mentions($params = array())
    {
        $url = "{$this->endpoint}/statuses/mentions.{$this->format}";
        if (count($params) > 0)
            return $this->request($url, array('get'=>$params));
        else
            return $this->request($url);
    }

    public function destroy_status($id)
    {
        $url = "{$this->endpoint}/statuses/destroy/$id.{$this->format}";
        $post = array('id' => $id);

        return $this->request($url, array('post'=>$post));
    }

    /********* USER METHODS *********/

    public function friends($id = false, $page = false, $cursor = false)
    {
        if ($id === false)
            $url = "{$this->endpoint}/statuses/friends.{$this->format}";
        else {
            $id = urlencode($id);
            $url = "{$this->endpoint}/statuses/friends/$id.{$this->format}";
        }
        if ($page !== false && $page > 1)
            return $this->request($url, array('get'=>array('page'=>$page)));
        elseif ($cursor !== false)
            return $this->request($url, array('get'=>array('cursor'=>$cursor)));
        else
            return $this->request($url);
    }

    public function followers($id = false, $page = false, $cursor = false)
    {
        if ($id === false)
            $url = "{$this->endpoint}/statuses/followers.{$this->format}";
        else {
            $id = urlencode($id);
            $url = "{$this->endpoint}/statuses/followers/$id.{$this->format}";
        }
        if ($page !== false && $page > 1)
            return $this->request($url, array('get'=>array('page'=>$page)));
        elseif ($cursor !== false)
            return $this->request($url, array('get'=>array('cursor'=>$cursor)));
        else
            return $this->request($url);
    }

    public function show_user($id)
    {
        $id = urlencode($id);
        $url = "{$this->endpoint}/users/show/{$id}.{$this->format}";

        return $this->request($url);
    }

    /********* DIRECT MESSAGE METHODS *********/

    /*
        Available parameters:
            since: HTTP formatted date
            since_id: twitter status ID
            page: integer
    */
    public function direct_messages($params = array())
    {
        $url = "{$this->endpoint}/direct_messages.{$this->format}";
        if (count($params) > 0)
            return $this->request($url, array('get'=>$params));
        else
            return $this->request($url);
    }

    /*
        Available parameters:
            since: HTTP formatted date
            since_id: twitter status ID
            page: integer
    */
    public function sent_direct_messages($params = array())
    {
        $url = "{$this->endpoint}/direct_messages/sent.{$this->format}";
        if (count($params) > 0)
            return $this->request($url, array('get'=>$params));
        else
            return $this->request($url);
    }

    public function new_direct_message($user, $text)
    {
        $url = "{$this->endpoint}/direct_messages/new.{$this->format}";
        $post = array('user' => $user, 'text' => $text);

        return $this->request($url, array('post'=>$post));
    }

    public function destroy_direct_message($id)
    {
        $url = "{$this->endpoint}/direct_messages/destroy/$id.{$this->format}";
        $post = array('id' => $id);

        return $this->request($url, array('post'=>$post));
    }

    /********* FRIENDSHIP METHODS *********/

    public function create_friendship($id, $follow = false)
    {
        $id = urlencode($id);
        $url = "{$this->endpoint}/friendships/create/$id.{$this->format}";
        $post = array();
        if ($follow)
            $post['follow'] = 'true';

        return $this->request($url, array('post'=>$post), true);
    }

    public function destroy_friendship($id)
    {
        $id = urlencode($id);
        $url = "{$this->endpoint}/friendships/destroy/$id.{$this->format}";

        return $this->request($url, array(), true);
    }

    public function exists($id1, $id2)
    {
        $url = "{$this->endpoint}/friendships/exists.{$this->format}";
        $get = array('user_a' => $id1, 'user_b' => $id2);

        return $this->request($url, array('get'=>$get));
    }

    /********* SOCIAL GRAPH METHODS *********/

    public function friend_ids($id = false)
    {
        if ($id === false)
            $url = "{$this->endpoint}/friends/ids.{$this->format}";
        else {
            $id = urlencode($id);
            $url = "{$this->endpoint}/friends/ids/$id.{$this->format}";
        }

        return $this->request($url);
    }

    public function follower_ids($id = false)
    {
        if ($id === false)
            $url = "{$this->endpoint}/followers/ids.{$this->format}";
        else {
            $id = urlencode($id);
            $url = "{$this->endpoint}/followers/ids/$id.{$this->format}";
        }

        return $this->request($url);
    }

    /********* ACCOUNT METHODS *********/
    public function rate_limit_status()
    {
        $url = "{$this->endpoint}/account/rate_limit_status.{$this->format}";

        return $this->request($url);
    }

    public function verify_credentials()
    {
        $url = "{$this->endpoint}/account/verify_credentials.{$this->format}";

        return $this->request($url);
    }

    /********* FAVORITE METHODS *********/
    public function favorites($id = false, $page = false)
    {
        if ($id === false)
            $url = "{$this->endpoint}/favorites.{$this->format}";
        else {
            $id = urlencode($id);
            $url = "{$this->endpoint}/favorites/$id.{$this->format}";
        }
        if ($page !== false && $page > 1)
            return $this->request($url, array('get'=>array('page'=>$page)));
        else
            return $this->request($url);
    }

    /********* NOTIFICATION METHODS *********/
    public function follow($id)
    {
        $url = "{$this->endpoint}/notifications/follow/{$id}.{$this->format}";

        return $this->request($url, array(), true);
    }
    public function leave($id)
    {
        $url = "{$this->endpoint}/notifications/leave/{$id}.{$this->format}";

        return $this->request($url, array(), true);
    }

    /********* SEARCH METHODS *********/
    public function search($query, $params = array())
    {
        $url = "{$this->endpoint}/search/tweets.{$this->format}";
        $get = array('q' => $query);
        $get = array_merge($get, $params);

        return $this->request($url, array('get'=>$get));
    }

    /********* LIST METHODS *********/
    public function lists($user_id = false)
    {
        $url = "{$this->endpoint}/lists/list.{$this->format}";
        if ($user_id) {
            $get = array('user_id' => $user_id);
        }

        return $this->request($url, array('get' => $get));
    }

    public function list_members($list_id, $cursor = -1)
    {
        $url = "{$this->endpoint}/lists/members.{$this->format}";
        $get = array('list_id' => $list_id);
        if ($cursor != -1 && $cursor) {
            $get['cursor'] = $cursor;
        }

        return $this->request($url, array('get' => $get));
    }

}
