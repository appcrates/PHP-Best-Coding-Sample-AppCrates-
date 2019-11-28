<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Common extends Model
{
   public $foo = '';

    public static function insert_data($data_array,$table_name)
    {
        return DB::table($table_name)->insertGetId($data_array);      
    }

    public static function update_data($id,$data_array,$table_name)
    {
        return DB::table($table_name)->where('id', $id)->update($data_array);
    }

    public static function single_data($id,$table_name,$status='1')
    {
        return DB::table($table_name)->where('id', $id)->where('status', $status)->first();
    }

    public static function data_by_with($where,$table)
    {
         return DB::table($table)->where($where)->first();
    }

    public static function search_data($select,$where,$table)
    {
       return DB::table($table)->select($select)->WhereRaw($where)->get();
    }

     public static function all_friends($select,$where,$table,$user_id)
    {
       return DB::select("select friend_id from friends where status='1' AND user_id='".$user_id."'");
    }

    public static function search_data_single($select,$where,$table)
    {
       return DB::table($table)->select($select)->WhereRaw($where)->first();
    }

     public static function search_data_with_pagination($select,$where,$table,$start_from,$limit)
    {
       return DB::table($table)
      ->select($select)
      ->WhereRaw($where)
      ->offset($start_from)
      ->limit($limit)
      ->get();
    }



    public static function search_single_data($select,$where,$table)
    {
       return DB::table($table)->select($select)->WhereRaw($where)->first();
    }
    public static function get_friend_requests($where,$start_from,$limit)
    {
        return DB::table('friends_request')
            ->join('users', 'users.id', '=', 'friends_request.request_sender')
            ->join('users as users_1', 'users_1.id', '=', 'friends_request.request_receiver')            
            ->select('users.id as sender_id','users.f_name as sender_f_name','users.picture as sender_picture','users.l_name as sender_l_name','friends_request.id as request_id','friends_request.status','users_1.f_name as receiver_f_name','users_1.l_name as receiver_l_name')->offset($start_from)
                ->limit($limit)
            ->WhereRaw($where)
            ->get();
    }    

   public static function delete_record($where,$table)
   {
        return DB::table($table)->where($where)->delete();
   }

    public static function friends_list($where,$start_from,$limit)
    {

        return DB::table('friends')
            ->join('users', 'friends.friend_id', '=', 'users.id')            
            ->select('users.*') 
            ->offset($start_from)
            ->limit($limit)           
            ->where($where)
            ->get();
    }

    public static function random_string($user_id) {
        $length=10;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $final_string=$randomString.time();

        $data=array(
            "login_token"=>$final_string
        );
        Common::update_data($user_id,$data,"users");
         return $final_string;
    }

    public static function get_conversiation($user_id,$start_from,$limit)
    {
         return DB::table('conversation')
            ->join('users', 'conversation.user', '=', 'users.id') 
            ->join('users as users_1', 'conversation.friend', '=', 'users_1.id')            
            ->select('conversation.id as cnv_id','users.f_name','users.picture','users.l_name','users.id as user_id','users_1.f_name as friend_f_name','users_1.l_name as friend_l_name','users_1.picture as friend_picture','users_1.id as friend_id','conversation.last_msg','conversation.updated_at')
            ->whereRaw('conversation.user='.$user_id.' OR conversation.friend='.$user_id.'')
            ->orderBy('conversation.updated_at', 'desc')
            ->offset($start_from)
                ->limit($limit)
            ->get();
    }

    public static function increase($where,$column,$table,$add_value=1)
    {
        return DB::table($table)        
        ->where($where)
        ->increment($column,$add_value);
    }

    public static function decremental($where,$column,$table,$add_value=1)
    {
        return DB::table($table)        
        ->where($where)
        ->decrement($column,$add_value);
    }

    public static function get_notifications($receiver,$start_from,$limit)
    {
         return DB::table('notifications')
            ->join('users', 'notifications.sender', '=', 'users.id') 
            
            ->select('notifications.id as ntf_id','users.picture','notifications.notification','notifications.method','notifications.request_id','notifications.type','notifications.status','notifications.created_at','notifications.open','notifications.sender')
            ->whereRaw('notifications.receiver='.$receiver.' AND notifications.status=1')->offset($start_from)
                ->limit($limit)
                ->orderBy('notifications.id', 'desc')
            ->get();
    }



  




    public static function get_notificationscount($receiver)
    {
         return DB::table('notifications')
            ->join('users', 'notifications.sender', '=', 'users.id') 
            ->selectRaw('count(*) as notificationcount')          
            ->whereRaw('notifications.receiver='.$receiver.' AND notifications.status=1 AND read_bit=0')
            ->get()->first();
    }


    public static function get_messagescount($receiver)
    {
         return DB::table('messages')            
            ->selectRaw('count(*) as messagecount')          
            ->whereRaw('receiver_id='.$receiver.' AND status=1 AND read_bit=0')
            ->get()->first();
    }


public static function update_counter($data_array,$table_name,$where)
    {
        return DB::table($table_name)->whereRaw($where)->update($data_array);
    }


public static function all_event_participants($event_id,$start_from,$limit)
{
     return DB::table('event_participants')
            ->join('users', 'event_participants.participants_id', '=', 'users.id') 
            ->select('users.id','users.f_name','users.l_name','users.picture','event_participants.created_at')->whereRaw('event_participants.event_id='.$event_id.' AND event_participants.status=1')
             ->offset($start_from)
                ->limit($limit)
            ->get();
}


 public static function get_conversiationcount($receiver,$cnv_id)
    {
         return DB::table('messages')            
            ->selectRaw('count(*) as messagecount')          
            ->whereRaw('receiver_id='.$receiver.' AND status=1 AND read_bit=0 AND conversation_id='.$cnv_id.'')
            ->get()->first();
    }











    public static function last_followed_by($event_id)
    {	
    	 return DB::table('event_participants')
                ->join('users', 'users.id', '=', 'event_participants.participants_id')              
                ->select('users.id as user_id','users.f_name','users.l_name')
                ->WhereRaw('event_participants.event_id='.$event_id.'')
                ->orderBy('event_participants.updated_at','desc')
                ->limit(1)
                ->get();
    }



public static function total_count($table,$where)
    {
         return DB::table($table)           
            ->selectRaw('count(*) as '.$table.'')          
            ->whereRaw($where)
            ->get()->first();
    }



   



    public static function auto_msg($lat,$long,$user_id)
    {
         return DB::select('SELECT events.id as event_id,users.id as user_id,(6371 * acos(cos( radians( '.$lat.' ) ) * cos( radians( `lat` ) ) * cos(radians( `lng` ) - radians( '.$long.' )) + sin(radians('.$lat.')) * sin(radians(`lat`)))) `distance` from events,users,event_participants where events.user_id= users.id AND events.id=event_participants.event_id AND event_participants.participants_id='.$user_id.' AND UTC_TIMESTAMP()>=utc_start_datetime AND UTC_TIMESTAMP()<=utc_end_datetime having distance < 0.5 ');
    }

    public static function get_friends($user_id,$event_id)
    {
        return DB::select('SELECT `user_on_event`.`user_id` FROM `user_on_event` INNER JOIN `friends` ON (`user_on_event`.`user_id` = `friends`.`friend_id`) WHERE `friends`.`user_id`='.$user_id.' AND user_on_event.event_id='.$event_id.'');
    }

public static function auto_msg2($lat,$long,$user_id)
    {
         return DB::select('SELECT events.id as event_id,users.id as user_id,(6371 * acos(cos( radians( '.$lat.' ) ) * cos( radians( `lat` ) ) * cos(radians( `lng` ) - radians( '.$long.' )) + sin(radians('.$lat.')) * sin(radians(`lat`)))) `distance` from events,users where events.user_id= users.id AND UTC_TIMESTAMP()>=utc_start_datetime AND UTC_TIMESTAMP()<=utc_end_datetime having distance < 0.5 ');
    }



/****************************** All Walls *********************************************/
public static function main_wall_script($start_from,$limit)
    {
         return DB::table('events')           
             ->join('users', 'events.user_id', '=', 'users.id')
            ->select('events.id as event_id','users.picture','users.cover_img','users.id as user_id','users.f_name','users.l_name','events.title','events.event_datetime','events.lat','events.lng','events.venue','events.created_at','events.event_privacy','events.total_joins','events.utc_end_datetime','events.utc_start_datetime')
            ->whereRaw('events.status=1 AND events.event_privacy!=1 AND  events.created_at > DATE_SUB(CURDATE(),INTERVAL 24 hour)')
            ->orderBy('events.created_at', 'desc')
            ->offset($start_from)
                ->limit($limit)
            ->get();
    }
   public static function my_wall_script($user_id,$start_from,$limit)
    {
         return DB::table('events')
            ->leftJoin('event_participants', 'event_participants.event_id', '=', 'events.id')
             ->join('users', 'events.user_id', '=', 'users.id')
            ->select('events.id as event_id','users.picture','users.cover_img','users.id as user_id','users.f_name','users.l_name','events.title','events.event_datetime','events.lat','events.lng','events.venue','events.created_at','events.event_privacy','events.total_joins','events.utc_end_datetime','events.utc_start_datetime')
            ->whereRaw('events.status=1 AND (events.user_id='.$user_id.' OR event_participants.participants_id='.$user_id.')')->orderBy('events.updated_at','desc')
            ->offset($start_from)
                ->limit($limit)
            ->get();
    }
    public static function tomorrow_events_script($start_from,$limit)
    {
         return DB::table('events')           
             ->join('users', 'events.user_id', '=', 'users.id')
            ->select('events.id as event_id','users.picture','users.cover_img','users.id as user_id','users.f_name','users.l_name','events.title','events.event_datetime','events.lat','events.lng','events.venue','events.created_at','events.event_privacy','events.total_joins','events.utc_end_datetime','events.utc_start_datetime')
            ->whereRaw('events.status=1 AND events.event_privacy!=1 AND  DATE_FORMAT(events.event_datetime, "%Y-%m-%d") = DATE_ADD(CURDATE(),INTERVAL 1 DAY)')
            ->orderBy('events.updated_at', 'desc')
            ->offset($start_from)
                ->limit($limit)
            ->get();
    }

    public static function date_events_script($date,$user_id)
    {

         return DB::table('events')
            ->leftJoin('event_participants', 'event_participants.event_id', '=', 'events.id')
             ->join('users', 'events.user_id', '=', 'users.id')
            ->select('events.id as event_id','users.picture','users.cover_img','users.id as user_id','users.f_name','users.l_name','events.title','events.event_datetime','events.lat','events.lng','events.venue','events.created_at','events.event_privacy','events.total_joins','events.utc_end_datetime','events.utc_start_datetime')
            ->whereRaw('events.status=1 AND DATE_FORMAT(events.event_datetime, "%Y-%m-%d")="'.$date.'" AND (events.user_id='.$user_id.' OR event_participants.participants_id='.$user_id.')')->orderBy('events.updated_at','desc')          
            ->get();

      
    }
    public static function week_events_script($start_from,$limit)
    {
         return DB::table('events')           
             ->join('users', 'events.user_id', '=', 'users.id')
            ->select('events.id as event_id','users.picture','users.cover_img','users.id as user_id','users.f_name','users.l_name','events.title','events.event_datetime','events.lat','events.lng','events.venue','events.created_at','events.event_privacy','events.total_joins','events.utc_end_datetime','events.utc_start_datetime')
            ->whereRaw('events.status=1 AND events.event_privacy!=1 AND DATE_FORMAT(events.event_datetime, "%Y-%m-%d") >= CURDATE() AND DATE_FORMAT(events.event_datetime, "%Y-%m-%d")<=DATE_ADD(CURDATE(),INTERVAL 7 DAY)')
            ->orderBy('events.updated_at', 'desc')
            ->offset($start_from)
                ->limit($limit)
            ->get();
    }


    public static function location_wise($start_from,$limit,$lat,$long)
    {
         return DB::select('SELECT events.id as event_id,users.picture,users.cover_img,users.id as user_id,users.f_name,users.l_name,events.title,events.event_datetime,events.lat,events.lng,events.venue,events.created_at,events.event_privacy,events.utc_end_datetime,events.utc_start_datetime,events.total_joins, (6371 * acos(cos( radians( '.$lat.' ) ) * cos( radians( `lat` ) ) * cos(radians( `lng` ) - radians( '.$long.' )) + sin(radians('.$lat.')) * sin(radians(`lat`)))) `distance` from events,users where events.user_id= users.id  having distance < 20 limit '.$start_from.','.$limit.' ');
    }

    public static function event_search($start_from,$limit,$key_word)
    {
         return DB::table('events')           
             ->join('users', 'events.user_id', '=', 'users.id')
            ->select('events.id as event_id','users.picture','users.cover_img','users.id as user_id','users.f_name','users.l_name','events.title','events.event_datetime','events.lat','events.lng','events.venue','events.created_at','events.event_privacy','events.total_joins','events.utc_end_datetime','events.utc_start_datetime')
            ->whereRaw("events.status=1 AND events.event_privacy!=1 AND events.title like '%".$key_word."%' ")
            ->orderBy('events.updated_at', 'desc')
            ->offset($start_from)
                ->limit($limit)
            ->get();
    }


     public static function event_live($start_from,$limit)
    {
         return DB::table('events')           
             ->join('users', 'events.user_id', '=', 'users.id')
            ->select('events.id as event_id','users.picture','users.cover_img','users.id as user_id','users.f_name','users.l_name','events.title','events.event_datetime','events.lat','events.lng','events.venue','events.created_at','events.event_privacy','events.total_joins','events.utc_end_datetime','events.utc_start_datetime')
            ->whereRaw("events.status=1 AND events.event_privacy!=1 AND UTC_TIMESTAMP()>=utc_start_datetime AND UTC_TIMESTAMP()<=utc_end_datetime")
            ->orderBy('events.updated_at', 'desc')
            ->offset($start_from)
                ->limit($limit)
            ->get();
    }

 public static function friend_filter($user_id,$start_from,$limit,$friends)
    {
         return DB::table('events')
            ->leftJoin('event_participants', 'event_participants.event_id', '=', 'events.id')
             ->join('users', 'events.user_id', '=', 'users.id')
            ->select('events.id as event_id','users.picture','users.cover_img','users.id as user_id','users.f_name','users.l_name','events.title','events.event_datetime','events.lat','events.lng','events.venue','events.created_at','events.event_privacy','events.total_joins','events.utc_end_datetime','events.utc_start_datetime')
            ->whereRaw('events.status=1 AND (events.user_id IN('.$friends.') OR event_participants.participants_id IN('.$friends.'))')->orderBy('events.updated_at','desc')
            ->distinct()
            ->offset($start_from)
                ->limit($limit)
            ->get();
    }

    public static function event_user_search($start_from,$limit,$key_word,$user_id)
    {
          return DB::table('events')
            ->leftJoin('event_participants', 'event_participants.event_id', '=', 'events.id')
             ->join('users', 'events.user_id', '=', 'users.id')
            ->select('events.id as event_id','users.picture','users.cover_img','users.id as user_id','users.f_name','users.l_name','events.title','events.event_datetime','events.lat','events.lng','events.venue','events.created_at','events.event_privacy','events.total_joins','events.utc_end_datetime','events.utc_start_datetime')
            ->whereRaw('events.status=1 AND events.title like "%'.$key_word.'%" AND (events.user_id='.$user_id.' OR event_participants.participants_id='.$user_id.')')->orderBy('events.updated_at','desc')
            ->offset($start_from)
                ->limit($limit)
            ->get();
    }

/*************************************************************************************/

 public static function create_password($user_id,$length=6) {
        
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $final_string=$randomString.time();

        $data=array(
            "password"=>md5($final_string)
        );
        Common::update_data($user_id,$data,"users");
         return $final_string;
    }




}
